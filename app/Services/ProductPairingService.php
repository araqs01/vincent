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

        // Пример: "Сыр, Мясо, Десерты"
        $items = collect(explode(',', $text))
            ->map(fn ($i) => trim($i))
            ->filter()
            ->unique();

        if ($items->isEmpty()) {
            return;
        }

        $pairingIds = [];

        foreach ($items as $name) {
            $pairing = Pairing::firstOrCreate(
                ['name->ru' => $name],
                ['name' => ['ru' => $name, 'en' => $name]]
            );

            $pairingIds[] = $pairing->id;
        }

        // Привязываем без дубликатов
        $product->pairings()->syncWithoutDetaching($pairingIds);
    }
}
