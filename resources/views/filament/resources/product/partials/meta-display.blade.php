@php
    $meta = $getRecord()->meta ?? [];
    if (is_string($meta)) {
        $meta = json_decode($meta, true) ?? [];
    }

    // 🔧 Декодируем вложенные JSON-строки (например, taste_groups)
    foreach ($meta as $key => $value) {
        if (is_string($value) && str_starts_with(trim($value), '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $meta[$key] = $decoded;
            }
        }
    }
@endphp

<div class="space-y-6">

    {{-- 🍇 Вкусовые группы --}}
    @if(isset($meta['taste_groups']) && is_array($meta['taste_groups']))
        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-700">
            <h3 class="text-lg font-semibold text-gray-100 mb-3">Taste groups</h3>

            @php
                $groups = $meta['taste_groups'];
                foreach ($groups as $k => $v) {
                    if (is_string($v) && str_starts_with(trim($v), '{')) {
                        $decoded = json_decode($v, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $groups[$k] = $decoded;
                        }
                    }
                }
            @endphp

            <div class="grid grid-cols-2 gap-4">
                @foreach($groups as $groupName => $groupData)
                    <div class="bg-gray-800/60 rounded-lg p-3">
                        <div class="font-semibold text-amber-400 mb-2">{{ ucfirst($groupName) }}</div>

                        {{-- Если есть items --}}
                        @php
                            $items = $groupData['Items'] ?? $groupData['items'] ?? null;
                            if (is_string($items)) {
                                $decoded = json_decode($items, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $items = $decoded;
                                }
                            }
                        @endphp

                        @if(is_array($items))
                            <ul class="space-y-1 text-sm">
                                @foreach($items as $tasteId => $tasteInfo)
                                    @php
                                        if (is_string($tasteInfo)) {
                                            $tasteInfo = json_decode($tasteInfo, true);
                                        }
                                        $tasteName = $tasteInfo['name'] ?? $tasteInfo['Name'] ?? '—';
                                        $percent = $tasteInfo['percent'] ?? $tasteInfo['Percent'] ?? null;
                                    @endphp

                                    <li>
                                        <div class="flex justify-between text-gray-200">
                                            <span>{{ ucfirst($tasteName) }}</span>
                                            <span class="text-amber-400 font-medium">{{ $percent ?? '' }}%</span>
                                        </div>
                                        @if($percent)
                                            <div class="w-full bg-gray-700 rounded-full h-1.5 mt-1">
                                                <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ min($percent, 100) }}%"></div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Итоговый процент группы --}}
                        @if(isset($groupData['Group_percent']))
                            <div class="mt-2 text-xs text-gray-400">
                                Group intensity: {{ $groupData['Group_percent'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 🌿 Профиль сортов --}}
    @if(isset($meta['grape_profile']) && is_array($meta['grape_profile']))
        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-700">
            <h3 class="text-lg font-semibold text-gray-100 mb-3">Grape profile</h3>

            <div class="grid grid-cols-5 gap-4 text-sm">
                @foreach($meta['grape_profile'] as $key => $val)
                    <div>
                        <div class="text-gray-300">{{ ucfirst($key) }}</div>
                        <div class="w-full bg-gray-700 rounded-full h-2 mt-1">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min($val * 20, 100) }}%"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">{{ $val }}/5</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ⭐ Vivino рейтинг --}}
    @if(isset($meta['vivino_rating']))
        <div class="bg-gray-900/60 p-3 rounded-xl border border-gray-700">
            <div class="flex justify-between items-center text-gray-300">
                <span class="font-medium">Vivino rating</span>
                <span class="text-amber-400 font-semibold">{{ $meta['vivino_rating'] }}</span>
            </div>
        </div>
    @endif

</div>
