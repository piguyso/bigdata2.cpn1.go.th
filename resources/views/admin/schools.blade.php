<x-layout>
    <x-slot:title>จัดการเครือข่ายสถานศึกษา | EE CPN1</x-slot>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="schoolManager()" x-init="init()">
        <!-- Toast Notification (Floating Glassmorphic) -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl backdrop-blur-md border border-white/20"
             :class="toast.type === 'success' ? 'bg-emerald-500/95 text-white' : 'bg-rose-500/95 text-white'"
             x-cloak>
            <template x-if="toast.type === 'success'">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </template>
            <template x-if="toast.type === 'error'">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </template>
            <span class="text-xs font-bold" x-text="toast.message"></span>
        </div>

        <header class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">จัดการเครือข่ายสถานศึกษา</h2>
                <p class="text-slate-500 text-sm mt-1">เพิ่ม แก้ไข และลบข้อมูลสถาบันการศึกษาในกลุ่มภาคีเครือข่ายพัฒนาครู ชุมพร เขต 1</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> เพิ่มโรงเรียนใหม่
                </button>
            </div>
        </header>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังดาวน์โหลดข้อมูลเครือข่ายสถานศึกษา...</p>
        </div>

        <!-- Schools List View (No refresh table) -->
        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-450 uppercase font-bold tracking-wider">
                            <th class="py-4 px-6 w-16">ตราสัญลักษณ์</th>
                            <th class="py-4 px-6">ชื่อโรงเรียน</th>
                            <th class="py-4 px-6">อำเภอ</th>
                            <th class="py-4 px-6">ที่ตั้ง / ที่อยู่</th>
                            <th class="py-4 px-6">เว็บไซต์</th>
                            <th class="py-4 px-6 text-center w-36">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="schools.length === 0">
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                                    <div class="mb-2 text-2xl">🏫</div>
                                    ยังไม่มีข้อมูลเครือข่ายสถานศึกษาในระบบ กดเพิ่มเครือข่ายสถานศึกษาได้เลย
                                </td>
                            </tr>
                        </template>
                        <template x-for="school in schools" :key="school.id">
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="py-4 px-6">
                                    <div class="w-10 h-10 border border-slate-100 bg-slate-50 rounded-lg flex items-center justify-center p-1 overflow-hidden shrink-0">
                                        <template x-if="school.logo_url">
                                            <img :src="school.logo_url" alt="Logo" class="max-w-full max-h-full object-contain">
                                        </template>
                                        <template x-if="!school.logo_url">
                                            <span class="text-base">🏫</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-800" x-text="school.name"></td>
                                <td class="py-4 px-6 flex flex-col gap-1 items-start">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-md text-[10px]" x-text="school.district"></span>
                                    <template x-if="school.school_group">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-bold rounded-md text-[10px]" x-text="school.school_group"></span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-slate-500" x-text="school.address || '-'"></td>
                                <td class="py-4 px-6">
                                    <template x-if="school.website">
                                        <a :href="school.website" target="_blank" class="text-sky-500 hover:underline font-medium inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> เปิดลิงก์
                                        </a>
                                    </template>
                                    <template x-if="!school.website">
                                        <span class="text-slate-400">-</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal(school)" class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-2 rounded-lg transition" title="แก้ไขข้อมูล">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(school)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition" title="ลบสถาบัน">
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

        <!-- Add/Edit School Modal -->
        <div x-show="modal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <form @submit.prevent="saveSchool()" class="bg-white rounded-[2rem] max-w-xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] md:max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-school text-emerald-500"></i>
                        <span x-text="cropping ? 'ครอบตัดตราโลโก้โรงเรียน (1:1)' : (form.id ? 'แก้ไขข้อมูลเครือข่ายสถานศึกษา' : 'เพิ่มเครือข่ายสถานศึกษาใหม่')"></span>
                    </h3>
                    <button type="button" @click="cropping ? closeCropper() : (modal.open = false)" class="text-slate-400 hover:text-slate-650 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <div class="p-6 md:p-8 space-y-5 overflow-y-auto flex-1">
                    <!-- Standard Form Fields (Visible only when NOT cropping) -->
                    <div x-show="!cropping" class="space-y-5">
                        <!-- Name Input -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อโรงเรียน (School Name) *</label>
                            <input type="text" 
                                   x-model="form.name" 
                                   required 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น โรงเรียนบ้านแก่งช้าง">
                        </div>

                        <!-- District Selector -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">อำเภอที่ตั้ง (District) *</label>
                            <select x-model="form.district" 
                                    required 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                <option value="">เลือกอำเภอ...</option>
                                <option value="อำเภอเมืองชุมพร">อำเภอเมืองชุมพร</option>
                                <option value="อำเภอท่าแซะ">อำเภอท่าแซะ</option>
                                <option value="อำเภอปะทิว">อำเภอปะทิว</option>
                            </select>
                        </div>

                        <!-- School Network Selector -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เครือข่ายสถานศึกษา (Network Group) *</label>
                            <select x-model="form.school_group" 
                                    required 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                <option value="">เลือกเครือข่าย...</option>
                                <option value="เมืองชุมพร 1">เมืองชุมพร 1</option>
                                <option value="เมืองชุมพร 2">เมืองชุมพร 2</option>
                                <option value="เมืองชุมพร 3">เมืองชุมพร 3</option>
                                <option value="เมืองชุมพร 4">เมืองชุมพร 4</option>
                                <option value="เมืองชุมพร 5">เมืองชุมพร 5</option>
                                <option value="ท่าแซะ 1">ท่าแซะ 1</option>
                                <option value="ท่าแซะ 2">ท่าแซะ 2</option>
                                <option value="ท่าแซะ 3">ท่าแซะ 3</option>
                                <option value="ปะทิว 1">ปะทิว 1</option>
                                <option value="ปะทิว 2">ปะทิว 2</option>
                            </select>
                        </div>

                        <!-- Address Input -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ที่ตั้ง / ที่อยู่โรงเรียน (Address)</label>
                            <textarea x-model="form.address" 
                                      rows="2" 
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                      placeholder="เช่น ถ.ปรมินทรมรรคา ต.ท่าตะเภา อ.เมือง จ.ชุมพร"></textarea>
                        </div>

                        <!-- Website URL -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เว็บไซต์โรงเรียน (Website URL)</label>
                            <input type="url" 
                                   x-model="form.website" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น https://www.school-website.ac.th">
                        </div>

                        <!-- Logo Selector interface -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ตราสัญลักษณ์โรงเรียน (Logo)</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 flex items-center justify-center p-1.5 overflow-hidden shrink-0 relative">
                                    <template x-if="form.previewUrl">
                                        <img :src="form.previewUrl" alt="Logo Preview" class="max-w-full max-h-full object-contain">
                                    </template>
                                    <template x-if="!form.previewUrl">
                                        <span class="text-slate-400 text-lg">🏫</span>
                                    </template>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="$refs.logoInput.click()" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-slate-800 transition">
                                            เลือกไฟล์โลโก้...
                                        </button>
                                        <template x-if="form.previewUrl">
                                            <button type="button" @click="removeLogo()" class="text-rose-500 hover:bg-rose-50 px-2 py-1.5 rounded-lg font-bold text-[10px] transition">
                                                ลบโลโก้
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-slate-400">แนะนำเป็นรูปจัตุรัสและไม่มีพื้นหลัง (PNG) รองรับการครอบตัดแบบ 1:1</p>
                                </div>
                            </div>
                            <input type="file" x-ref="logoInput" class="hidden" accept="image/*" @change="logoSelected($event)">
                        </div>
                    </div>

                    <!-- Cropper Canvas View (Visible only when cropping logo) -->
                    <div x-show="cropping" class="space-y-4 flex flex-col" x-cloak>
                        <div class="p-4 sm:p-6 bg-slate-100 flex justify-center items-center overflow-hidden h-48 sm:h-64 md:h-72 rounded-2xl">
                            <img id="schoolCropperImage" class="max-w-full max-h-full block">
                        </div>
                        
                        <!-- Controls -->
                        <div class="flex justify-between items-center bg-white text-slate-500">
                            <span class="text-[10px] font-medium text-slate-400">กรอบจัตุรัสแบบ 1:1 เท่านั้น</span>
                            <div class="flex gap-2">
                                <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="หมุนขวา" @click="cropper.rotate(90)">
                                    <i class="fa-solid fa-rotate-right text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                                    <i class="fa-solid fa-magnifying-glass-plus text-xs"></i>
                                </button>
                                <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="ซูมออก" @click="cropper.zoom(-0.1)">
                                    <i class="fa-solid fa-magnifying-glass-minus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions (Form View) -->
                <div x-show="!cropping" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50 shrink-0">
                    <button type="button" @click="modal.open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            :disabled="saving" 
                            class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-lg shadow-emerald-100 flex items-center gap-2">
                        <template x-if="saving">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                        </template>
                        <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล'"></span>
                    </button>
                </div>

                <!-- Actions (Crop View) -->
                <div x-show="cropping" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50/50 shrink-0" x-cloak>
                    <button type="button" @click="closeCropper()" class="px-4 py-2 bg-white border border-slate-200 text-slate-650 rounded-lg font-bold text-[10px] hover:bg-slate-50 transition">
                        ยกเลิก
                    </button>
                    <button type="button" @click="cropImage()" class="px-5 py-2 bg-emerald-600 text-white rounded-lg font-bold text-[10px] hover:bg-emerald-700 transition">
                        บันทึกการครอบตัด
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-[2rem] max-w-sm w-full overflow-hidden shadow-2xl border border-slate-100 p-6 flex flex-col gap-5">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบสถาบันเครือข่าย</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ต้องการลบข้อมูลโรงเรียน <span class="font-bold text-slate-650" x-text="deleteModal.schoolName"></span> หรือไม่? เมื่อทำรายการแล้วจะไม่สามารถย้อนคืนได้
                    </p>
                </div>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-150 text-slate-600 rounded-xl font-bold text-xs transition">
                        ยกเลิก
                    </button>
                    <button type="button" @click="deleteSchool()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition shadow-md shadow-rose-100">
                        ยืนยันการลบ
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function schoolManager() {
            return {
                loading: true,
                saving: false,
                schools: [],
                cropping: false,
                form: {
                    id: null,
                    name: '',
                    district: '',
                    school_group: '',
                    address: '',
                    website: '',
                    logo_data: '',
                    previewUrl: null,
                    delete_logo: false
                },
                modal: {
                    open: false
                },
                deleteModal: {
                    open: false,
                    schoolId: null,
                    schoolName: ''
                },
                cropper: null,
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                init() {
                    this.fetchSchools();
                },

                fetchSchools() {
                    this.loading = true;
                    axios.get('{{ route('admin.schools.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.schools = response.data.data;
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Schools Error:', error);
                            this.showToast('ไม่สามารถโหลดข้อมูลโรงเรียนได้', 'error');
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                openCreateModal() {
                    this.resetForm();
                    this.modal.open = true;
                },

                openEditModal(school) {
                    this.resetForm();
                    this.form.id = school.id;
                    this.form.name = school.name;
                    this.form.district = school.district;
                    this.form.school_group = school.school_group || '';
                    this.form.address = school.address || '';
                    this.form.website = school.website || '';
                    this.form.previewUrl = school.logo_url;
                    this.modal.open = true;
                },

                resetForm() {
                    this.form.id = null;
                    this.form.name = '';
                    this.form.district = '';
                    this.form.school_group = '';
                    this.form.address = '';
                    this.form.website = '';
                    this.form.logo_data = '';
                    this.form.previewUrl = null;
                    this.form.delete_logo = false;
                    this.cropping = false;
                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                },

                logoSelected(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.cropping = true;
                        const imageEl = document.getElementById('schoolCropperImage');
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
                        aspectRatio: 1, // 1:1 ratio
                        viewMode: 1,
                        background: false,
                        responsive: true,
                        checkOrientation: false
                    });
                },

                cropImage() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 256,
                        height: 256
                    });
                    this.form.logo_data = canvas.toDataURL('image/png');
                    this.form.previewUrl = this.form.logo_data;
                    this.form.delete_logo = false;
                    this.closeCropper();
                },

                removeLogo() {
                    this.form.previewUrl = null;
                    this.form.logo_data = '';
                    this.form.delete_logo = true;
                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                },

                closeCropper() {
                    this.cropping = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                },

                saveSchool() {
                    this.saving = true;
                    const payload = {
                        id: this.form.id,
                        name: this.form.name,
                        district: this.form.district,
                        school_group: this.form.school_group,
                        address: this.form.address,
                        website: this.form.website,
                        logo_data: this.form.logo_data,
                        delete_logo: this.form.delete_logo
                    };

                    axios.post('{{ route('admin.schools.save') }}', payload)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.modal.open = false;
                                this.fetchSchools();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Save School Error:', error);
                            const msg = error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                            this.showToast(msg, 'error');
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                },

                confirmDelete(school) {
                    this.deleteModal.schoolId = school.id;
                    this.deleteModal.schoolName = school.name;
                    this.deleteModal.open = true;
                },

                deleteSchool() {
                    this.deleteModal.open = false;
                    const id = this.deleteModal.schoolId;

                    axios.delete(`{{ url('/admin/schools') }}/${id}`)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchSchools();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Delete School Error:', error);
                            this.showToast('เกิดข้อผิดพลาดในการลบเครือข่ายสถานศึกษา', 'error');
                        });
                },

                showToast(message, type = 'success') {
                    this.toast.show = true;
                    this.toast.message = message;
                    this.toast.type = type;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                }
            };
        }
    </script>
    @endpush
</x-layout>

