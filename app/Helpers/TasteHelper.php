<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class TasteHelper
{
    public static function detectGroup(string $name): ?string
    {
        $dictionary = config('taste_dictionary');
        $nameLower = Str::lower(trim($name));

        foreach ($dictionary as $groupSlug => $groupData) {
            foreach ($groupData['names'] as $item) {
                $en = Str::lower($item['en']);
                $ru = Str::lower($item['ru']);

                if (Str::contains($nameLower, $en) || Str::contains($nameLower, $ru)) {
                    return $groupSlug;
                }
            }
        }

        return null; // не нашли — пусть создаётся без группы
    }

    public static function translate(string $name, string $targetLang = 'ru'): ?string
    {
        $dictionary = config('taste_dictionary');
        $nameLower = Str::lower(trim($name));

        foreach ($dictionary as $groupData) {
            foreach ($groupData['names'] as $item) {
                if (Str::contains($nameLower, Str::lower($item['en']))) {
                    return $item[$targetLang] ?? $name;
                }
                if (Str::contains($nameLower, Str::lower($item['ru']))) {
                    return $item[$targetLang] ?? $name;
                }
            }
        }

        return $name; // fallback
    }
}
