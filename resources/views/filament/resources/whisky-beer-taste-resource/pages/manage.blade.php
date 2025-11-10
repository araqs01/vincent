<x-filament-panels::page>
    <x-filament::tabs label="Вкусы и группы напитков">
        @foreach(static::getTabs() as $key => $tab)
            <x-filament::tabs.item :label="$tab->getLabel()" :icon="$tab->getIcon()" :active="$loop->first">
                {{ $tab->getSchema()[0] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>
</x-filament-panels::page>
