@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings')
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all()
        : [];
    $webName = $settings['web_name'] ?? 'EE.CPN1';
@endphp

<x-layout>
    <x-slot:title>จัดการข้อมูลส่วนตัว | {{ $webName }}</x-slot>

    <main class="py-16 md:py-24 bg-slate-50/50 min-h-screen">
        <div class="h-8"></div>

        <!-- Profile Content Containers -->
        <section class="max-w-6xl mx-auto px-6 space-y-10">

            @if(in_array(auth()->user()->role ?? '', ['teacher', 'admin']) || $teacherProfile)
                <!-- Teacher Profile Edit Card -->
                <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-100/30 p-8 md:p-12 transition-all duration-300">
                    @include('profile.partials.update-teacher-form')
                </div>
            @else
                <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-100/30 p-8 md:p-12 text-center text-slate-500 py-20">
                    <i class="fa-solid fa-user-lock text-4xl text-slate-300 mb-4 block"></i>
                    <p class="font-extrabold text-slate-700 text-sm">ไม่พบข้อมูลประวัติการปฏิบัติหน้าที่ครูของคุณ</p>
                    <p class="text-xs text-slate-400 mt-2">กรุณาติดต่อผู้ดูแลระบบเพื่อกำหนดบทบาทการใช้งานเป็นครู</p>
                </div>
            @endif
        </section>
    </main>
</x-layout>
