@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings')
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all()
        : [];
    $webName = $settings['web_name'] ?? 'BigData สพป.ชพ.1';
@endphp

<x-layout>
    <x-slot:title>จัดการข้อมูลส่วนตัว | {{ $webName }}</x-slot>

    <main class="py-16 md:py-24 bg-slate-50/50 min-h-screen">
        <div class="h-8"></div>

        <!-- Profile Content Containers -->
        <section class="max-w-6xl mx-auto px-6 space-y-10">

            <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-100/30 p-8 md:p-12 transition-all duration-300">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>
    </main>
</x-layout>
