<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use Inertia\Inertia;
use Inertia\Response;

class AmbassadorController extends Controller
{
    public function show(Ambassador $ambassador): Response
    {
        abort_unless(
            $ambassador->status === config('constants.ambassador.status.active'),
            404,
        );

        return Inertia::render('ambassadors/Show', [
            'ambassador' => [
                'id' => $ambassador->id,
                'name' => $ambassador->name,
                'title' => $ambassador->title,
                'description' => $ambassador->description,
                'image_url' => $ambassador->image_url,
                'handle_linkedin' => $ambassador->handle_linkedin,
                'handle_facebook' => $ambassador->handle_facebook,
                'handle_instagram' => $ambassador->handle_instagram,
                'website_url' => $ambassador->website_url,
            ],
        ]);
    }
}
