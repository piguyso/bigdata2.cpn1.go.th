<x-layout>
    <x-slot:title>จัดการหลักสูตร LMS | การตั้งค่าระบบ</x-slot>
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>
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

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="lmsCoursesAdmin()" x-init="init()">
        
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
                <i class="fa-solid fa-plus-circle"></i> สร้างหลักสูตรใหม่
            </button>
        </header>

        <!-- Sub-navigation Tabs -->
        <div class="border-b border-slate-200 mb-8 flex flex-wrap gap-1">
            <a href="{{ route('admin.lms.courses.index') }}" class="px-5 py-3 border-b-2 border-indigo-600 text-indigo-600 font-extrabold text-xs">
                <i class="fa-solid fa-graduation-cap mr-1"></i> 1. หลักสูตรทั้งหมด
            </a>
            <a href="{{ route('admin.lms.lessons.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-book-open mr-1"></i> 2. จัดการบทเรียน
            </a>
            <a href="{{ route('admin.lms.quizzes.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-clipboard-question mr-1"></i> 3. จัดการแบบทดสอบ
            </a>
            <a href="{{ route('admin.lms.submissions.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-file-signature mr-1"></i> 4. ตรวจผลงานส่ง
            </a>
        </div>

        <!-- Table Grid List -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-450 uppercase font-extrabold tracking-wider">
                            <th class="py-4 px-6 w-32">หน้าปก</th>
                            <th class="py-4 px-6">ชื่อหลักสูตร</th>
                            <th class="py-4 px-6">หมวดหมู่ / ระดับ</th>
                            <th class="py-4 px-6 w-28 text-center">เกณฑ์ประเมิน</th>
                            <th class="py-4 px-6 w-28 text-center">สถานะ</th>
                            <th class="py-4 px-6 text-center w-36">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="courses.length === 0">
                            <tr>
                                <td colspan="6" class="py-16 text-center text-slate-400 font-bold">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-200 block"></i>
                                    ยังไม่มีหลักสูตรอบรมในระบบ
                                </td>
                            </tr>
                        </template>
                        <template x-for="course in courses" :key="course.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6">
                                    <div class="w-20 h-12 bg-slate-100 border border-slate-150 rounded-lg overflow-hidden shrink-0">
                                        <template x-if="course.cover_url">
                                            <img :src="course.cover_url" alt="Cover" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!course.cover_url">
                                            <div class="w-full h-full flex items-center justify-center text-[9px] text-slate-400 font-extrabold">NO COVER</div>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-800 text-sm block" x-text="course.title"></span>
                                    <span class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" x-html="course.description"></span>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-650">
                                    <span class="block" x-text="course.category"></span>
                                    <span class="text-[10px] text-indigo-600 block mt-0.5" x-text="'ระดับ: ' + course.level"></span>
                                </td>
                                <td class="py-4 px-6 text-center font-extrabold text-slate-700" x-text="course.pass_threshold + '%'"></td>
                                <td class="py-4 px-6 text-center">
                                    <template x-if="course.status === 'published'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">เปิดแพร่แล้ว</span>
                                    </template>
                                    <template x-if="course.status === 'draft'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-100">ฉบับร่าง</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEditModal(course)" class="p-2 border border-slate-200 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition cursor-pointer" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="deleteCourse(course.id)" class="p-2 border border-rose-100 text-rose-500 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="ลบ">
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
                
                <h3 class="text-lg font-extrabold text-slate-900" x-text="form.id ? 'แก้ไขรายละเอียดหลักสูตรอบรม' : 'สร้างหลักสูตรอบรมใหม่'"></h3>

                <form @submit.prevent="saveCourse()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!cropping">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ชื่อหลักสูตรอบรม *</label>
                            <input type="text" x-model="form.title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">คำอธิบายหลักสูตร</label>
                            <div x-init="
                                descQuill = new Quill($refs.descEditor, {
                                    theme: 'snow',
                                    placeholder: 'ระบุคำอธิบายหลักสูตร...',
                                    modules: {
                                        toolbar: [
                                            ['bold', 'italic', 'underline'],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            ['clean']
                                        ]
                                    }
                                });
                                descQuill.on('text-change', () => {
                                    form.description = descQuill.root.innerHTML === '<p><br></p>' ? '' : descQuill.root.innerHTML;
                                });
                                $watch('form.description', value => {
                                    if (descQuill && value !== descQuill.root.innerHTML) {
                                        descQuill.root.innerHTML = value || '';
                                    }
                                });
                            }">
                                <div x-ref="descEditor"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">หมวดหมู่</label>
                            <input type="text" x-model="form.category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition" placeholder="เช่น เทคโนโลยี, วิทยาการคำนวณ">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ระดับความยาก *</label>
                            <select x-model="form.level" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                                <option value="ทั่วไป">ทั่วไป</option>
                                <option value="ต้น">ต้น</option>
                                <option value="กลาง">กลาง</option>
                                <option value="สูง">สูง</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">เกณฑ์การสอบผ่าน (%) *</label>
                            <input type="number" min="0" max="100" x-model="form.pass_threshold" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">สถานะการแสดงผล *</label>
                            <select x-model="form.status" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition">
                                <option value="draft">ฉบับร่าง (Draft)</option>
                                <option value="published">เผยแพร่ (Published)</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">รูปภาพหน้าปกหลักสูตร (Cover Banner)</label>
                            <div class="flex flex-col gap-3">
                                <div class="w-full h-48 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 flex items-center justify-center p-1.5 overflow-hidden relative">
                                    <template x-if="form.previewUrl">
                                        <img :src="form.previewUrl" alt="Cover Preview" class="w-full h-full object-cover rounded-xl">
                                    </template>
                                    <template x-if="!form.previewUrl">
                                        <div class="text-center text-slate-400">
                                            <i class="fa-regular fa-image text-3xl mb-1.5 block"></i>
                                            <span class="text-xs">ยังไม่มีภาพปกหลักสูตร (คลิกด้านล่างเพื่อเลือกรูปภาพ)</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="$refs.coverInput.click()" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-slate-800 transition cursor-pointer">
                                        เลือกรูปภาพปก...
                                    </button>
                                    <template x-if="form.previewUrl">
                                        <button type="button" @click="removeCoverImage()" class="text-rose-500 hover:bg-rose-50 px-2 py-1.5 rounded-lg font-bold text-[10px] transition cursor-pointer">
                                            ลบรูปภาพปก
                                        </button>
                                    </template>
                                    <span class="text-[9px] text-slate-400 ms-auto">อัตราส่วนปกภาพแนวนอน (16:9) รองรับการครอบตัด</span>
                                </div>
                                <input type="file" x-ref="coverInput" class="hidden" accept="image/*" @change="coverSelected($event)">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ภาพพื้นหลังใบเกียรติบัตร (A4 แนวนอน)</label>
                            <input type="file" x-ref="certInput" @change="fileCertSelected" accept="image/*" class="w-full text-xs">
                        </div>
                    </div>

                    <!-- Cropper Canvas View (Visible only when cropping cover image) -->
                    <div class="space-y-4 flex flex-col" x-show="cropping" x-cloak>
                        <div class="p-4 sm:p-6 bg-slate-100 flex justify-center items-center overflow-hidden h-48 sm:h-64 md:h-72 rounded-2xl">
                            <img id="courseCropperImage" class="max-w-full max-h-full block">
                        </div>
                        
                        <!-- Cropper Controls -->
                        <div class="flex justify-between items-center bg-white text-slate-500">
                            <span class="text-[10px] font-medium text-slate-400">กรอบอัตราส่วนแบบแนวนอน 16:9 เท่านั้น</span>
                            <div class="flex gap-2">
                                <button type="button" class="p-2 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="หมุนขวา" @click="cropper.rotate(90)">
                                    <i class="fa-solid fa-rotate-right text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                                    <i class="fa-solid fa-magnifying-glass-plus text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="ซูมออก" @click="cropper.zoom(-0.1)">
                                    <i class="fa-solid fa-magnifying-glass-minus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions (Standard views) -->
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3" x-show="!cropping">
                        <button type="button" @click="modal.open = false" class="px-5 py-3 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-50 transition cursor-pointer">ยกเลิก</button>
                        <button type="submit" :disabled="saving" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูลหลักสูตร'"></span>
                        </button>
                    </div>

                    <!-- Modal Actions (Crop View) -->
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2" x-show="cropping" x-cloak>
                        <button type="button" @click="closeCropper()" class="px-4 py-2 bg-white border border-slate-200 text-slate-650 rounded-lg font-bold text-[10px] hover:bg-slate-50 transition cursor-pointer">
                            ยกเลิกการครอบตัด
                        </button>
                        <button type="button" @click="cropImage()" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-bold text-[10px] hover:bg-indigo-700 transition cursor-pointer">
                            บันทึกการครอบตัด
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function lmsCoursesAdmin() {
        return {
            courses: [],
            loading: false,
            saving: false,
            toast: { show: false, message: '', type: 'success' },
            modal: { open: false },
            descQuill: null,
            cropping: false,
            cropper: null,
            form: {
                id: '',
                title: '',
                description: '',
                category: 'ทั่วไป',
                level: 'ทั่วไป',
                pass_threshold: 60,
                status: 'draft',
                cover_file: null,
                cover_image_data: '',
                previewUrl: null,
                delete_cover_image: false,
                certificate_bg_file: null
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
                axios.get('{{ route("admin.lms.courses.data") }}')
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.courses = response.data.data;
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถดึงข้อมูลหลักสูตรได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            openCreateModal() {
                this.form = {
                    id: '',
                    title: '',
                    description: '',
                    category: 'ทั่วไป',
                    level: 'ทั่วไป',
                    pass_threshold: 60,
                    status: 'draft',
                    cover_file: null,
                    cover_image_data: '',
                    previewUrl: null,
                    delete_cover_image: false,
                    certificate_bg_file: null
                };
                this.cropping = false;
                if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                if (this.$refs.certInput) this.$refs.certInput.value = '';
                this.modal.open = true;
            },

            openEditModal(course) {
                this.form = {
                    id: course.id,
                    title: course.title,
                    description: course.description,
                    category: course.category,
                    level: course.level,
                    pass_threshold: course.pass_threshold,
                    status: course.status,
                    cover_file: null,
                    cover_image_data: '',
                    previewUrl: course.cover_url || null,
                    delete_cover_image: false,
                    certificate_bg_file: null
                };
                this.cropping = false;
                if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                if (this.$refs.certInput) this.$refs.certInput.value = '';
                this.modal.open = true;
            },

            coverSelected(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.cropping = true;
                    const imageEl = document.getElementById('courseCropperImage');
                    imageEl.src = e.target.result;
                    
                    setTimeout(() => {
                        this.initCropper(imageEl);
                    }, 100);
                };
                reader.readAsDataURL(file);
            },

            initCropper(imageEl) {
                if (this.cropper) {
                    this.cropper.destroy();
                }
                this.cropper = new Cropper(imageEl, {
                    aspectRatio: 16 / 9,
                    viewMode: 1,
                    background: false,
                    responsive: true,
                    checkOrientation: false
                });
            },

            cropImage() {
                if (!this.cropper) return;
                const canvas = this.cropper.getCroppedCanvas({
                    width: 640,
                    height: 360
                });
                this.form.cover_image_data = canvas.toDataURL('image/jpeg');
                this.form.previewUrl = this.form.cover_image_data;
                this.form.delete_cover_image = false;
                this.closeCropper();
            },

            removeCoverImage() {
                this.form.previewUrl = null;
                this.form.cover_image_data = '';
                this.form.delete_cover_image = true;
                if (this.$refs.coverInput) this.$refs.coverInput.value = '';
            },

            closeCropper() {
                this.cropping = false;
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
                if (this.$refs.coverInput) this.$refs.coverInput.value = '';
            },

            fileCertSelected(e) {
                this.form.certificate_bg_file = e.target.files[0];
            },

            saveCourse() {
                this.saving = true;
                const fd = new FormData();
                fd.append('title', this.form.title);
                fd.append('description', this.form.description || '');
                fd.append('category', this.form.category || 'ทั่วไป');
                fd.append('level', this.form.level);
                fd.append('pass_threshold', this.form.pass_threshold);
                fd.append('status', this.form.status);
                if (this.form.id) {
                    fd.append('id', this.form.id);
                }
                if (this.form.cover_image_data) {
                    fd.append('cover_image_data', this.form.cover_image_data);
                }
                if (this.form.delete_cover_image) {
                    fd.append('delete_cover_image', this.form.delete_cover_image ? 1 : 0);
                }
                if (this.form.certificate_bg_file) {
                    fd.append('certificate_bg_file', this.form.certificate_bg_file);
                }

                axios.post('{{ route("admin.lms.courses.save") }}', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showToast(response.data.message, 'success');
                        this.modal.open = false;
                        if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                        if (this.$refs.certInput) this.$refs.certInput.value = '';
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

            deleteCourse(id) {
                Swal.fire({
                    title: 'ยืนยันการลบหลักสูตร?',
                    text: 'หากลบหลักสูตร ข้อมูลบทเรียน แบบทดสอบ การส่งงาน และความก้าวหน้าของผู้เรียนทั้งหมดในหลักสูตรนี้จะถูกลบอย่างถาวร!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/admin/lms/courses/${id}`, {
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
