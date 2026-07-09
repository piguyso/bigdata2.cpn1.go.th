@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'EE.CPN1';
@endphp
<x-layout>
    <x-slot:title>{{ $course->title }} | {{ $webName }}</x-slot>

    <!-- Custom Style Definitions -->
    <style>
        /* Smooth Scroll Reveal Animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform, opacity;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    @php
        // Generate Fallback Banner Image depending on Title keywords if cover_image is missing
        $bannerUrl = $course->cover_image_url;
        if (!$bannerUrl) {
            $titleLower = mb_strtolower($course->title);
            if (str_contains($titleLower, 'ai') || str_contains($titleLower, 'ปัญญาประดิษฐ์') || str_contains($titleLower, 'คอมพิวเตอร์') || str_contains($titleLower, 'เทคโนโลยี') || str_contains($titleLower, 'ดิจิทัล')) {
                $bannerUrl = 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=1200&q=80';
            } elseif (str_contains($titleLower, 'วิทย') || str_contains($titleLower, 'โลก') || str_contains($titleLower, ' climate') || str_contains($titleLower, 'สิ่งแวดล้อม') || str_contains($titleLower, 'สเต็ม') || str_contains($titleLower, 'stem')) {
                $bannerUrl = 'https://images.unsplash.com/photo-1507668077129-56e32842fceb?auto=format&fit=crop&w=1200&q=80';
            } elseif (str_contains($titleLower, 'คณิต') || str_contains($titleLower, 'คำนวณ') || str_contains($titleLower, 'วิเคราะห์') || str_contains($titleLower, 'คิด') || str_contains($titleLower, 'pisa')) {
                $bannerUrl = 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=1200&q=80';
            } else {
                $bannerUrl = 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=1200&q=80';
            }
        }
    @endphp

    <main class="py-12 md:py-16 space-y-10" x-data="{ activeLightboxImage: null }">
        <!-- Back Navigation & Header breadcrumb -->
        <section class="max-w-7xl mx-auto px-6">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <a href="/" class="hover:text-emerald-600 transition">หน้าหลัก</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <a href="/#courses" class="hover:text-emerald-600 transition">หลักสูตรฝึกอบรม</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-slate-600 truncate max-w-[200px] md:max-w-xs">{{ $course->title }}</span>
            </div>
            <a href="/#courses" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-emerald-600 transition group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i> ย้อนกลับไปหน้าหลัก
            </a>
        </section>

        <!-- Course Immersive Banner -->
        <section class="max-w-7xl mx-auto px-6 reveal active">
            <div class="h-64 md:h-[400px] w-full rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-lg relative group">
                <img src="{{ $bannerUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-101 transition duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/40 to-transparent"></div>
                
                <!-- Tag on top of image -->
                <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 text-left space-y-3 max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($course->status === 'open')
                            <span class="px-3 py-1.5 bg-emerald-500 text-white font-bold rounded-xl text-[10px] uppercase tracking-wider shadow-md shadow-emerald-950/20">เปิดรับสมัคร</span>
                        @elseif($course->status === 'ongoing')
                            <span class="px-3 py-1.5 bg-sky-500 text-white font-bold rounded-xl text-[10px] uppercase tracking-wider shadow-md shadow-sky-950/20">กำลังดำเนินการ</span>
                        @elseif($course->status === 'upcoming')
                            <span class="px-3 py-1.5 bg-amber-500 text-white font-bold rounded-xl text-[10px] uppercase tracking-wider shadow-md shadow-amber-950/20">เตรียมเปิดสมัคร</span>
                        @else
                            <span class="px-3 py-1.5 bg-slate-600 text-white font-bold rounded-xl text-[10px] uppercase tracking-wider shadow-md">เสร็จสิ้นโครงการ</span>
                        @endif
                        <span class="px-3 py-1 bg-white/10 backdrop-blur-md text-emerald-300 font-bold rounded-lg text-[10px] border border-white/10 uppercase" x-text="'{{ $course->hours }} ชั่วโมงอบรม'"></span>
                    </div>
                    <h1 class="text-xl md:text-3xl lg:text-4xl font-extrabold text-white leading-tight tracking-tight shadow-sm">{{ $course->title }}</h1>
                </div>
            </div>
        </section>

        <!-- Course Grid Split Details -->
        <section class="max-w-7xl mx-auto px-6 reveal">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 md:gap-12 items-start">
                
                <!-- Left Details (Description, Objectives, Reports) -->
                <div class="lg:col-span-8 space-y-10">
                    
                    <!-- Objectives -->
                    <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-4 text-left">
                        <h3 class="text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-emerald-500"></i> รายละเอียดและวัตถุประสงค์โครงการ
                        </h3>
                        <div class="text-slate-600 text-xs md:text-sm leading-relaxed whitespace-pre-line bg-slate-50/40 p-5 rounded-2xl border border-slate-100/50">
                            {{ $course->objectives ?: 'ไม่มีรายละเอียดวัตถุประสงค์เพิ่มเติมสำหรับหลักสูตรนี้' }}
                        </div>
                    </div>

                    <!-- IF CLOSED: Show Project Achievement Report, Event Photos & Attachments -->
                    @if($course->status === 'closed' || $course->report_text || count($course->report_images_urls) > 0 || count($course->report_files_urls) > 0)
                        <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6 text-left">
                            <h3 class="text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-square-poll-vertical text-emerald-500"></i> รายงานสรุปผลสัมฤทธิ์โครงการ
                            </h3>

                            <!-- Report text -->
                            @if($course->report_text)
                                <div class="text-slate-655 text-xs md:text-sm leading-relaxed whitespace-pre-line p-5 bg-emerald-50/20 border border-emerald-500/10 rounded-2xl text-slate-600">
                                    {{ $course->report_text }}
                                </div>
                            @else
                                <div class="text-slate-400 text-xs italic bg-slate-50 p-4 border border-slate-100 rounded-2xl">
                                    กิจกรรมการอบรมได้เสร็จสิ้นเรียบร้อยแล้ว คณะทำงานกำลังดำเนินการสรุปรายงานผลโครงการ
                                </div>
                            @endif

                            <!-- Report Images Gallery Grid -->
                            @if(count($course->report_images_urls) > 0)
                                <div class="space-y-3 pt-4">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">ภาพบันทึกกิจกรรมโครงการ (Project Gallery)</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach($course->report_images_urls as $imgUrl)
                                            <div class="relative w-full h-28 rounded-2xl overflow-hidden border border-slate-100 hover:border-emerald-500/30 hover:shadow-md transition shadow-inner cursor-pointer group"
                                                 @click="activeLightboxImage = '{{ $imgUrl }}'">
                                                <img src="{{ $imgUrl }}" alt="Activity Photo" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition duration-300 flex items-center justify-center">
                                                    <i class="fa-solid fa-magnifying-glass-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-base"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Report Files list -->
                            @if(count($course->report_files_urls) > 0)
                                <div class="space-y-3 pt-4 border-t border-slate-100/60">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">เอกสารสรุปผล / ดาวน์โหลดเอกสารแนบ (Attachments)</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($course->report_files_urls as $file)
                                            @php
                                                $fileName = $file['name'];
                                                $fileNameLower = strtolower($fileName);
                                                $icon = 'fa-file';
                                                $color = 'text-slate-400 bg-slate-50 border-slate-100';
                                                if (str_ends_with($fileNameLower, '.pdf')) {
                                                    $icon = 'fa-file-pdf';
                                                    $color = 'text-rose-500 bg-rose-50/50 border-rose-100/50';
                                                } elseif (str_ends_with($fileNameLower, '.doc') || str_ends_with($fileNameLower, '.docx')) {
                                                    $icon = 'fa-file-word';
                                                    $color = 'text-blue-500 bg-blue-50/50 border-blue-100/50';
                                                } elseif (str_ends_with($fileNameLower, '.xls') || str_ends_with($fileNameLower, '.xlsx')) {
                                                    $icon = 'fa-file-excel';
                                                    $color = 'text-emerald-600 bg-emerald-50/50 border-emerald-100/50';
                                                } elseif (str_ends_with($fileNameLower, '.zip') || str_ends_with($fileNameLower, '.rar')) {
                                                    $icon = 'fa-file-zipper';
                                                    $color = 'text-amber-500 bg-amber-50/50 border-amber-100/50';
                                                } elseif (str_ends_with($fileNameLower, '.ppt') || str_ends_with($fileNameLower, '.pptx')) {
                                                    $icon = 'fa-file-powerpoint';
                                                    $color = 'text-orange-500 bg-orange-50/50 border-orange-100/50';
                                                }
                                            @endphp
                                            <a href="{{ $file['url'] }}" download="{{ $file['name'] }}" target="_blank" class="flex items-center justify-between p-3.5 bg-slate-50/60 rounded-2xl border border-slate-100 group hover:border-emerald-500/20 hover:bg-emerald-50/10 transition duration-200">
                                                <div class="flex items-center gap-3 overflow-hidden flex-1">
                                                    <div class="w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 text-sm shadow-sm {{ $color }}">
                                                        <i class="fa-solid {{ $icon }}"></i>
                                                    </div>
                                                    <div class="overflow-hidden flex-1 pr-2">
                                                        <span class="block text-xs font-bold text-slate-700 truncate group-hover:text-emerald-600 transition" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                                                        <span class="text-[9px] text-slate-400 font-semibold block uppercase">คลิกเพื่อดาวน์โหลด</span>
                                                    </div>
                                                </div>
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-emerald-500 group-hover:border-emerald-200/50 shadow-sm transition">
                                                    <i class="fa-solid fa-download text-[11px]"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Right Sidebar (Metadata and Apply actions) -->
                <div class="lg:col-span-4 space-y-6 text-left">
                    
                    <!-- Metadata Info Card -->
                    <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-5">
                        <h4 class="font-extrabold text-slate-800 text-sm">ข้อมูลการจัดกิจกรรม</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-slate-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shrink-0 text-sm">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="block text-slate-455 text-[9px] font-bold text-slate-400 uppercase tracking-wider">ระยะเวลาอบรม</span>
                                    <span class="text-slate-700 font-extrabold text-xs leading-normal" title="{{ $course->duration_text }}">{{ $course->duration_text ?: 'จะประกาศให้ทราบเร็วๆ นี้' }}</span>
                                </div>
                            </div>

                            @if($course->academic_year)
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-slate-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shrink-0 text-sm">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="block text-slate-455 text-[9px] font-bold text-slate-400 uppercase tracking-wider">ปีการศึกษา</span>
                                    <span class="text-slate-700 font-extrabold text-xs leading-normal">ปีการศึกษา {{ $course->academic_year }}</span>
                                </div>
                            </div>
                            @endif

                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-slate-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shrink-0 text-sm">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="block text-slate-455 text-[9px] font-bold text-slate-400 uppercase tracking-wider">สถานที่จัดโครงการ</span>
                                    <span class="text-slate-700 font-extrabold text-xs leading-normal" title="{{ $course->location }}">{{ $course->location ?: ($webName === 'EE.CPN1' ? 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1' : $webName) }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-slate-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shrink-0 text-sm">
                                    <i class="fa-solid fa-user-group"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="block text-slate-455 text-[9px] font-bold text-slate-400 uppercase tracking-wider">กลุ่มเป้าหมายผู้เข้าร่วม</span>
                                    <span class="text-slate-700 font-extrabold text-xs leading-normal">{{ $course->target_group ?: 'ครูระดับประถมศึกษา' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button action -->
                        <div class="pt-4 border-t border-slate-50">
                            @if($course->status === 'open' && $course->registration_link)
                                <a href="{{ $course->registration_link }}" target="_blank" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-3.5 px-6 rounded-xl shadow-lg shadow-emerald-100 hover:shadow-emerald-200 transition-all flex items-center justify-center gap-2 cursor-pointer duration-200 text-center">
                                    <i class="fa-solid fa-file-pen"></i> ลงทะเบียนสมัครเข้าร่วม
                                </a>
                            @else
                                <button disabled class="w-full bg-slate-100 text-slate-400 font-bold text-xs py-3.5 px-6 rounded-xl cursor-not-allowed text-center">
                                    @if($course->status === 'upcoming')
                                        ยังไม่เปิดรับสมัคร
                                    @elseif($course->status === 'ongoing')
                                        ปิดรับสมัครแล้ว (อยู่ระหว่างจัดอบรม)
                                    @else
                                        เสร็จสิ้นกิจกรรมโครงการแล้ว
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Help Information -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 md:p-8 text-white shadow-lg space-y-4">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-emerald-300 text-lg">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <h4 class="font-bold text-sm">ต้องการสอบถามเพิ่มเติม?</h4>
                        <p class="text-[11px] text-slate-300 leading-relaxed">
                            ติดต่อประสานงานวิชาการโครงการ หรือช่วยเหลือปัญหาเกี่ยวกับการสมัครลงทะเบียนอบรมพัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1
                        </p>
                        <a href="/#contact" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-300 hover:text-emerald-400 transition">
                            ดูข้อมูลติดต่อประสานงานวิชาการ <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- Lightbox Modal overlay for Zooming Activity Images -->
        <div x-show="activeLightboxImage" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4" x-transition x-cloak>
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center justify-center">
                <img :src="activeLightboxImage" class="max-w-full max-h-[80vh] rounded-3xl object-contain border border-white/10 shadow-2xl">
                <button type="button" @click="activeLightboxImage = null" class="absolute top-4 right-4 bg-slate-900/40 hover:bg-slate-900/60 backdrop-blur-sm text-white w-10 h-10 rounded-full flex items-center justify-center transition border border-white/10">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Scroll Reveal intersection observer
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        obs.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>
    @endpush
</x-layout>

