<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreTestimonialRequest;
use App\Http\Requests\Staff\Content\UpdateTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/testimonials/Index', [
            'testimonials' => Testimonial::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/testimonials/Create');
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $testimonial = Testimonial::query()->create($request->validated());

        return redirect()->route('staff.content.testimonials.show', $testimonial);
    }

    public function show(Testimonial $testimonial): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/testimonials/Show', ['testimonial' => $testimonial]);
    }

    public function edit(Testimonial $testimonial): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/testimonials/Edit', ['testimonial' => $testimonial]);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('manage-content');

        $testimonial->update($request->validated());

        return redirect()->route('staff.content.testimonials.show', $testimonial);
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('manage-content');

        $testimonial->delete();

        return redirect()->route('staff.content.testimonials.index');
    }
}
