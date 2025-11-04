<?php

namespace App\Services;

use App\Models\Pairing;
use Illuminate\Support\Str;

class ProductPairingService
{
    public static function attachPairings($product, ?string $text): void
    {
        if (empty($text)) {
            return;
        }

        $text = preg_replace('/\s{2,}/u', ' ', trim($text));

        $parts = collect(
            preg_split('/[,;\/]+|\s+(и|с|для)\s+|\s+/ui', $text)
        )
            ->map(fn($v) => trim($v))
            ->filter(fn($v) => mb_strlen($v) > 1)
            ->unique()
            ->values();

        if ($parts->isEmpty()) {
            return;
        }

        $pairingIds = [];

        foreach ($parts as $name) {
            $normalized = ucfirst(mb_strtolower($name));

            $pairing = \App\Models\Pairing::firstOrCreate(
                ['name->ru' => $normalized],
                ['name' => ['ru' => $normalized, 'en' => $normalized]]
            );

            $pairingIds[] = $pairing->id;
        }

        $product->pairings()->syncWithoutDetaching($pairingIds);
    }
}
