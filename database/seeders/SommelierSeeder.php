<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SommelierGroup;
use App\Models\SommelierTag;
use Illuminate\Support\Str;

class SommelierSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'ощущения' => 'легкое, утонченное, элегантное, изящное, сочное, гармоничное, объемное, сложное',
            'интенсивность' => 'нейтральное, сдержанное, ароматное, выразительное, яркое',
            'танины/кислотность' => 'мягкотелое, освежающее, живое, мощное, зеленое, резкое, терпкое, твердое, сухое',
            'финал' => 'короткий, развитый, длительный',
        ];

        $order = 1;
        foreach ($data as $groupName => $tagsString) {
            $group = SommelierGroup::firstOrCreate(
                ['slug' => Str::slug($groupName)],
                [
                    'name' => ['ru' => ucfirst($groupName), 'en' => ucfirst($groupName)],
                    'order_index' => $order++,
                ]
            );

            $tags = collect(explode(',', $tagsString))
                ->map(fn($v) => trim($v))
                ->filter()
                ->values();

            $i = 1;
            foreach ($tags as $tag) {
                SommelierTag::firstOrCreate(
                    ['group_id' => $group->id, 'slug' => Str::slug($tag)],
                    [
                        'name' => ['ru' => ucfirst($tag), 'en' => ucfirst($tag)],
                        'order_index' => $i++,
                    ]
                );
            }
        }
    }
}
