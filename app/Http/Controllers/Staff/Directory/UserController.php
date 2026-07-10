<?php

namespace App\Http\Controllers\Staff\Directory;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Directory\StoreUserRequest;
use App\Http\Requests\Staff\Directory\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/users/Index', [
            'users' => User::query()->with('staffUser')->latest()->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $user = User::query()->create($request->validated());

        return redirect()->route('staff.directory.users.show', $user);
    }

    public function show(User $user): Response
    {
        Gate::authorize('manage-directory');

        $user->load('staffUser');

        return Inertia::render('staff/directory/users/Show', ['user' => $user]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $user->update($request->validated());

        return redirect()->route('staff.directory.users.show', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $user->delete();

        return redirect()->route('staff.directory.users.index');
    }
}
