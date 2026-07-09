<x-layout>
    <x-slot:title>{{ $quiz->title }} | แบบทดสอบอบรมออนไลน์</x-slot>

    <main class="py-16 bg-slate-50/50 min-h-screen" x-data="lmsQuiz('{{ $quiz->id }}', '{{ $courseId }}')">
        <div class="max-w-3xl mx-auto px-6 space-y-8">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <a href="/" class="hover:text-emerald-600 transition">หน้าหลัก</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <a href="{{ route('lms.courses.show', $courseId) }}" class="hover:text-emerald-600 transition">คอร์สเรียน</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-slate-650 truncate max-w-[200px]">{{ $quiz->title }}</span>
            </nav>

            <!-- Quiz Instructions Card -->
            <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-100/30 overflow-hidden" x-show="!started" x-cloak>
                @if($quiz->header_image)
                    <div class="w-full h-48 bg-slate-100">
                        <img src="{{ $quiz->header_image }}" alt="Quiz Header" class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="p-8 md:p-12 space-y-6 text-center">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto border border-emerald-100">
                        <i class="fa-solid fa-clipboard-question"></i>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">
                            {{ $quiz->title }}
                        </h1>
                        <p class="text-xs text-slate-450 uppercase tracking-widest font-extrabold">
                            {{ $quizType === 'pre' ? 'แบบทดสอบก่อนเรียน (Pre-test)' : 'แบบทดสอบหลังเรียน (Post-test)' }}
                        </p>
                    </div>

                    @if(!empty(trim($quiz->instructions)))
                        <div class="bg-slate-50 rounded-2xl p-5 text-slate-500 text-xs leading-relaxed max-w-xl mx-auto border border-slate-100 text-left prose">
                            <p class="font-bold text-slate-700 mb-1"><i class="fa-solid fa-circle-info"></i> คำชี้แจงในการทำแบบทดสอบ:</p>
                            {!! $quiz->instructions !!}
                        </div>
                    @endif

                    <div class="text-xs text-slate-400 font-semibold">
                        <span class="block">จำนวนข้อสอบทั้งหมด: <strong>{{ count($questions) }} ข้อ</strong></span>
                    </div>

                    <div class="pt-4 flex justify-center gap-4">
                        <a href="{{ route('lms.courses.show', $courseId) }}" 
                           class="px-6 py-3.5 border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                            ย้อนกลับ
                        </a>
                        <button type="button" 
                                @click="started = true" 
                                class="px-8 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-100 transition cursor-pointer">
                            เริ่มทำแบบทดสอบ
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quiz Attempt View (Question Cards List) -->
            <div class="space-y-6" x-show="started" x-cloak>
                
                <!-- Progress Sticky Bar -->
                <div class="sticky top-20 z-40 bg-white border border-slate-100 rounded-2xl shadow-md p-4 flex items-center justify-between gap-4">
                    <div class="w-full flex-1">
                        <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <span>ความคืบหน้าการทำข้อสอบ</span>
                            <span x-text="`${answeredCount()} / ${totalQuestions} ข้อ`"></span>
                        </div>
                        <div class="h-2 w-full bg-slate-150 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" 
                                 :style="`width: ${(answeredCount() / totalQuestions) * 100}%`"></div>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="space-y-8">
                    @foreach($questions as $index => $q)
                        <div class="bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 p-6 md:p-8 space-y-5 text-left">
                            <div class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center shrink-0 border border-indigo-100/50">
                                    {{ $index + 1 }}
                                </span>
                                <h3 class="font-extrabold text-slate-800 text-sm md:text-base leading-snug">
                                    {{ $q->question_text }}
                                </h3>
                            </div>

                            @if($q->media_url)
                                <div class="w-full text-center bg-slate-50 rounded-xl p-3 border border-slate-100">
                                    <img src="{{ $q->media_url }}" alt="Question attachment" class="max-h-60 mx-auto rounded-lg">
                                </div>
                            @endif

                            <!-- Options Radio Cards Grid -->
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($q->options as $opt)
                                    <label class="border rounded-2xl p-4 flex items-start gap-3 hover:bg-slate-50 cursor-pointer transition"
                                           :class="answers['{{ $q->id }}'] == '{{ $opt->id }}' ? 'border-emerald-500 bg-emerald-50/20 text-emerald-800 font-bold' : 'border-slate-150 bg-white text-slate-650'">
                                        <input type="radio" 
                                               name="q_{{ $q->id }}" 
                                               value="{{ $opt->id }}"
                                               x-model="answers['{{ $q->id }}']"
                                               class="mt-1 accent-emerald-600 focus:ring-emerald-500">
                                        <div class="text-xs leading-normal">
                                            {{ $opt->option_text }}
                                            @if($opt->option_image_url)
                                                <img src="{{ $opt->option_image_url }}" alt="Option image" class="max-h-32 rounded mt-2 block">
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Toolbar -->
                <div class="flex justify-between items-center gap-4 bg-white border border-slate-100 rounded-[2rem] p-6 shadow-xl shadow-slate-100/10">
                    <button type="button" 
                            @click="started = false" 
                            class="px-6 py-3 border border-slate-200 text-slate-500 font-bold text-xs rounded-xl hover:bg-slate-50 transition cursor-pointer">
                        ย้อนกลับคำชี้แจง
                    </button>
                    <button type="button" 
                            @click="submitQuiz()" 
                            :disabled="saving"
                            class="px-8 py-3.5 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-60 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-100 transition cursor-pointer flex items-center gap-2">
                        <template x-if="saving">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </template>
                        <template x-if="!saving">
                            <i class="fa-solid fa-paper-plane"></i>
                        </template>
                        <span x-text="saving ? 'กำลังส่งคะแนน...' : 'ส่งคำตอบแบบทดสอบ'"></span>
                    </button>
                </div>

            </div>

        </div>
    </main>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsQuiz(quizId, courseId) {
        return {
            quizId: quizId,
            courseId: courseId,
            started: false,
            saving: false,
            totalQuestions: {{ count($questions) }},
            answers: {},

            answeredCount() {
                return Object.keys(this.answers).length;
            },

            submitQuiz() {
                if (this.answeredCount() < this.totalQuestions) {
                    Swal.fire({
                        title: 'ทำข้อสอบยังไม่ครบ?',
                        text: `คุณพึ่งตอบไปเพียง ${this.answeredCount()} จากทั้งหมด ${this.totalQuestions} ข้อ กรุณาตรวจคำตอบให้ครบถ้วนก่อนส่ง`,
                        icon: 'warning',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'ตกลง'
                    });
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการส่งคำตอบ?',
                    text: "คุณไม่สามารถย้อนกลับมาแก้ไขกระดาษคำตอบนี้ได้อีกหลังจากส่งแล้ว",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ยืนยันการส่ง',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.saving = true;
                        axios.post(`/lms/quizzes/${this.quizId}/submit`, { answers: this.answers })
                            .then(response => {
                                if (response.data.status === 'success') {
                                    const d = response.data.data;
                                    Swal.fire({
                                        title: 'การส่งคำตอบสำเร็จ!',
                                        html: `คุณทำคะแนนได้: <strong class="text-xl text-emerald-600">${d.score} / ${d.total} คะแนน</strong><br>คิดเป็นเปอร์เซ็นต์: <strong>${d.percent}%</strong>`,
                                        icon: 'success',
                                        confirmButtonText: 'กลับไปหน้าหลักสูตร'
                                    }).then(() => {
                                        window.location.href = `/lms/courses/${this.courseId}`;
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด',
                                    text: 'ไม่สามารถบันทึกคำตอบข้อสอบของคุณได้ในขณะนี้',
                                    icon: 'error'
                                });
                            })
                            .finally(() => { this.saving = false; });
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-layout>
