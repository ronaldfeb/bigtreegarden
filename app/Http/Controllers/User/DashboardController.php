<?php

namespace App\Http\Controllers\User;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\MemorialPageMessage;
use App\Models\MemorialPagePamphlet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $pamphlets = MemorialPagePamphlet::query()
            ->with(['background', 'memorialPage.personOfInterest.users'])
            ->whereHas('memorialPage.personOfInterest.users', function ($query) use ($request): void {
                $query->where('users.id', $request->user()->id)
                    ->where('user_persons_of_interest.role', 'owner');
            })
            ->latest()
            ->get()
            ->map(fn (MemorialPagePamphlet $pamphlet): array => [
                'id' => $pamphlet->id,
                'heading' => $pamphlet->heading,
                'person_full_name' => $pamphlet->person_full_name,
                'status' => $pamphlet->status?->value ?? $pamphlet->status,
                'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
                'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
                'background_asset_path' => $pamphlet->background?->asset_path,
            ]);

        $pendingMessages = MemorialPageMessage::query()
            ->with(['authorUser', 'memorialPage', 'transaction'])
            ->where('status', 'pending')
            ->whereHas('memorialPage.personOfInterest.users', function ($query) use ($request): void {
                $query->where('users.id', $request->user()->id);
            })
            ->latest()
            ->get()
            ->map(fn (MemorialPageMessage $message): array => [
                'id' => $message->id,
                'author_name' => $message->authorUser?->name,
                'memorial_page_title' => $message->memorialPage?->title,
                'context' => $message->context,
                'body_excerpt' => Str::limit((string) $message->body, 120),
                'created_at' => $message->created_at?->toDateTimeString(),
                'transaction_complete' => $message->transaction === null
                    ? null
                    : $message->transaction->status === TransactionStatus::Complete,
            ]);

        return Inertia::render('user/Dashboard', [
            'pamphlets' => $pamphlets,
            'pendingMessages' => $pendingMessages,
        ]);
    }
}
