<?php

namespace App\Http\Controllers;

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\Transaction;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MemorialPageLiveController extends Controller
{
    public function show(string $slug): InertiaResponse
    {
        $memorialPage = $this->resolvePublishedMemorialPage($slug);
        $memorialPage->loadMissing('personOfInterest');

        return Inertia::render('memorial/Live', [
            'memorialPage' => [
                'title' => $memorialPage->title,
                'person_full_name' => $memorialPage->personOfInterest?->display_name,
                'public_slug' => $memorialPage->public_slug,
            ],
            'isActiveDay' => $this->isActiveDay($memorialPage),
            'canPost' => request()->user() !== null && $this->isActiveDay($memorialPage),
            'messagesUrl' => route('memorial.live.messages', $memorialPage->public_slug),
            'postUrl' => route('memorial.live.store', $memorialPage->public_slug),
            'memorialUrl' => route('memorial.public.show', $memorialPage->public_slug),
            'loginUrl' => route('login'),
            'registerUrl' => route('register', ['intent' => 'live']),
        ]);
    }

    public function messages(Request $request, string $slug): JsonResponse
    {
        $memorialPage = $this->resolvePublishedMemorialPage($slug);

        $query = $memorialPage->messages()
            ->with('authorUser')
            ->where('context', 'live_day')
            ->where('status', 'approved')
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(200);

        $after = $request->string('after')->toString();

        if ($after !== '') {
            $afterTimestamp = rescue(fn (): Carbon => Carbon::parse($after), null, false);

            if ($afterTimestamp !== null) {
                $query->where('created_at', '>', $afterTimestamp);
            }
        }

        $messages = $query->get()->map(fn (MemorialPageMessage $message): array => [
            'id' => $message->id,
            'author' => $message->authorUser?->name,
            'body' => $message->body,
            'image_url' => $message->image_path !== null ? '/storage/'.$message->image_path : null,
            'posted_at' => $message->created_at?->toIso8601String(),
        ]);

        return response()
            ->json(['messages' => $messages])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $memorialPage = $this->resolvePublishedMemorialPage($slug);

        abort_unless($this->isActiveDay($memorialPage), 403);

        $validated = $request->validate([
            'body' => ['required_without:image', 'nullable', 'string', 'max:500'],
            'image' => ['required_without:body', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $imagePath = $request->file('image')?->store('live/'.$memorialPage->id, 'public');

        $memorialPage->messages()->create([
            'author_user_id' => $request->user()->id,
            'type' => $imagePath !== null ? 'image' : 'text',
            'body' => $validated['body'] ?? null,
            'image_path' => $imagePath,
            'context' => 'live_day',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back();
    }

    public function storeFlower(Request $request, string $slug, GeofenceService $geofenceService): RedirectResponse
    {
        $memorialPage = $this->resolvePublishedMemorialPage($slug);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $personOfInterest = $memorialPage->personOfInterest;

        $site = $personOfInterest === null ? null : $geofenceService->isWithinMemorialSite(
            $personOfInterest,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        if ($site === null) {
            throw ValidationException::withMessages([
                'location' => 'Flowers can only be left at the memorial site. Please visit the site to leave your flowers.',
            ]);
        }

        $message = DB::transaction(function () use ($request, $memorialPage, $validated, $site): MemorialPageMessage {
            $message = $memorialPage->messages()->create([
                'author_user_id' => $request->user()->id,
                'memorial_site_id' => $site->id,
                'type' => 'text',
                'body' => $validated['body'],
                'context' => 'flowers',
                'posted_latitude' => $validated['latitude'],
                'posted_longitude' => $validated['longitude'],
                'is_gps_verified' => true,
                'status' => 'pending',
            ]);

            $transaction = Transaction::query()->create([
                'user_id' => $request->user()->id,
                'payable_type' => MemorialPageMessage::class,
                'payable_id' => $message->id,
                'type' => TransactionType::FlowerMessage,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => config('memorial.flower_price_cents'),
                'currency' => 'ZAR',
                'status' => TransactionStatus::Initiated,
            ]);

            $message->update(['transaction_id' => $transaction->id]);

            return $message;
        });

        return redirect()->route('flowers.checkout', $message);
    }

    private function resolvePublishedMemorialPage(string $slug): MemorialPage
    {
        return MemorialPage::query()
            ->where('public_slug', $slug)
            ->whereHas('pamphlet', function ($query): void {
                $query->whereIn('status', [PamphletStatus::Paid, PamphletStatus::Published]);
            })
            ->firstOrFail();
    }

    private function isActiveDay(MemorialPage $memorialPage): bool
    {
        return $memorialPage->live_comments_enabled
            && $memorialPage->active_day_date !== null
            && $memorialPage->active_day_date->isToday();
    }
}
