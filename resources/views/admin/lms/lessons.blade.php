<x-layout>
    <x-slot:title>จัดการบทเรียน LMS | การตั้งค่าระบบ</x-slot>
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

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="lmsLessonsAdmin()" x-init="init()">
        
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
                <i class="fa-solid fa-plus-circle"></i> สร้างบทเรียนใหม่
            </button>
        </header>

        <!-- Sub-navigation Tabs -->
        <div class="border-b border-slate-200 mb-8 flex flex-wrap gap-1">
            <a href="{{ route('admin.lms.courses.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-graduation-cap mr-1"></i> 1. หลักสูตรทั้งหมด
            </a>
            <a href="{{ route('admin.lms.lessons.index') }}" class="px-5 py-3 border-b-2 border-indigo-600 text-indigo-600 font-extrabold text-xs">
                <i class="fa-solid fa-book-open mr-1"></i> 2. จัดการบทเรียน
            </a>
            <a href="{{ route('admin.lms.quizzes.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
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
                    <option value="">-- แสดงบทเรียนทั้งหมด --</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-[11px] font-bold text-slate-400" x-text="`บทเรียนทั้งหมด: ${lessons.length} รายการ`"></div>
        </div>

        <!-- Table Grid List -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-450 uppercase font-extrabold tracking-wider">
                            <th class="py-4 px-6 w-20 text-center">ลำดับ</th>
                            <th class="py-4 px-6">ชื่อบทเรียน</th>
                            <th class="py-4 px-6">หลักสูตร</th>
                            <th class="py-4 px-6 w-32">ประเภทสื่อ</th>
                            <th class="py-4 px-6 w-28 text-center">ต้องส่งงาน?</th>
                            <th class="py-4 px-6 text-center w-36">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="lessons.length === 0">
                            <tr>
                                <td colspan="6" class="py-16 text-center text-slate-400 font-bold">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-200 block"></i>
                                    ยังไม่มีบทเรียนที่ระบุในเงื่อนไขนี้
                                </td>
                            </tr>
                        </template>
                        <template x-for="lesson in lessons" :key="lesson.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 text-center font-extrabold text-slate-500" x-text="lesson.sort_order"></td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-800 text-sm block" x-text="lesson.title"></span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5" x-text="'เวลาเรียนขั้นต่ำ: ' + (lesson.min_focus_seconds || 30) + ' วินาที'"></span>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-650" x-text="lesson.course_title"></td>
                                <td class="py-4 px-6 font-bold">
                                    <template x-if="lesson.content_type === 'video'">
                                        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded text-[9px]"><i class="fa-solid fa-video mr-1"></i> วิดีโอ</span>
                                    </template>
                                    <template x-if="lesson.content_type === 'pdf'">
                                        <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded text-[9px]"><i class="fa-solid fa-file-pdf mr-1"></i> PDF</span>
                                    </template>
                                    <template x-if="lesson.content_type === 'image'">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px]"><i class="fa-solid fa-image mr-1"></i> รูปภาพ</span>
                                    </template>
                                    <template x-if="lesson.content_type === 'embed'">
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[9px]"><i class="fa-solid fa-code mr-1"></i> Embed</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <template x-if="lesson.require_submission">
                                        <span class="inline-flex items-center text-indigo-700 font-extrabold text-[10px]"><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> ต้องส่งงาน</span>
                                    </template>
                                    <template x-if="!lesson.require_submission">
                                        <span class="text-slate-400 text-[10px]">ไม่ต้องส่ง</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEditModal(lesson)" class="p-2 border border-slate-200 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition cursor-pointer" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="deleteLesson(lesson.id)" class="p-2 border border-rose-100 text-rose-500 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="ลบ">
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
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-2xl w-full p-8 relative space-y-6 text-left" @click.away="modal.open = false">
                
                <h3 class="text-lg font-extrabold text-slate-900" x-text="form.id ? 'แก้ไขบทเรียน' : 'สร้างบทเรียนใหม่'"></h3>

                <form @submit.prevent="saveLesson()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
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
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ชื่อบทเรียน *</label>
                            <input type="text" x-model="form.title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ประเภทเนื้อหาสื่อ *</label>
                            <select x-model="form.content_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                                <option value="video">วิดีโอ (Video)</option>
                                <option value="pdf">ไฟล์เอกสาร (PDF)</option>
                                <option value="image">รูปภาพ (Image)</option>
                                <option value="embed">Embed Frame</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ลำดับการแสดงผล *</label>
                            <input type="number" min="1" x-model="form.sort_order" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ลิงก์ URL สื่อการเรียน</label>
                            <input type="text" x-model="form.content_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition" placeholder="ใส่ลิงก์ YouTube/Vimeo หรือ URL อื่นๆ">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">อัปโหลดไฟล์สื่อโดยตรง (ทับข้อมูล URL ด้านบน)</label>
                            <input type="file" x-ref="mediaInput" @change="fileMediaSelected" class="w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">เวลาเรียนขั้นต่ำ (วินาที)</label>
                            <input type="number" min="0" x-model="form.min_focus_seconds" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="checkbox" x-model="form.require_submission" class="rounded border-slate-200 text-indigo-600 focus:ring-indigo-500">
                                <span>ต้องส่งแบบฝึกหัด/ชิ้นงานเพื่อสำเร็จบท</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">เนื้อหาบทเรียน (HTML/Text)</label>
                            <div x-init="
                                contentQuill = new Quill($refs.contentEditor, {
                                    theme: 'snow',
                                    placeholder: 'พิมพ์หรือวางเนื้อหาบทเรียนที่นี่...',
                                    modules: {
                                        toolbar: [
                                            ['bold', 'italic', 'underline'],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            ['clean']
                                        ]
                                    }
                                });
                                contentQuill.on('text-change', () => {
                                    form.content_html = contentQuill.root.innerHTML === '<p><br></p>' ? '' : contentQuill.root.innerHTML;
                                });
                                $watch('form.content_html', value => {
                                    if (contentQuill && value !== contentQuill.root.innerHTML) {
                                        contentQuill.root.innerHTML = value || '';
                                    }
                                });
                            }">
                                <div x-ref="contentEditor"></div>
                            </div>
                        </div>

                        <div class="md:col-span-2" x-show="form.require_submission">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">เกณฑ์การให้คะแนนและรายละเอียดการส่งงาน (Rubric)</label>
                            <div x-init="
                                rubricQuill = new Quill($refs.rubricEditor, {
                                    theme: 'snow',
                                    placeholder: 'ระบุเกณฑ์ประเมินและชิ้นงานที่ต้องอัปโหลดส่ง...',
                                    modules: {
                                        toolbar: [
                                            ['bold', 'italic', 'underline'],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            ['clean']
                                        ]
                                    }
                                });
                                rubricQuill.on('text-change', () => {
                                    form.rubric_html = rubricQuill.root.innerHTML === '<p><br></p>' ? '' : rubricQuill.root.innerHTML;
                                });
                                $watch('form.rubric_html', value => {
                                    if (rubricQuill && value !== rubricQuill.root.innerHTML) {
                                        rubricQuill.root.innerHTML = value || '';
                                    }
                                });
                            }">
                                <div x-ref="rubricEditor"></div>
                            </div>
                        </div>

                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="modal.open = false" class="px-5 py-3 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-50 transition cursor-pointer">ยกเลิก</button>
                        <button type="submit" :disabled="saving" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูลบทเรียน'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsLessonsAdmin() {
        return {
            lessons: [],
            filterCourseId: '',
            loading: false,
            saving: false,
            toast: { show: false, message: '', type: 'success' },
            modal: { open: false },
            contentQuill: null,
            rubricQuill: null,
            form: {
                id: '',
                course_id: '',
                title: '',
                content_type: 'video',
                content_url: '',
                content_html: '',
                rubric_html: '',
                sort_order: 10,
                min_focus_seconds: 30,
                require_submission: false,
                media_file: null
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
                axios.get(`{{ route("admin.lms.lessons.data") }}?course_id=${this.filterCourseId}`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.lessons = response.data.data;
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถดึงข้อมูลบทเรียนได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            openCreateModal() {
                this.form = {
                    id: '',
                    course_id: this.filterCourseId,
                    title: '',
                    content_type: 'video',
                    content_url: '',
                    content_html: '',
                    rubric_html: '',
                    sort_order: (this.lessons.length + 1) * 10,
                    min_focus_seconds: 30,
                    require_submission: false,
                    media_file: null
                };
                if (this.$refs.mediaInput) this.$refs.mediaInput.value = '';
                this.modal.open = true;
            },

            openEditModal(lesson) {
                this.form = {
                    id: lesson.id,
                    course_id: lesson.course_id,
                    title: lesson.title,
                    content_type: lesson.content_type,
                    content_url: lesson.content_url,
                    content_html: lesson.content_html,
                    rubric_html: lesson.rubric_html,
                    sort_order: lesson.sort_order,
                    min_focus_seconds: lesson.min_focus_seconds,
                    require_submission: lesson.require_submission > 0,
                    media_file: null
                };
                if (this.$refs.mediaInput) this.$refs.mediaInput.value = '';
                this.modal.open = true;
            },

            fileMediaSelected(e) {
                this.form.media_file = e.target.files[0];
            },

            saveLesson() {
                this.saving = true;
                const fd = new FormData();
                fd.append('course_id', this.form.course_id);
                fd.append('title', this.form.title);
                fd.append('content_type', this.form.content_type);
                fd.append('content_url', this.form.content_url || '');
                fd.append('content_html', this.form.content_html || '');
                fd.append('rubric_html', this.form.rubric_html || '');
                fd.append('sort_order', this.form.sort_order);
                fd.append('min_focus_seconds', this.form.min_focus_seconds);
                fd.append('require_submission', this.form.require_submission ? '1' : '0');
                if (this.form.id) {
                    fd.append('id', this.form.id);
                }
                if (this.form.media_file) {
                    fd.append('media_file', this.form.media_file);
                }

                axios.post('{{ route("admin.lms.lessons.save") }}', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showToast(response.data.message, 'success');
                        this.modal.open = false;
                        if (this.$refs.mediaInput) this.$refs.mediaInput.value = '';
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

            deleteLesson(id) {
                Swal.fire({
                    title: 'ยืนยันการลบบทเรียน?',
                    text: 'ความก้าวหน้าการเรียนและผลงานส่งทั้งหมดของนักเรียนในบทนี้จะถูกลบอย่างถาวร!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/admin/lms/lessons/${id}`, {
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
