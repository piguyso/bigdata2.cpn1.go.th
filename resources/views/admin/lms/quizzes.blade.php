<x-layout>
    <x-slot:title>จัดการแบบทดสอบ LMS | การตั้งค่าระบบ</x-slot>
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
    <style>
        .ql-toolbar.ql-snow {
            border: 1px solid #e2e8f0 !important;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            background-color: #f8fafc;
        }
        .ql-container.ql-snow {
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            font-family: inherit;
            font-size: 0.75rem;
            min-height: 120px;
        }
    </style>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="lmsQuizzesAdmin()" x-init="init()">
        
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
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">ระบบจัดการข้อมูล LMS</h2>
                <p class="text-slate-500 text-xs mt-1">ตั้งค่าหลักสูตรอบรม, บทเรียน, แบบทดสอบ และอนุมัติงานส่งของนักเรียน</p>
            </div>
            <button type="button" @click="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs py-3 px-5 rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i> สร้างแบบทดสอบใหม่
            </button>
        </header>

        <!-- Sub-navigation Tabs -->
        <div class="border-b border-slate-200 mb-8 flex flex-wrap gap-1">
            <a href="{{ route('admin.lms.courses.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-graduation-cap mr-1"></i> 1. หลักสูตรทั้งหมด
            </a>
            <a href="{{ route('admin.lms.lessons.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-book-open mr-1"></i> 2. จัดการบทเรียน
            </a>
            <a href="{{ route('admin.lms.quizzes.index') }}" class="px-5 py-3 border-b-2 border-indigo-600 text-indigo-600 font-extrabold text-xs">
                <i class="fa-solid fa-clipboard-question mr-1"></i> 3. จัดการแบบทดสอบ
            </a>
            <a href="{{ route('admin.lms.submissions.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-file-signature mr-1"></i> 4. ตรวจผลงานส่ง
            </a>
        </div>

        <!-- Filter Dropdown Bar -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-150/60 mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label class="text-xs font-extrabold text-slate-500 uppercase shrink-0">กรองตามหลักสูตร:</label>
                <select x-model="filterCourseId" @change="fetchData()" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition w-full sm:w-80">
                    <option value="">-- แสดงแบบทดสอบทั้งหมด --</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-[11px] font-bold text-slate-400" x-text="`แบบทดสอบทั้งหมด: ${quizzes.length} รายการ`"></div>
        </div>

        <!-- Table Grid List -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-450 uppercase font-extrabold tracking-wider">
                            <th class="py-4 px-6">ชื่อแบบทดสอบ</th>
                            <th class="py-4 px-6">หลักสูตร</th>
                            <th class="py-4 px-6 w-32 text-center">ประเภทแบบทดสอบ</th>
                            <th class="py-4 px-6 w-32 text-center">จำนวนข้อสอบ</th>
                            <th class="py-4 px-6 w-28 text-center">สถานะ</th>
                            <th class="py-4 px-6 text-center w-48">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="quizzes.length === 0">
                            <tr>
                                <td colspan="6" class="py-16 text-center text-slate-400 font-bold">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-200 block"></i>
                                    ยังไม่มีแบบทดสอบในเงื่อนไขการกรองนี้
                                </td>
                            </tr>
                        </template>
                        <template x-for="quiz in quizzes" :key="quiz.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-800 text-sm block" x-text="quiz.title"></span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 line-clamp-1" x-text="quiz.instructions || 'ไม่มีคำแนะนำเพิ่มเติม'"></span>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-650" x-text="quiz.course_title"></td>
                                <td class="py-4 px-6 text-center">
                                    <template x-if="quiz.quiz_type === 'pre'">
                                        <span class="px-2.5 py-0.5 bg-sky-50 text-sky-700 rounded-lg text-[9px] font-bold">ก่อนเรียน (Pre-test)</span>
                                    </template>
                                    <template x-if="quiz.quiz_type === 'post'">
                                        <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-lg text-[9px] font-bold">หลังเรียน (Post-test)</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center font-extrabold text-slate-700" x-text="quiz.question_count + ' ข้อ'"></td>
                                <td class="py-4 px-6 text-center">
                                    <template x-if="quiz.is_active > 0">
                                        <span class="px-2 py-0.5 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded text-[9px] font-bold">เปิดใช้งาน</span>
                                    </template>
                                    <template x-if="!(quiz.is_active > 0)">
                                        <span class="px-2 py-0.5 bg-slate-55 bg-slate-100 text-slate-400 rounded text-[9px] font-bold">ปิดใช้งาน</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a :href="`/admin/lms/questions?quiz_id=${quiz.id}`" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold rounded-lg transition" title="จัดการข้อสอบ">
                                            <i class="fa-solid fa-list-check mr-0.5"></i> จัดการข้อสอบ
                                        </a>
                                        <button type="button" @click="openEditModal(quiz)" class="p-2 border border-slate-200 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition cursor-pointer" title="แก้ไขชื่อ/คำชี้แจง">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="deleteQuiz(quiz.id)" class="p-2 border border-rose-100 text-rose-500 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="ลบ">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal Dialog -->
        <div x-show="modal.open" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-lg w-full p-8 relative space-y-6 text-left" @click.away="modal.open = false">
                
                <h3 class="text-lg font-extrabold text-slate-900" x-text="form.id ? 'แก้ไขรายละเอียดแบบทดสอบ' : 'สร้างแบบทดสอบใหม่'"></h3>

                <form @submit.prevent="saveQuiz()" class="space-y-4">
                    <div class="space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">หลักสูตรอบรม *</label>
                            <select x-model="form.course_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                                <option value="">-- เลือกหลักสูตรอบรม --</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ชื่อแบบทดสอบ *</label>
                            <input type="text" x-model="form.title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ประเภทแบบทดสอบ *</label>
                            <select x-model="form.quiz_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                                <option value="pre">ก่อนเรียน (Pre-test)</option>
                                <option value="post">หลังเรียน (Post-test)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">คำแนะนำ/คำชี้แจงก่อนทำข้อสอบ</label>
                            <div x-init="
                                instrQuill = new Quill($refs.instrEditor, {
                                    theme: 'snow',
                                    placeholder: 'ระบุเกณฑ์การทำข้อสอบ คำชี้แจง และเงื่อนไขการผ่าน...',
                                    modules: {
                                        toolbar: [
                                            ['bold', 'italic', 'underline'],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            ['clean']
                                        ]
                                    }
                                });
                                instrQuill.on('text-change', () => {
                                    form.instructions = instrQuill.root.innerHTML === '<p><br></p>' ? '' : instrQuill.root.innerHTML;
                                });
                                $watch('form.instructions', value => {
                                    if (instrQuill && value !== instrQuill.root.innerHTML) {
                                        instrQuill.root.innerHTML = value || '';
                                    }
                                });
                            }">
                                <div x-ref="instrEditor"></div>
                            </div>
                        </div>

                        <div class="flex items-center pt-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="checkbox" x-model="form.is_active" class="rounded border-slate-200 text-indigo-600 focus:ring-indigo-500">
                                <span>เปิดใช้งานแบบทดสอบนี้</span>
                            </label>
                        </div>

                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="modal.open = false" class="px-5 py-3 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-50 transition cursor-pointer">ยกเลิก</button>
                        <button type="submit" :disabled="saving" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกแบบทดสอบ'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsQuizzesAdmin() {
        return {
            quizzes: [],
            filterCourseId: '',
            loading: false,
            saving: false,
            toast: { show: false, message: '', type: 'success' },
            modal: { open: false },
            instrQuill: null,
            form: {
                id: '',
                course_id: '',
                title: '',
                quiz_type: 'pre',
                instructions: '',
                is_active: true
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
                axios.get(`{{ route("admin.lms.quizzes.data") }}?course_id=${this.filterCourseId}`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.quizzes = response.data.data;
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถดึงข้อมูลแบบทดสอบได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            openCreateModal() {
                this.form = {
                    id: '',
                    course_id: this.filterCourseId,
                    title: '',
                    quiz_type: 'pre',
                    instructions: '',
                    is_active: true
                };
                this.modal.open = true;
            },

            openEditModal(quiz) {
                this.form = {
                    id: quiz.id,
                    course_id: quiz.course_id,
                    title: quiz.title,
                    quiz_type: quiz.quiz_type,
                    instructions: quiz.instructions,
                    is_active: quiz.is_active > 0
                };
                this.modal.open = true;
            },

            saveQuiz() {
                this.saving = true;
                const payload = {
                    course_id: this.form.course_id,
                    title: this.form.title,
                    quiz_type: this.form.quiz_type,
                    instructions: this.form.instructions || '',
                    is_active: this.form.is_active ? 1 : 0
                };
                if (this.form.id) {
                    payload.id = this.form.id;
                }

                axios.post('{{ route("admin.lms.quizzes.save") }}', payload)
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

            deleteQuiz(id) {
                Swal.fire({
                    title: 'ยืนยันการลบแบบทดสอบ?',
                    text: 'คำถาม ตัวเลือก ข้อสอบ และประวัติการทำของนักเรียนทั้งหมดจะถูกลบอย่างถาวร!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/admin/lms/quizzes/${id}`, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        })
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchData();
                            }
                        })
                        .catch(error => {
                            this.showToast('เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
                        });
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-layout>
