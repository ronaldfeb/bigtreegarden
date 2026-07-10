<?php

namespace App\Http\Controllers\Staff\Directory;

use App\Http\Controllers\Staff\Controller;
use App\Models\MemorialPageMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MemorialPageMessageController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('manage-directory');

        $status = $request->query('status');
        $context = $request->query('context');

        $messages = MemorialPageMessage::query()
            ->with(['memorialPage.personOfInterest', 'authorUser'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($context, fn ($query) => $query->where('context', $context))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (MemorialPageMessage $message): array => [
                'id' => $message->id,
                'memorial_page_title' => $message->memorialPage?->title,
                'person_of_interest_name' => $message->memorialPage?->personOfInterest?->display_name,
                'author_name' => $message->authorUser?->name,
                'context' => $message->context,
                'status' => $message->status,
                'is_gps_verified' => $message->is_gps_verified ? 'Yes' : 'No',
                'created_at' => $message->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('staff/directory/memorial-page-messages/Index', [
            'messages' => $messages,
            'filters' => [
                'status' => $status,
                'context' => $context,
            ],
        ]);
    }
}
