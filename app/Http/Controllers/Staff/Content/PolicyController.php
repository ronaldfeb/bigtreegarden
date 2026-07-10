<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StorePolicyRequest;
use App\Http\Requests\Staff\Content\UpdatePolicyRequest;
use App\Models\Policy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PolicyController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/policies/Index', [
            'policies' => Policy::query()->latest()->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/policies/Create');
    }

    public function store(StorePolicyRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $policy = Policy::query()->create([
            ...$request->validated(),
            'updated_by_staff_user_id' => $this->staffUser($request)->id,
        ]);

        return redirect()->route('staff.content.policies.show', $policy);
    }

    public function show(Policy $policy): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/policies/Show', ['policy' => $policy]);
    }

    public function edit(Policy $policy): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/policies/Edit', ['policy' => $policy]);
    }

    public function update(UpdatePolicyRequest $request, Policy $policy): RedirectResponse
    {
        Gate::authorize('manage-content');

        $policy->update([
            ...$request->validated(),
            'updated_by_staff_user_id' => $this->staffUser($request)->id,
        ]);

        return redirect()->route('staff.content.policies.show', $policy);
    }

    public function destroy(Policy $policy): RedirectResponse
    {
        Gate::authorize('manage-content');

        $policy->delete();

        return redirect()->route('staff.content.policies.index');
    }
}
