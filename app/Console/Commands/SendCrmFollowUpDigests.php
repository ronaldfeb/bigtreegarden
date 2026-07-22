<?php

namespace App\Console\Commands;

use App\Mail\CrmFollowUpDigestMail;
use App\Models\CrmFollowUp;
use App\Models\StaffUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCrmFollowUpDigests extends Command
{
    protected $signature = 'crm:send-follow-up-digests';

    protected $description = 'Email each staff member a digest of their due and overdue CRM follow-ups.';

    public function handle(): int
    {
        $grouped = CrmFollowUp::query()
            ->due()
            ->whereNotNull('assigned_staff_user_id')
            ->with(['assignedStaffUser.user', 'subject'])
            ->get()
            ->groupBy('assigned_staff_user_id');

        $sent = 0;

        foreach ($grouped as $followUps) {
            /** @var StaffUser|null $staffUser */
            $staffUser = $followUps->first()->assignedStaffUser;
            $email = $staffUser?->user?->email;

            if ($staffUser === null || ! $staffUser->is_active || $email === null) {
                continue;
            }

            Mail::to($email)->send(new CrmFollowUpDigestMail($staffUser, $followUps));
            $sent++;
        }

        $this->info("Sent {$sent} follow-up digest(s).");

        return self::SUCCESS;
    }
}
