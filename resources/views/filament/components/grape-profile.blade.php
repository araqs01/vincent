@php
    $meta = $getRecord()->meta ?? [];
    if (is_string($meta)) {
        $meta = json_decode($meta, true) ?? [];
    }
    $p = $meta['grape_profile'] ?? [];
@endphp

@if(!empty($p))
    <div class="grid grid-cols-5 gap-2 text-sm">
        @foreach($p as $key => $val)
            <div>
                <strong>{{ ucfirst($key) }}</strong>
                <div class="w-full bg-gray-200 rounded h-2 mt-1">
                    <div class="bg-red-500 h-2 rounded" style="width: {{ ($val ?? 0) * 20 }}%"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ $val }}/5</div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-gray-500 text-sm">Профиль сортов пока не рассчитан.</p>
@endif
