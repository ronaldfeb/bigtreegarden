<?php

namespace App\Services;

use App\Enums\MemorialPageStatus;
use App\Enums\PamphletStatus;
use App\Enums\PersonOfInterestStatus;
use App\Mail\FamilyMemorialReadyMail;
use App\Models\MemorialPagePamphlet;
use App\Models\PersonOfInterest;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;

class ProviderMemorialCreationService
{
    public function __construct(
        private ServiceProviderCreditService $creditService,
        private QrCodeService $qrCodeService,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(
        ServiceProvider $serviceProvider,
        User $actor,
        array $validated,
        string $imagePath,
    ): MemorialPagePamphlet {
        if ($serviceProvider->credits_remaining < 1) {
            throw new RuntimeException('Insufficient memorial page credits.');
        }

        return DB::transaction(function () use ($serviceProvider, $actor, $validated, $imagePath): MemorialPagePamphlet {
            [$firstName, $lastName] = $this->splitFullName($validated['person_full_name']);

            $familyUser = $this->findOrCreateFamilyUser(
                $validated['family_contact_name'],
                $validated['family_contact_email'],
            );

            $personOfInterest = PersonOfInterest::query()->create([
                'created_by_user_id' => $actor->id,
                'service_provider_id' => $serviceProvider->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => $validated['person_full_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'date_of_passing' => $validated['date_of_passing'],
                'profile_image_path' => $imagePath,
                'public_slug' => Str::lower((string) Str::ulid()),
                'status' => PersonOfInterestStatus::Draft,
            ]);

            $personOfInterest->users()->syncWithoutDetaching([
                $familyUser->id => ['role' => 'owner'],
            ]);

            $memorialPage = $personOfInterest->memorialPages()->create([
                'title' => $validated['heading'],
                'public_slug' => Str::lower((string) Str::ulid()),
                'status' => MemorialPageStatus::Draft,
                'published_at' => null,
            ]);

            $usesProviderBackground = ($validated['background_source'] ?? null) === 'provider';

            $pamphlet = $memorialPage->pamphlet()->create([
                'background_id' => $usesProviderBackground ? null : $validated['background_id'],
                'service_provider_background_id' => $usesProviderBackground
                    ? $validated['service_provider_background_id']
                    : null,
                'status' => PamphletStatus::Paid,
                'paid_at' => now(),
                'heading' => $validated['heading'],
                'short_text' => $validated['short_text'],
                'date_format' => $validated['date_format'],
                'image_shape' => $validated['image_shape'],
                'image_crop_mode' => $validated['image_crop_mode'],
                'uploaded_image_path' => $imagePath,
            ]);

            $pamphlet->style()->create([
                'font_family' => $validated['font_family'] ?? 'Georgia',
                'is_bold' => false,
                'is_italic' => false,
                'date_format' => $validated['date_format'],
                'heading_color' => $validated['heading_color'],
                'name_color' => $validated['name_color'],
                'short_text_color' => $validated['short_text_color'],
                'dates_color' => $validated['dates_color'],
            ]);

            $this->creditService->consumeCredit($serviceProvider, $memorialPage, $actor);

            $targetUrl = route('memorial.public.show', $memorialPage->public_slug);
            $personOfInterest->update([
                'qr_code_path' => $this->qrCodeService->imageUrlForTarget($targetUrl),
                'qr_generated_at' => now(),
            ]);

            Mail::to($familyUser)->send(new FamilyMemorialReadyMail(
                $familyUser,
                $personOfInterest->fresh(),
                $serviceProvider,
            ));

            return $pamphlet->fresh(['memorialPage.personOfInterest', 'background', 'serviceProviderBackground', 'style'])
                ?? $pamphlet;
        });
    }

    private function findOrCreateFamilyUser(string $name, string $email): User
    {
        $normalizedEmail = Str::lower($email);
        $existing = User::query()->whereRaw('lower(email) = ?', [$normalizedEmail])->first();

        if ($existing !== null) {
            return $existing;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $normalizedEmail,
            'password' => Str::password(32),
        ]);

        Password::broker()->sendResetLink(['email' => $user->email]);

        return $user;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFullName(string $fullName): array
    {
        $trimmed = trim($fullName);
        $parts = preg_split('/\s+/', $trimmed, 2) ?: [];

        return [
            $parts[0] ?? $trimmed,
            $parts[1] ?? '',
        ];
    }
}
