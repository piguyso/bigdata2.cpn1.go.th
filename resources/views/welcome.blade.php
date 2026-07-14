@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'BigData สพป.ชพ.1';
@endphp

<x-layout>
    <x-slot:title>
        {{ $webName === 'BigData สพป.ชพ.1' ? 'ฐานข้อมูล BigData สพป.ชพ. 1' : $webName }}
    </x-slot>

    <!-- Empty container to hold only header & footer -->
    <div class="py-24 min-h-[60vh] flex items-center justify-center bg-slate-50/50">
        <div class="text-center space-y-4">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                ยินดีต้อนรับสู่ {{ $webName }}
            </h1>
            <p class="text-slate-500 text-sm font-medium">
                ระบบกำลังอยู่ในระหว่างการปรับปรุงและอัปเดตข้อมูล
            </p>
        </div>
    </div>
</x-layout>