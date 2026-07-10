<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            ['name' => 'Doves Funeral Services', 'website_url' => 'https://www.doves.co.za'],
            ['name' => 'AVBOB', 'website_url' => 'https://www.avbob.co.za'],
            ['name' => 'Martins Funerals', 'website_url' => 'https://www.martinsfunerals.co.za'],
            ['name' => 'Icebolethu Group', 'website_url' => 'https://www.icebolethugroup.co.za'],
            ['name' => 'Sowetan Memorials', 'website_url' => null],
            ['name' => 'Legacy Stoneworks', 'website_url' => null],
        ];

        foreach ($partners as $sortOrder => $partner) {
            Partner::query()->updateOrCreate(
                ['name' => $partner['name']],
                [
                    'logo_path' => $this->logoUrl($partner['name']),
                    'website_url' => $partner['website_url'],
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }

    private function logoUrl(string $name): string
    {
        return 'https://ui-avatars.com/api/?'.http_build_query([
            'name' => $name,
            'size' => 256,
            'background' => 'fbf8f2',
            'color' => '748f3a',
            'bold' => 'true',
            'format' => 'png',
        ]);
    }
}
