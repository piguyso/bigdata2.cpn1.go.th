<x-layout>
    <x-slot:title>จัดการข้อสอบ | {{ $quiz->title }}</x-slot>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="lmsQuestionsAdmin('{{ $quiz->id }}')" x-init="init()">
        
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

        <!-- Admin Header -->
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                    <a href="{{ route('admin.lms.quizzes.index') }}" class="hover:text-indigo-600 transition">แบบทดสอบทั้งหมด</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-650">จัดการข้อสอบ</span>
                </nav>
                <h2 class="text-xl font-extrabold text-slate-900">คำถามในแบบทดสอบ: {{ $quiz->title }}</h2>
                <p class="text-slate-500 text-xs mt-0.5" x-text="`ประเภท: ${'{{ $quiz->quiz_type }}' === 'pre' ? 'ก่อนเรียน (Pre-test)' : 'หลังเรียน (Post-test)'}`"></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.lms.quizzes.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับหน้าแบบทดสอบ
                </a>
                <button type="button" @click="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs py-3 px-5 rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus-circle"></i> เพิ่มข้อสอบใหม่
                </button>
            </div>
        </header>

        <!-- Questions List cards -->
        <div class="space-y-6">
            <template x-if="questions.length === 0">
                <div class="bg-white border border-slate-100 rounded-3xl p-16 text-center text-slate-400 font-bold shadow-sm">
                    <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-200 block"></i>
                    ยังไม่มีข้อสอบในแบบทดสอบนี้
                </div>
            </template>
            <template x-for="(q, idx) in questions" :key="q.id">
                <div class="bg-white border border-slate-100 rounded-[2rem] shadow-xl shadow-slate-100/10 p-6 md:p-8 space-y-4 flex flex-col md:flex-row md:items-start md:justify-between gap-6 text-left">
                    <div class="space-y-4 flex-1">
                        <div class="flex items-start gap-2.5">
                            <span class="w-6 h-6 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-extrabold flex items-center justify-center rounded-lg shrink-0" x-text="idx + 1"></span>
                            <h4 class="font-extrabold text-slate-800 text-sm md:text-base leading-snug" x-text="q.question_text"></h4>
                        </div>
                        <!-- Options list preview -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pl-8">
                            <template x-for="opt in q.options" :key="opt.id">
                                <div class="px-4 py-2.5 rounded-xl border border-slate-100 text-xs flex items-center justify-between gap-3"
                                     :class="opt.is_correct > 0 ? 'bg-emerald-50 border-emerald-100 text-emerald-800 font-bold' : 'bg-slate-50/50 text-slate-600'">
                                    <span x-text="opt.option_text"></span>
                                    <template x-if="opt.is_correct > 0">
                                        <i class="fa-solid fa-circle-check text-emerald-600 shrink-0"></i>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center gap-1.5 self-end md:self-start shrink-0">
                        <button type="button" @click="openEditModal(q)" class="p-2.5 border border-slate-200 text-slate-650 hover:text-indigo-650 hover:bg-slate-50 rounded-xl transition cursor-pointer" title="แก้ไขข้อนี้">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button" @click="deleteQuestion(q.id)" class="p-2.5 border border-rose-100 text-rose-500 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="ลบข้อนี้">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Question Form Modal Dialog -->
        <div x-show="modal.open" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-xl w-full p-8 relative space-y-6 text-left" @click.away="modal.open = false">
                
                <h3 class="text-lg font-extrabold text-slate-900" x-text="form.id ? 'แก้ไขคำถามข้อสอบ' : 'สร้างคำถามข้อสอบใหม่'"></h3>

                <form @submit.prevent="saveQuestion()" class="space-y-4">
                    <div class="space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">โจทย์คำถาม *</label>
                            <textarea x-model="form.question_text" required rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition" placeholder="ระบุเนื้อหาโจทย์คำถามที่ต้องการถาม..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ลำดับข้อสอบ *</label>
                            <input type="number" min="1" x-model="form.sort_order" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <hr class="border-slate-100">

                        <!-- Options Dynamic List -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-extrabold text-slate-700 uppercase">ตัวเลือกและคำตอบ (Options) *</label>
                                <button type="button" @click="addOption()" class="text-[10px] font-bold text-indigo-650 hover:underline">
                                    <i class="fa-solid fa-plus mr-0.5"></i> เพิ่มตัวเลือก
                                </button>
                            </div>
                            
                            <div class="space-y-2">
                                <template x-for="(opt, oIdx) in form.options" :key="oIdx">
                                    <div class="flex items-center gap-2">
                                        <!-- Correct toggle -->
                                        <input type="radio" 
                                               name="correct_opt" 
                                               :value="oIdx" 
                                               :checked="opt.is_correct"
                                               @change="setCorrectOption(oIdx)"
                                               class="accent-emerald-600">
                                        <!-- Option Text -->
                                        <input type="text" 
                                               x-model="opt.option_text" 
                                               required 
                                               class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition"
                                               :placeholder="`ตัวเลือกที่ ${oIdx + 1}`">
                                        <!-- Delete option -->
                                        <button type="button" 
                                                @click="removeOption(oIdx)" 
                                                x-show="form.options.length > 2"
                                                class="text-rose-500 hover:text-rose-700 text-xs p-2">
                                            <i class="fa-solid fa-circle-minus"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <span class="block text-[9px] text-slate-400 font-bold italic mt-1"><i class="fa-solid fa-circle-info"></i> เลือกปุ่มวิทยุ (Radio Button) ด้านซ้ายเพื่อกำหนดตัวเลือกที่ถูกต้องที่สุดเพียงข้อเดียว</span>
                        </div>

                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="modal.open = false" class="px-5 py-3 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-50 transition cursor-pointer">ยกเลิก</button>
                        <button type="submit" :disabled="saving" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกคำถาม'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsQuestionsAdmin(quizId) {
        return {
            quizId: quizId,
            questions: [],
            loading: false,
            saving: false,
            toast: { show: false, message: '', type: 'success' },
            modal: { open: false },
            form: {
                id: '',
                question_text: '',
                sort_order: 10,
                options: [
                    { option_text: '', is_correct: true },
                    { option_text: '', is_correct: false },
                    { option_text: '', is_correct: false },
                    { option_text: '', is_correct: false }
                ]
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            init() {
                this.fetchData();
            },

            fetchData() {
                this.loading = true;
                axios.get(`{{ route("admin.lms.questions.data") }}?quiz_id=${this.quizId}`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.questions = response.data.data;
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถดึงข้อมูลคำถามข้อสอบได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            openCreateModal() {
                this.form = {
                    id: '',
                    question_text: '',
                    sort_order: (this.questions.length + 1) * 10,
                    options: [
                        { option_text: '', is_correct: true },
                        { option_text: '', is_correct: false },
                        { option_text: '', is_correct: false },
                        { option_text: '', is_correct: false }
                    ]
                };
                this.modal.open = true;
            },

            openEditModal(q) {
                this.form = {
                    id: q.id,
                    question_text: q.question_text,
                    sort_order: q.sort_order,
                    options: q.options.map(opt => ({
                        option_text: opt.option_text,
                        is_correct: opt.is_correct > 0
                    }))
                };
                this.modal.open = true;
            },

            addOption() {
                this.form.options.push({ option_text: '', is_correct: false });
            },

            removeOption(idx) {
                if (this.form.options[idx].is_correct) {
                    // fall back correct setting to index 0
                    this.form.options[0].is_correct = true;
                }
                this.form.options.splice(idx, 1);
            },

            setCorrectOption(correctIdx) {
                this.form.options.forEach((opt, idx) => {
                    opt.is_correct = (idx === correctIdx);
                });
            },

            saveQuestion() {
                // Ensure at least one correct option exists
                const hasCorrect = this.form.options.some(opt => opt.is_correct);
                if (!hasCorrect) {
                    this.form.options[0].is_correct = true;
                }

                this.saving = true;
                const payload = {
                    quiz_id: this.quizId,
                    question_text: this.form.question_text,
                    sort_order: this.form.sort_order,
                    options: this.form.options
                };
                if (this.form.id) {
                    payload.id = this.form.id;
                }

                axios.post('{{ route("admin.lms.questions.save") }}', payload)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showToast(response.data.message, 'success');
                        this.modal.open = false;
                        this.fetchData();
                    } else {
                        this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                })
                .catch(error => {
                    const msg = error.response?.data?.message ?? 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    this.showToast(msg, 'error');
                })
                .finally(() => { this.saving = false; });
            },

            deleteQuestion(id) {
                Swal.fire({
                    title: 'ยืนยันการลบคำถามข้อนี้?',
                    text: 'ตัวเลือกและคะแนนสะสมที่อิงจากโจทย์ข้อนี้ทั้งหมดจะถูกลบอย่างถาวร!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ลบคำถาม',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/admin/lms/questions/${id}`, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        })
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchData();
                            }
                        })
                        .catch(error => {
                            this.showToast('เกิดข้อผิดพลาดในการลบคำถาม', 'error');
                        });
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-layout>
