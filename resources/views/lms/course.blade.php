<x-layout>
    <x-slot:title>{{ $course->title }} | LMS ระบบเรียนออนไลน์</x-slot>

    <main class="py-16 bg-slate-50/50 min-h-screen" x-data="lmsCourse('{{ $course->id }}')">
        <div class="max-w-7xl mx-auto px-6 space-y-8">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <a href="/" class="hover:text-emerald-600 transition">หน้าหลัก</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-slate-600">คอร์สอบรมออนไลน์</span>
            </nav>

            <!-- Status Alerts -->
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl text-rose-700 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Toast alert -->
            <div
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-end="opacity-0"
                :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
                class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
                x-cloak
            >
                <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
                <span x-text="toast.message"></span>
            </div>

            <!-- Course Header Card -->
            <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-100/30 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <!-- Cover Image -->
                    <div class="md:col-span-4 h-64 md:h-full bg-slate-100 relative min-h-[250px]">
                        @if($course->cover_url)
                            <img src="{{ $course->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex flex-col items-center justify-center text-white p-6 text-center">
                                <i class="fa-solid fa-graduation-cap text-5xl mb-3 opacity-80"></i>
                                <span class="font-bold text-sm">หลักสูตรฝึกอบรมพัฒนาวิชาชีพครู</span>
                            </div>
                        @endif
                    </div>
                    <!-- Course Details -->
                    <div class="md:col-span-8 p-8 md:p-12 flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold rounded-lg text-[10px] uppercase tracking-wider">
                                    {{ $course->status === 'published' ? 'เปิดให้เข้าเรียน' : 'ฉบับร่าง' }}
                                </span>
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-bold rounded-lg text-[10px]">
                                    {{ $course->pass_threshold }}% เกณฑ์ผ่านหลังเรียน
                                </span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight">
                                {{ $course->title }}
                            </h1>
                            <div class="text-slate-500 text-sm leading-relaxed prose max-w-none">
                                {!! $course->description !!}
                            </div>
                        </div>

                        <!-- Enrollment Action Buttons -->
                        <div class="pt-4 border-t border-slate-50 flex items-center gap-4">
                            @if(!$isEnrolled)
                                <button type="button" 
                                        @click="enroll()" 
                                        :disabled="loading"
                                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-extrabold text-xs py-3.5 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition cursor-pointer">
                                    <i class="fa-solid fa-plus-circle"></i> ลงทะเบียนเข้าเรียน
                                </button>
                            @else
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-xs font-bold select-none">
                                        <i class="fa-solid fa-circle-check"></i> ลงทะเบียนเรียนแล้ว
                                    </span>
                                    <button type="button" 
                                            @click="unenroll()" 
                                            :disabled="loading"
                                            class="inline-flex items-center gap-1.5 border border-rose-200 text-rose-500 hover:bg-rose-50 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">
                                        <i class="fa-solid fa-minus-circle"></i> ยกเลิกการลงทะเบียน
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Syllabus & Assessment -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left: Lessons Syllabus list -->
                <div class="lg:col-span-8 bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 p-6 md:p-8 space-y-6">
                    <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-500"></i> โครงสร้างบทเรียนและเนื้อหา
                    </h2>

                    @if($lessons->isEmpty())
                        <div class="py-12 text-center text-slate-400 font-medium">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                            ยังไม่มีบทเรียนในคอร์สนี้
                        </div>
                    @else
                        @php $previousCompleted = true; @endphp
                        <div class="space-y-4">
                            @foreach($lessons as $idx => $lesson)
                                @php
                                    $isCompleted = isset($completedMap[$lesson->id]);
                                    $needPreForFirst = ($idx === 0 && !$pretestDone);
                                    $isLocked = !$previousCompleted || $needPreForFirst;
                                    $canOpen = $isEnrolled && !$isLocked;
                                    $previousCompleted = $previousCompleted && $isCompleted;
                                @endphp
                                <div class="border rounded-2xl p-5 transition flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $isCompleted ? 'border-emerald-200 bg-emerald-50/20' : ($isLocked ? 'border-slate-150 bg-slate-50/50' : 'border-indigo-100 bg-indigo-50/20') }}">
                                    <div class="space-y-1">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">บทที่ {{ $idx + 1 }}</span>
                                        <h4 class="font-extrabold text-slate-800 text-sm md:text-base leading-snug">
                                            {{ $lesson->title }}
                                        </h4>
                                        @if($lesson->require_submission)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded mt-1">
                                                <i class="fa-solid fa-file-arrow-up"></i> ต้องส่งงาน
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 self-start sm:self-center shrink-0">
                                        @if($isCompleted)
                                            <span class="px-3 py-1.5 rounded-xl text-xs bg-emerald-100 text-emerald-700 font-extrabold flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check"></i> สำเร็จแล้ว
                                            </span>
                                        @elseif($needPreForFirst)
                                            <span class="px-3 py-1.5 rounded-xl text-xs bg-amber-50 text-amber-700 font-bold border border-amber-100">
                                                ทำก่อนเรียนก่อน
                                            </span>
                                        @elseif($isLocked)
                                            <span class="px-3 py-1.5 rounded-xl text-xs bg-slate-100 text-slate-450 font-bold border border-slate-200/50 flex items-center gap-1.5">
                                                <i class="fa-solid fa-lock text-[10px]"></i> ปิดล็อกอยู่
                                            </span>
                                        @else
                                            <span class="px-3 py-1.5 rounded-xl text-xs bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                                พร้อมเรียน
                                            </span>
                                        @endif

                                        @if($canOpen)
                                            <a href="{{ route('lms.lessons.show', $lesson->id) }}" 
                                               class="px-5 py-2 rounded-xl text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold shadow-md shadow-indigo-100 hover:scale-105 active:scale-95 transition cursor-pointer">
                                                เข้าเรียน
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right: Quizzes & Assessment Details -->
                <div class="lg:col-span-4 bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 p-6 md:p-8 space-y-6">
                    <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-gauge-high text-emerald-500"></i> ความคืบหน้าของฉัน
                    </h2>

                    <!-- Scores Summary -->
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100/80 space-y-3.5 text-xs text-slate-600">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">คะแนนทดสอบก่อนเรียน:</span>
                            <span class="font-extrabold text-slate-900">{{ $prePercent === null ? '-' : number_format($prePercent, 2) . '%' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">คะแนนทดสอบหลังเรียน:</span>
                            <span class="font-extrabold text-slate-900">{{ $postPercent === null ? '-' : number_format($postPercent, 2) . '%' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">ดัชนีพัฒนาการเรียนรู้:</span>
                            <span class="font-extrabold {{ ($improvement ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $improvement === null ? '-' : ($improvement >= 0 ? '+' : '') . number_format($improvement, 2) . '%' }}
                            </span>
                        </div>
                        <hr class="border-slate-200/60 my-2">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-4">
                                <span class="font-semibold">1) ผ่านเกณฑ์ประเมินหลังเรียน (>= {{ $passThreshold }}%):</span>
                                <span class="font-extrabold text-[10px] px-2 py-0.5 rounded-md {{ $passByPost ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $postPercent === null ? 'ยังไม่สอบ' : ($passByPost ? 'ผ่าน' : 'ไม่ผ่าน') }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <span class="font-semibold">2) งานและแบบฝึกหัดที่ส่งผ่านครบ:</span>
                                <span class="font-extrabold text-[10px] px-2 py-0.5 rounded-md {{ $allRequiredJobsPassed ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $requiredJobCount === 0 ? 'ไม่มีงาน' : ($allRequiredJobsPassed ? 'ผ่านครบ' : 'ผ่าน ' . $passedJobCount . '/' . $requiredJobCount . ' ชิ้น') }}
                                </span>
                            </div>
                        </div>
                        <hr class="border-slate-200/60 my-2">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-sm">สถานะผลการอบรม:</span>
                            <span class="font-extrabold text-sm {{ $coursePassed ? 'text-emerald-600' : 'text-rose-500' }}">
                                {{ $coursePassed ? 'ผ่านหลักสูตรสำเร็จ' : 'อยู่ระหว่างศึกษา' }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div class="space-y-3.5 pt-2">
                        <!-- Pretest Button -->
                        @if($preQuiz)
                            <a href="{{ route('lms.quiz.show') }}?course_id={{ $course->id }}&type=pre" 
                               class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-sky-500 hover:bg-sky-600 active:scale-95 text-white font-extrabold text-xs shadow-md shadow-sky-100 transition">
                                <i class="fa-solid fa-clipboard-question"></i> ทำแบบทดสอบก่อนเรียน
                            </a>
                        @else
                            <p class="text-[10px] text-slate-400 font-bold text-center italic">ไม่มีแบบทดสอบก่อนเรียนสำหรับหลักสูตรนี้</p>
                        @endif

                        <!-- Posttest Button -->
                        @if($postQuiz)
                            @if($allLessonsCompleted)
                                <a href="{{ route('lms.quiz.show') }}?course_id={{ $course->id }}&type=post" 
                                   class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-extrabold text-xs shadow-md shadow-emerald-100 transition">
                                    <i class="fa-solid fa-clipboard-check"></i> ทำแบบทดสอบหลังเรียน
                                </a>
                            @else
                                <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 text-[10px] font-bold text-center leading-relaxed">
                                    <i class="fa-solid fa-circle-info"></i> ต้องศึกษาบทเรียนให้ผ่านครบทุกบทก่อน จึงจะสามารถทำแบบทดสอบหลังเรียนได้
                                </div>
                            @endif
                        @endif

                        <!-- Certificate Button -->
                        @if($coursePassed)
                            <a href="{{ route('lms.courses.certificate', $course->id) }}" 
                               class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-xs shadow-lg shadow-amber-100 transition">
                                <i class="fa-solid fa-certificate"></i> ดาวน์โหลดเกียรติบัตร (PDF)
                            </a>
                        @else
                            <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 text-[10px] font-bold text-center leading-relaxed">
                                เมื่อเรียนครบทุกบท สอบหลังเรียนผ่านเกณฑ์ และผู้ดูแลอนุมัติงานส่งครบ จะสามารถดาวน์โหลดเกียรติบัตรได้
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsCourse(courseId) {
        return {
            courseId: courseId,
            loading: false,
            toast: { show: false, message: '', type: 'success' },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            enroll() {
                this.loading = true;
                axios.post(`/lms/courses/${this.courseId}/enroll`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showToast(response.data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถลงทะเบียนเรียนได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            unenroll() {
                Swal.fire({
                    title: 'ต้องการยกเลิกการลงทะเบียน?',
                    text: "หากยกเลิกการลงทะเบียน ข้อมูลการเรียน คะแนนทดสอบ และงานที่ส่งทั้งหมดของคอร์สนี้จะถูกลบอย่างถาวร!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ใช่, ต้องการยกเลิก',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.loading = true;
                        axios.post(`/lms/courses/${this.courseId}/unenroll`)
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.showToast(response.data.message, 'success');
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                }
                            })
                            .catch(error => {
                                this.showToast('เกิดข้อผิดพลาดในการยกเลิกการลงทะเบียน', 'error');
                            })
                            .finally(() => { this.loading = false; });
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-layout>
