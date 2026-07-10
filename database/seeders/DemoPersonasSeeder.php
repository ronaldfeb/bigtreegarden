<?php

namespace Database\Seeders;

use App\Enums\MemorialPageStatus;
use App\Enums\PamphletStatus;
use App\Enums\PersonOfInterestStatus;
use App\Enums\StaffRole;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletStyle;
use App\Models\MemorialSite;
use App\Models\PersonOfInterest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultPost;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderUser;
use App\Models\StaffUser;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoPersonasSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $password = Hash::make(self::DEMO_PASSWORD);
        $background = MemorialPagePamphletBackground::query()->first()
            ?? MemorialPagePamphletBackground::factory()->create();

        $this->seedStaffAdmin($password);
        $this->seedMemorialOwner($password, $background);
        $this->seedVisitor($password);
        $this->seedVaultSubscriber($password);
        $this->seedServiceProviders($password);
    }

    private function seedStaffAdmin(string $password): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'staff@bigtreegarden.test'],
            [
                'name' => 'Staff Admin',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        StaffUser::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'role' => StaffRole::Admin,
                'job_title' => 'Platform Administrator',
                'is_active' => true,
            ],
        );
    }

    private function seedMemorialOwner(string $password, MemorialPagePamphletBackground $background): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'memorial@bigtreegarden.test'],
            [
                'name' => 'Memorial Owner',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $person = PersonOfInterest::query()->updateOrCreate(
            ['public_slug' => 'james-morrison'],
            [
                'created_by_user_id' => $user->id,
                'first_name' => 'James',
                'last_name' => 'Morrison',
                'display_name' => 'James Morrison',
                'date_of_birth' => '1945-03-12',
                'date_of_passing' => now()->subDays(7)->toDateString(),
                'place_of_birth' => 'Cape Town',
                'place_of_passing' => 'Johannesburg',
                'status' => PersonOfInterestStatus::Active,
            ],
        );

        $user->personsOfInterest()->syncWithoutDetaching([
            $person->id => ['role' => 'owner'],
        ]);

        $memorialPage = MemorialPage::query()->updateOrCreate(
            ['public_slug' => 'memorial-demo'],
            [
                'person_of_interest_id' => $person->id,
                'title' => 'In loving memory of James Morrison',
                'status' => MemorialPageStatus::Published,
                'active_day_date' => now()->toDateString(),
                'live_comments_enabled' => true,
                'obituary' => 'James Morrison was a devoted husband, father, and community leader who touched countless lives.',
                'funeral_programme' => "10:00 — Arrival\n10:30 — Opening hymn\n11:00 — Tributes\n12:00 — Committal",
                'hymns' => "Amazing Grace\nAbide With Me",
                'gallery_enabled' => true,
                'published_at' => now(),
            ],
        );

        $pamphlet = MemorialPagePamphlet::query()->updateOrCreate(
            ['memorial_page_id' => $memorialPage->id],
            [
                'background_id' => $background->id,
                'heading' => 'In loving memory',
                'short_text' => 'Forever in our hearts.',
                'date_format' => 'd M Y',
                'image_shape' => 'square',
                'image_crop_mode' => 'cover',
                'uploaded_image_path' => 'pamphlets/images/demo-memorial.jpg',
                'status' => PamphletStatus::Published,
                'paid_at' => now()->subWeek(),
            ],
        );

        MemorialPagePamphletStyle::query()->updateOrCreate(
            ['pamphlet_id' => $pamphlet->id],
            [
                'font_family' => 'serif',
                'is_bold' => false,
                'is_italic' => false,
                'date_format' => 'd M Y',
            ],
        );

        MemorialSite::query()->updateOrCreate(
            [
                'person_of_interest_id' => $person->id,
                'name' => 'Westpark Cemetery',
            ],
            [
                'site_type' => 'burial',
                'description' => 'Main memorial site for GPS-gated flowers.',
                'address' => 'Beyers Naudé Drive, Johannesburg',
                'latitude' => -26.2041000,
                'longitude' => 28.0473000,
                'geofence_radius_m' => 50,
            ],
        );
    }

    private function seedVisitor(string $password): void
    {
        User::query()->updateOrCreate(
            ['email' => 'visitor@bigtreegarden.test'],
            [
                'name' => 'Memorial Visitor',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $memorialPage = MemorialPage::query()->where('public_slug', 'memorial-demo')->first();

        if ($memorialPage === null) {
            return;
        }

        $visitor = User::query()->where('email', 'visitor@bigtreegarden.test')->first();

        $message = MemorialPageMessage::query()->updateOrCreate(
            [
                'memorial_page_id' => $memorialPage->id,
                'author_user_id' => $visitor?->id,
                'context' => 'flowers',
                'body' => 'Thinking of you and your family today. Rest in peace, James.',
            ],
            [
                'type' => 'text',
                'status' => 'pending',
                'is_gps_verified' => true,
                'posted_latitude' => -26.2041000,
                'posted_longitude' => 28.0473000,
            ],
        );

        if ($visitor !== null && $message->transaction_id === null) {
            $transaction = Transaction::query()->create([
                'user_id' => $visitor->id,
                'payable_type' => MemorialPageMessage::class,
                'payable_id' => $message->id,
                'type' => TransactionType::FlowerMessage,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => config('memorial.flower_price_cents', 500),
                'currency' => 'ZAR',
                'status' => TransactionStatus::Complete,
                'paid_at' => now(),
            ]);

            $message->update(['transaction_id' => $transaction->id]);
        }
    }

    private function seedVaultSubscriber(string $password): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'vault@bigtreegarden.test'],
            [
                'name' => 'Vault Subscriber',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $package = SubscriptionPackage::query()
            ->where('slug', 'vault-monthly')
            ->first()
            ?? SubscriptionPackage::query()->where('billing_interval', 'monthly')->first();

        if ($package !== null) {
            Subscription::query()->updateOrCreate(
                ['user_id' => $user->id, 'subscription_package_id' => $package->id],
                [
                    'status' => SubscriptionStatus::Active,
                    'payfast_token' => 'demo-payfast-token',
                    'merchant_reference' => 'demo-vault-subscription',
                    'activated_at' => now()->subMonth(),
                    'next_billing_at' => now()->addMonth(),
                ],
            );
        }

        $sealedPerson = PersonOfInterest::query()->updateOrCreate(
            ['public_slug' => 'elizabeth-van-der-merwe'],
            [
                'created_by_user_id' => $user->id,
                'first_name' => 'Elizabeth',
                'last_name' => 'van der Merwe',
                'display_name' => 'Elizabeth van der Merwe',
                'date_of_birth' => '1950-08-20',
                'date_of_passing' => '2024-11-03',
                'status' => PersonOfInterestStatus::Active,
            ],
        );

        $user->personsOfInterest()->syncWithoutDetaching([
            $sealedPerson->id => ['role' => 'owner'],
        ]);

        PersonOfInterestVault::query()->updateOrCreate(
            ['person_of_interest_id' => $sealedPerson->id],
            [
                'name' => "Elizabeth's Vault",
                'status' => 'sealed',
                'storage_limit_mb' => 1024,
            ],
        );

        $releasedPerson = PersonOfInterest::query()->updateOrCreate(
            ['public_slug' => 'robert-sithole'],
            [
                'created_by_user_id' => $user->id,
                'first_name' => 'Robert',
                'last_name' => 'Sithole',
                'display_name' => 'Robert Sithole',
                'date_of_birth' => '1948-01-15',
                'date_of_passing' => '2023-06-22',
                'status' => PersonOfInterestStatus::Active,
            ],
        );

        $user->personsOfInterest()->syncWithoutDetaching([
            $releasedPerson->id => ['role' => 'owner'],
        ]);

        $releasedVault = PersonOfInterestVault::query()->updateOrCreate(
            ['person_of_interest_id' => $releasedPerson->id],
            [
                'name' => "Robert's Legacy Vault",
                'status' => 'released',
                'released_at' => now()->subWeek(),
                'storage_limit_mb' => 1024,
            ],
        );

        $beneficiary = PersonOfInterestVaultBeneficiary::query()->updateOrCreate(
            [
                'vault_id' => $releasedVault->id,
                'email' => 'beneficiary@bigtreegarden.test',
            ],
            [
                'type' => 'beneficiary',
                'full_name' => 'Nomsa Sithole',
                'contact_number' => '+27 82 555 0101',
                'physical_address' => '12 Oak Street, Pretoria',
                'access_code_hash' => Hash::make('BENEFICIARY1'),
                'access_code_hint' => 'BE******R1',
            ],
        );

        $post = PersonOfInterestVaultPost::query()->updateOrCreate(
            [
                'vault_id' => $releasedVault->id,
                'title' => 'A letter for you',
            ],
            [
                'author_user_id' => $user->id,
                'body' => 'My dearest Nomsa, if you are reading this, please know how proud I was of the life we built together.',
                'visibility' => 'selected',
            ],
        );

        $post->beneficiaries()->sync([$beneficiary->id]);
    }

    private function seedServiceProviders(string $password): void
    {
        $activeUser = User::query()->updateOrCreate(
            ['email' => 'provider@bigtreegarden.test'],
            [
                'name' => 'Provider Manager',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $activeProvider = ServiceProvider::query()->updateOrCreate(
            ['slug' => 'sunrise-funeral-services'],
            [
                'name' => 'Sunrise Funeral Services',
                'registration_number' => '2020/123456/07',
                'description' => 'Compassionate funeral and memorial services across Gauteng.',
                'email' => 'hello@sunrisefunerals.test',
                'phone' => '+27 11 555 0202',
                'website_url' => 'https://sunrisefunerals.test',
                'physical_address' => '45 Memorial Drive',
                'city' => 'Johannesburg',
                'province' => 'Gauteng',
                'status' => config('constants.service_provider.status.active'),
            ],
        );

        ServiceProviderUser::query()->updateOrCreate(
            [
                'service_provider_id' => $activeProvider->id,
                'user_id' => $activeUser->id,
            ],
            ['role' => 'owner'],
        );

        $pendingUser = User::query()->updateOrCreate(
            ['email' => 'provider-pending@bigtreegarden.test'],
            [
                'name' => 'Pending Provider',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $pendingProvider = ServiceProvider::query()->updateOrCreate(
            ['slug' => 'heritage-memorials'],
            [
                'name' => 'Heritage Memorials',
                'description' => 'Awaiting platform approval.',
                'email' => 'info@heritagememorials.test',
                'phone' => '+27 21 555 0303',
                'city' => 'Cape Town',
                'province' => 'Western Cape',
                'status' => config('constants.service_provider.status.pending'),
            ],
        );

        ServiceProviderUser::query()->updateOrCreate(
            [
                'service_provider_id' => $pendingProvider->id,
                'user_id' => $pendingUser->id,
            ],
            ['role' => 'owner'],
        );
    }
}
