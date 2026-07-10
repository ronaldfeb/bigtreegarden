<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreHelpCenterTopicRequest;
use App\Http\Requests\Staff\Content\UpdateHelpCenterTopicRequest;
use App\Models\HelpCenterTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HelpCenterTopicController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-topics/Index', [
            'topics' => HelpCenterTopic::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-topics/Create');
    }

    public function store(StoreHelpCenterTopicRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $topic = HelpCenterTopic::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], HelpCenterTopic::class),
        ]);

        return redirect()->route('staff.content.help-center-topics.show', $topic);
    }

    public function show(HelpCenterTopic $helpCenterTopic): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-topics/Show', ['topic' => $helpCenterTopic]);
    }

    public function edit(HelpCenterTopic $helpCenterTopic): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-topics/Edit', ['topic' => $helpCenterTopic]);
    }

    public function update(UpdateHelpCenterTopicRequest $request, HelpCenterTopic $helpCenterTopic): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $helpCenterTopic->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], HelpCenterTopic::class, $helpCenterTopic->id);
        }

        $helpCenterTopic->update($validated);

        return redirect()->route('staff.content.help-center-topics.show', $helpCenterTopic);
    }

    public function destroy(HelpCenterTopic $helpCenterTopic): RedirectResponse
    {
        Gate::authorize('manage-content');

        $helpCenterTopic->delete();

        return redirect()->route('staff.content.help-center-topics.index');
    }
}
