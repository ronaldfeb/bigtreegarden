<?php

namespace App\Console\Commands;

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use App\Models\CrmContact;
use Illuminate\Console\Command;

class GenerateCrmRenewalFollowUps extends Command
{
    protected $signature = 'crm:generate-renewal-follow-ups {--days=30 : Days ahead to look for renewals}';

    protected $description = 'Create renewal follow-ups for CRM contacts whose linked subscription renews soon.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $window = now()->addDays($days);

        $contacts = CrmContact::query()
            ->whereNotNull('user_id')
            ->whereHas('user.subscriptions', function ($query) use ($window) {
                $query->active()
                    ->whereNotNull('next_billing_at')
                    ->where('next_billing_at', '<=', $window);
            })
            ->with(['user' => fn ($query) => $query->with(['subscriptions' => fn ($sub) => $sub->active()])])
            ->get();

        $created = 0;

        foreach ($contacts as $contact) {
            $subscription = $contact->user?->subscriptions
                ->whereNotNull('next_billing_at')
                ->sortBy('next_billing_at')
                ->first();

            if ($subscription === null) {
                continue;
            }

            $alreadyScheduled = $contact->followUps()
                ->where('type', CrmFollowUpType::Renewal)
                ->where('status', CrmFollowUpStatus::Pending)
                ->exists();

            if ($alreadyScheduled) {
                continue;
            }

            $contact->followUps()->create([
                'assigned_staff_user_id' => $contact->relationship_owner_staff_user_id,
                'type' => CrmFollowUpType::Renewal,
                'status' => CrmFollowUpStatus::Pending,
                'title' => 'Subscription renewal check-in',
                'notes' => 'Auto-generated: subscription renews on '.$subscription->next_billing_at->format('d M Y').'.',
                'due_at' => $subscription->next_billing_at,
            ]);

            $created++;
        }

        $this->info("Created {$created} renewal follow-up(s).");

        return self::SUCCESS;
    }
}
