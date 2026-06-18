<?php

namespace Database\Seeders;

use App\Models\Ambassador;
use App\Models\AmbassadorImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AmbassadorSeeder extends Seeder
{
    /**
     * @return list<string>
     */
    private function ambassadorAssetPaths(): array
    {
        return collect(File::files(public_path('assets/ambassadors')))
            ->map(fn (\SplFileInfo $file): string => 'assets/ambassadors/'.$file->getFilename())
            ->values()
            ->all();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assetPaths = $this->ambassadorAssetPaths();

        $ambassador = Ambassador::query()->create([
            'name' => 'Somizi Mhlongo',
            'title' => 'Brand Ambassador, Big Tree Garden',
            'description' => <<<'TEXT'
"You Basically Become Immortal"
Somizi Mhlongo on Big Tree Garden

The reason I resonate with a brand such as Big Tree Garden, and this is personal, is because I wish I had something like this for my own family.
I come from a rich lineage. A culturally colourful, deeply meaningful family tree. But here's the thing: I don't know most of it. And that doesn't feel right to me.

From my dad's side, I've heard things apparently and I hate that word. Apparently. Because apparently means speculation. One person says this, another person says that. It could have been documented. It could have lived forever. Instead, I'm left wondering.

My grandfather on my father's side was a highly educated, deeply respected man. That translated into who my mother and her siblings became, but I know almost nothing about my mother's story. Where she was born. Where she came from. Who her friends were. And now it's too late.
Same with my name. I only found out later in life, through word of mouth, that Somizi was my great-great-great-grandfather's name. That's why I'm the only one with this name. It's not a common name. But imagine if that had been documented. Imagine if my daughter could read that story instead of just hearing it from someone who heard it from someone else.

That's exactly what Big Tree gives me now. An opportunity to change that.

People know the persona. They see me on TV, they hear me on radio, but there's a personal side that very few people know. Even my daughter doesn't know certain things about me. Now she can. And when I'm gone, she can tell her children. Not through word of mouth. Documented. She'll know the people who surrounded me, how they felt about me, how I made them feel.

This is more than a partnership to me. It's emotional. It's spiritual. It's a legacy that lives forever.

You basically become immortal, that's why I'm excited to partner with Big Tree Garden. It's beautiful, it's original, it's unique and it's genuinely groundbreaking.
TEXT,
            'status' => config('constants.ambassador.status.active'),
        ]);

        foreach ($assetPaths as $index => $assetPath) {
            AmbassadorImage::query()->create([
                'ambassador_id' => $ambassador->id,
                'image_path' => $assetPath,
                'caption' => $index === 0 ? 'Photo by Stills By Tom' : null,
                'sort_order' => $index,
            ]);
        }
    }
}
