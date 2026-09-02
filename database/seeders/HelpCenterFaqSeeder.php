<?php

namespace Database\Seeders;

use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HelpCenterFaqSeeder extends Seeder
{
    public const TOPIC_ID = '019f4c61-a19b-739d-a268-39be704354dc';

    public const AUTHOR_STAFF_USER_ID = '019f4c61-a4e9-73d9-8b7c-fd94d48a912f';

    public function run(): void
    {
        if (! HelpCenterTopic::query()->whereKey(self::TOPIC_ID)->exists()) {
            $this->command?->warn('Help center FAQ topic not found. Skipping HelpCenterFaqSeeder.');

            return;
        }

        foreach ($this->articles() as $article) {
            HelpCenterArticle::query()->updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'help_center_topic_id' => self::TOPIC_ID,
                    'author_staff_user_id' => self::AUTHOR_STAFF_USER_ID,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'body' => $article['body'],
                    'status' => config('constants.help_center_article.status.published'),
                    'published_at' => now(),
                    'sort_order' => $article['sort_order'],
                    'view_count' => 0,
                ],
            );
        }
    }

    /**
     * @return list<array{title: string, excerpt: string, body: string, sort_order: int}>
     */
    private function articles(): array
    {
        return [
            [
                'title' => 'What is Big Tree Garden?',
                'excerpt' => 'A digital legacy platform for preserving and sharing the stories of people you love.',
                'body' => '<p>Big Tree Garden is a digital legacy platform that helps families preserve and share the stories of people they love. Each memorial can hold memories, milestones, photos, videos, stories, and tributes in one lasting online space.</p>',
                'sort_order' => 0,
            ],
            [
                'title' => 'How does the QR code work?',
                'excerpt' => 'Scan a memorial QR code from a keepsake or pamphlet to open the linked memorial page.',
                'body' => '<p>A Big Tree Garden memorial can be linked to its own QR code. When the code is scanned from a tombstone, keepsake, pamphlet, card, or plaque, it opens the memorial page so family and community members can access the memories.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => 'What can I add to a memorial page?',
                'excerpt' => 'Add stories, photos, videos, milestones, tributes, and family memories you have permission to share.',
                'body' => '<p>You can add meaningful details such as the person\'s story, photos, videos, milestones, written tributes, and family memories. You should only upload content you own or have permission to use.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => 'What packages are available?',
                'excerpt' => 'Funeral Memorial, Memorial Legacy, and Living Legacy packages with once-off pricing from R750.',
                'body' => '<p>The current Pricing page lists Funeral Memorial at R750 once-off, Memorial Legacy at R4,500 once-off, and Living Legacy at R25,000 once-off. For custom requests or memorial-site-specific requirements, contact Big Tree Garden for confirmation.</p>',
                'sort_order' => 3,
            ],
            [
                'title' => 'What is included in the Funeral Memorial package?',
                'excerpt' => 'A digital memorial page, QR-coded pamphlet, photo gallery, and guest tributes for an individual service.',
                'body' => '<p>Funeral Memorial is a once-off digital memorial page and QR-coded funeral pamphlet for an individual service. It includes a personalised memorial page, printable QR-coded pamphlet, photo gallery, and guest tributes. It does not include a GPS memorial site or physical plaque.</p>',
                'sort_order' => 4,
            ],
            [
                'title' => 'What is included in the Memorial Legacy package?',
                'excerpt' => 'Everything in Funeral Memorial plus a physical QR plaque, GPS coordinates, and an on-site tribute.',
                'body' => '<p>Memorial Legacy includes everything in Funeral Memorial, plus a physical QR plaque installation, GPS memorial site coordinates, and a lasting on-site tribute.</p>',
                'sort_order' => 5,
            ],
            [
                'title' => 'What is included in the Living Legacy package?',
                'excerpt' => 'A secure digital vault for messages and media, with beneficiary access after your passing.',
                'body' => '<p>Living Legacy is a timeboxed digital vault for messages, photos, videos, voice notes, and documents. It includes secure storage, beneficiaries with secure access codes, and messages released to loved ones after your passing.</p>',
                'sort_order' => 6,
            ],
            [
                'title' => 'Can people search for a memorial publicly?',
                'excerpt' => 'Public search is opt-in only and turned off by default.',
                'body' => '<p>Only if the account holder chooses to make the memorial searchable. The default setting is off. A memorial appears in public search only after explicit opt-in.</p>',
                'sort_order' => 7,
            ],
            [
                'title' => 'Can I turn public search off later?',
                'excerpt' => 'Yes. Opt-in can be withdrawn and removal from search normally happens within 48 hours.',
                'body' => '<p>Yes. The opt-in can be withdrawn. The Terms and Privacy Policy say removal from search should happen within a reasonable period, normally within 48 hours.</p>',
                'sort_order' => 8,
            ],
            [
                'title' => 'Who owns uploaded photos, videos, and stories?',
                'excerpt' => 'You retain ownership. Big Tree Garden receives hosting and display permissions only.',
                'body' => '<p>You retain ownership of the content you upload. Big Tree Garden receives the permission needed to host, store, display, back up, format, and maintain that content as part of the service.</p>',
                'sort_order' => 9,
            ],
            [
                'title' => 'How are payments handled?',
                'excerpt' => 'Payments are processed securely through PayFast without storing full card details.',
                'body' => '<p>Payments are processed securely through PayFast or another payment gateway Big Tree Garden may use. Big Tree Garden does not store full card details.</p>',
                'sort_order' => 10,
            ],
            [
                'title' => 'Can I cancel or request a refund?',
                'excerpt' => 'Seven-day cancellation rights apply for services; plaque defects and other refunds are handled case by case.',
                'body' => '<p>The Terms say an electronic transaction for services may be cancelled within seven days after the agreement is concluded, without reason and without penalty, subject only to the direct cost of returning physical goods already delivered. If a plaque is defective within six months of delivery, the customer may choose repair, replacement, or refund. Other refund requests are considered case by case.</p>',
                'sort_order' => 11,
            ],
            [
                'title' => 'Is access to memorials always available?',
                'excerpt' => 'High availability is the goal, but uninterrupted access cannot be guaranteed.',
                'body' => '<p>Big Tree Garden aims to keep memorial websites highly available, but uninterrupted or error-free access cannot be guaranteed. Temporary interruptions may happen for maintenance, updates, security, or circumstances outside Big Tree Garden\'s reasonable control.</p>',
                'sort_order' => 12,
            ],
            [
                'title' => 'What personal information does Big Tree Garden collect?',
                'excerpt' => 'Account, contact, memorial, payment, support, and technical data needed to run the service.',
                'body' => '<p>Big Tree Garden may collect account and contact details, delivery information, memorial content, payment transaction information, support communications, optional search preferences, and technical or usage data needed to operate and improve the service.</p>',
                'sort_order' => 13,
            ],
            [
                'title' => 'Does Big Tree Garden sell personal information?',
                'excerpt' => 'No. Personal information is not sold, rented, or traded for marketing.',
                'body' => '<p>No. The Privacy Policy says Big Tree Garden does not sell, rent, or trade personal information to third parties for marketing purposes.</p>',
                'sort_order' => 14,
            ],
            [
                'title' => 'What privacy rights do users have?',
                'excerpt' => 'Under POPIA, users may request access, correction, deletion, objection, or lodge a complaint.',
                'body' => '<p>Under POPIA, users may request access, correction, deletion in certain circumstances, objection to certain processing, withdrawal of consent, or lodge a complaint. Privacy requests should be sent to the Information Officer.</p>',
                'sort_order' => 15,
            ],
            [
                'title' => 'Who can I contact for help?',
                'excerpt' => 'Email info@bigtreegarden.com, call 068 308 6703, or contact legal@bigtreegarden.com for privacy queries.',
                'body' => '<p>For general questions, contact info@bigtreegarden.com or call 068 308 6703. For privacy or POPIA queries, contact the Information Officer at legal@bigtreegarden.com.</p>',
                'sort_order' => 16,
            ],
        ];
    }
}
