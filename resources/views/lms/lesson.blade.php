@php
    $embedUrl = App\Http\Controllers\LmsController::embedVideoUrl($lesson->content_url);
@endphp
<x-layout>
    <x-slot:title>{{ $lesson->title }} | {{ $course->title }}</x-slot>

    <main class="py-12 bg-slate-50/50 min-h-screen" x-data="lmsLesson('{{ $lesson->id }}', {{ $lesson->min_focus_seconds ?: 30 }}, {{ $isCompleted ? 'true' : 'false' }})">
        <div class="max-w-6xl mx-auto px-6 space-y-6">
            
            <!-- Header navigation -->
            <div class="flex items-center justify-between flex-wrap gap-4 border-b border-slate-100 pb-4">
                <nav class="flex items-center gap-2 text-xs font-bold text-slate-400">
                    <a href="/" class="hover:text-emerald-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <a href="{{ route('lms.courses.show', $course->id) }}" class="hover:text-emerald-600 transition">คอร์สเรียน</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-650 truncate max-w-[250px]">{{ $lesson->title }}</span>
                </nav>
                
                <a href="{{ route('lms.courses.show', $course->id) }}" 
                   class="group inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl text-xs font-bold shadow-md hover:shadow-emerald-200/60 hover:shadow-lg transition-all duration-300 active:scale-95">
                    <i class="fa-solid fa-arrow-left transition-transform duration-300 group-hover:-translate-x-1"></i>
                    <span>กลับหน้ารายละเอียดหลักสูตร</span>
                </a>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Lesson Content & Frame -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Content Card Container -->
                    <div class="bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 overflow-hidden p-6 md:p-8 space-y-6">
                        
                        <div class="space-y-2 text-left">
                            <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 leading-snug">
                                {{ $lesson->title }}
                            </h1>
                        </div>

                        <!-- Render content depending on content_type -->
                        @if($lesson->content_type === 'video' && $embedUrl)
                            <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-900 border border-slate-100 shadow-sm relative">
                                <iframe src="{{ $embedUrl }}" 
                                        class="w-full h-full" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen></iframe>
                            </div>
                        @elseif($lesson->content_type === 'pdf')
                            <div class="w-full h-[600px] rounded-2xl overflow-hidden border border-slate-150 shadow-sm relative">
                                <iframe src="{{ $lesson->content_url }}" class="w-full h-full"></iframe>
                            </div>
                        @elseif($lesson->content_type === 'image')
                            <div class="w-full text-center bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <img src="{{ $lesson->content_url }}" alt="Lesson Image" class="max-w-full h-auto mx-auto rounded-xl">
                            </div>
                        @elseif($lesson->content_type === 'embed')
                            <div class="w-full h-[500px] rounded-2xl overflow-hidden border border-slate-100">
                                <iframe src="{{ $lesson->content_url }}" class="w-full h-full" frameborder="0"></iframe>
                            </div>
                        @endif

                        <!-- Rich Text Content (content_html) -->
                        @if(!empty(trim($lesson->content_html)))
                            <div class="prose max-w-none text-slate-600 text-sm leading-relaxed border-t border-slate-50 pt-6">
                                {!! $lesson->content_html !!}
                            </div>
                        @endif

                        <!-- Complete Tracking Action Bar -->
                        <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <!-- Timer Indicator -->
                            <div>
                                <template x-if="!isCompleted && remainingSeconds > 0">
                                    <div class="flex items-center gap-2 text-xs font-bold text-amber-600 bg-amber-50 px-4 py-2.5 rounded-xl border border-amber-100/60">
                                        <i class="fa-solid fa-clock-rotate-left fa-spin text-sm"></i>
                                        <span>กรุณาศึกษาเนื้อหาบทเรียนอย่างน้อยอีก <span x-text="remainingSeconds"></span> วินาทีเพื่อสะสมความก้าวหน้า</span>
                                    </div>
                                </template>
                                <template x-if="isCompleted">
                                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-4 py-2.5 rounded-xl border border-emerald-100/60">
                                        <i class="fa-solid fa-circle-check text-sm text-emerald-500"></i>
                                        <span>คุณได้ศึกษาบทเรียนนี้เสร็จสิ้นแล้ว</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Nav Buttons -->
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                @if($prevLesson)
                                    <a href="{{ route('lms.lessons.show', $prevLesson->id) }}" 
                                       class="px-5 py-3 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-chevron-left"></i> บทก่อนหน้า
                                    </a>
                                @endif

                                @if($nextLesson)
                                    <a href="{{ route('lms.lessons.show', $nextLesson->id) }}" 
                                       :class="isCompleted ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                       :disabled="!isCompleted"
                                       class="px-5 py-3 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                                        บทถัดไป <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: Assignment Submission (If required) -->
                <div class="lg:col-span-4 space-y-6">
                    
                    @if($lesson->require_submission)
                        <div class="bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 p-6 md:p-8 space-y-6 text-left">
                            <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-file-arrow-up text-emerald-500"></i> การส่งงานวิชา
                            </h2>

                            <!-- Rubric Description -->
                            @if(!empty($lesson->rubric_html))
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs text-slate-500 leading-relaxed">
                                    <p class="font-bold text-slate-700 mb-1.5"><i class="fa-solid fa-circle-info"></i> คำแนะนำและข้อกำหนด:</p>
                                    {!! $lesson->rubric_html !!}
                                </div>
                            @endif

                            <!-- Current Submission Status Card -->
                            @if($submission)
                                <div class="p-4 rounded-xl border text-xs leading-relaxed space-y-2 {{ $submission->status === 'passed' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : ($submission->status === 'failed' ? 'bg-rose-50 border-rose-100 text-rose-800' : 'bg-amber-50 border-amber-100 text-amber-800') }}">
                                    <div class="flex justify-between items-center font-bold">
                                        <span>สถานะการส่งงาน:</span>
                                        <span>
                                            @if($submission->status === 'passed')
                                                <i class="fa-solid fa-circle-check"></i> ตรวจผ่านแล้ว
                                            @elseif($submission->status === 'failed')
                                                <i class="fa-solid fa-circle-xmark"></i> ไม่ผ่านเกณฑ์ (กรุณาแก้ไขและส่งใหม่)
                                            @else
                                                <i class="fa-solid fa-hourglass-half"></i> รอตรวจประเมิน
                                            @endif
                                        </span>
                                    </div>
                                    @if($submission->file_url)
                                        <div class="pt-1">
                                            <a href="{{ asset($submission->file_url) }}" target="_blank" class="font-bold underline text-indigo-600 hover:text-indigo-800">
                                                <i class="fa-solid fa-paperclip"></i> ดาวน์โหลดไฟล์งานที่ส่งไปล่าสุด
                                            </a>
                                        </div>
                                    @endif
                                    @if($submission->admin_comment)
                                        <div class="pt-1.5 border-t border-slate-200/50 mt-1.5">
                                            <p class="font-bold text-slate-700">คำแนะนำจากผู้ประเมิน:</p>
                                            <p class="mt-0.5 text-slate-650">{{ $submission->admin_comment }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Submission Upload Form -->
                            @if(!$submission || $submission->status !== 'passed')
                                <form action="{{ route('lms.lessons.submit', $lesson->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">เลือกไฟล์แนบส่งงาน (รองรับ PDF ไม่เกิน 10MB)</label>
                                        <input type="file" 
                                               name="file" 
                                               accept=".pdf" 
                                               required
                                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">บันทึกเพิ่มเติม (ถ้ามี)</label>
                                        <textarea name="student_note" 
                                                  placeholder="คำอธิบายสั้นๆ..." 
                                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none min-h-[80px] transition">{{ $submission ? $submission->student_note : '' }}</textarea>
                                    </div>
                                    <button type="submit" 
                                            class="w-full flex items-center justify-center gap-2 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-100 active:scale-95 transition cursor-pointer">
                                        <i class="fa-solid fa-paper-plane"></i> {{ $submission ? 'ส่งงานใหม่อีกครั้ง' : 'ส่งงานเข้าประเมิน' }}
                                    </button>
                                </form>
                            @endif

                        </div>
                    @endif

                </div>

            </div>
        </div>
    </main>

    @push('scripts')
    <script>
    function lmsLesson(lessonId, minFocusSeconds, initialCompleted) {
        return {
            lessonId: lessonId,
            minFocusSeconds: minFocusSeconds,
            isCompleted: initialCompleted,
            remainingSeconds: minFocusSeconds,
            timer: null,

            init() {
                if (this.isCompleted) return;
                
                // Focus tracking countdown timer
                this.timer = setInterval(() => {
                    if (this.remainingSeconds > 0) {
                        this.remainingSeconds--;
                    } else {
                        clearInterval(this.timer);
                        this.markAsComplete();
                    }
                }, 1000);
            },

            markAsComplete() {
                axios.post(`/lms/lessons/${this.lessonId}/complete`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.isCompleted = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error saving lesson progress:', error);
                    });
            }
        };
    }
    </script>
    @endpush
</x-layout>
