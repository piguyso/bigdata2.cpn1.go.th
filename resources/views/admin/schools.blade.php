<x-layout>
    <x-slot:title>จัดการข้อมูลโรงเรียน | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="schoolManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message"
             x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">School Master Data</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">จัดการข้อมูลโรงเรียน</h2>
                <p class="text-slate-500 text-sm mt-1">เพิ่ม แก้ไข และลบข้อมูลจากตาราง system_school</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.schools.template') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-arrow-down text-orange-600"></i> ดาวน์โหลด Template (.csv)
                </a>
                <input type="file" x-ref="importFile" class="hidden" accept=".csv,.txt,text/csv,text/plain" @change="importSchools($event)">
                <button type="button" @click="$refs.importFile.click()" :disabled="importing" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2 disabled:opacity-50">
                    <i :class="importing ? 'fa-solid fa-circle-notch animate-spin' : 'fa-solid fa-file-import'"></i>
                    <span x-text="importing ? 'กำลังนำเข้า...' : 'Import CSV'"></span>
                </button>
                <a href="{{ route('admin.school-group.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-layer-group"></i> จัดการเครือข่าย
                </a>
                <button type="button" @click="openCreateModal()" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> เพิ่มโรงเรียน
                </button>
            </div>
        </header>

        <section class="mb-5 grid grid-cols-1 md:grid-cols-[1fr_220px] gap-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="search" x-model.debounce.250ms="search" class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="ค้นหาชื่อโรงเรียน, SMIS, อำเภอ, ตำบล">
            </div>
            <select x-model="groupFilter" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none">
                <option value="">ทุกเครือข่าย</option>
                <template x-for="group in groups" :key="group.code">
                    <option :value="group.code" x-text="group.code + ' - ' + group.name"></option>
                </template>
            </select>
        </section>

        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังโหลดข้อมูลโรงเรียน...</p>
        </div>

        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak>
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">ทั้งหมด <span class="text-slate-900" x-text="filteredSchools().length"></span> รายการ</span>
                <span class="text-[10px] font-bold text-slate-400">ข้อมูลหลักโรงเรียน</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                            <th class="py-4 px-5 w-28">SMIS</th>
                            <th class="py-4 px-5 min-w-64">โรงเรียน</th>
                            <th class="py-4 px-5 min-w-48">เครือข่าย</th>
                            <th class="py-4 px-5 min-w-56">ที่ตั้ง</th>
                            <th class="py-4 px-5 min-w-40">ติดต่อ</th>
                            <th class="py-4 px-5 w-28 text-center">สถานะ</th>
                            <th class="py-4 px-5 w-28 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="filteredSchools().length === 0">
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">ไม่พบข้อมูลโรงเรียน</td>
                            </tr>
                        </template>
                        <template x-for="school in filteredSchools()" :key="school.id">
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-5 font-bold text-slate-700" x-text="school.smis"></td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <template x-if="school.logo_url">
                                            <img :src="school.logo_url" :alt="school.schoolname" class="w-10 h-10 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                        </template>
                                        <div x-show="!school.logo_url" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-school text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-850 truncate" x-text="school.schoolname"></div>
                                            <div class="text-[10px] text-slate-400 mt-1 truncate" x-text="school.schoolname_eng || '-'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 rounded-md font-bold text-[10px]" x-text="school.schoolgroup_name || school.schoolgroup || '-'"></span>
                                </td>
                                <td class="py-4 px-5 text-slate-500">
                                    <div x-text="addressLine(school)"></div>
                                    <div class="text-[10px] text-slate-400 mt-1" x-text="school.postcode || ''"></div>
                                </td>
                                <td class="py-4 px-5 text-slate-500">
                                    <div x-text="school.tel || '-'"></div>
                                    <div class="text-[10px] text-slate-400 mt-1 truncate max-w-44" x-text="school.email || school.website || '-'"></div>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-2.5 py-1 rounded-md font-bold text-[10px]"
                                          :class="school.statusID === '1' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                          x-text="school.statusDetail || school.statusID || '-'"></span>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal(school)" class="text-slate-500 hover:text-orange-600 hover:bg-orange-50 px-2 py-2 rounded-lg transition" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(school)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition" title="ลบ">
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

        <div x-show="modal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <form @submit.prevent="saveSchool()" class="bg-white rounded-3xl max-w-4xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/70 shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-school text-orange-500"></i>
                        <span x-text="form.id ? 'แก้ไขข้อมูลโรงเรียน' : 'เพิ่มโรงเรียนใหม่'"></span>
                    </h3>
                    <button type="button" @click="modal.open = false" class="text-slate-400 hover:text-slate-700 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">รหัส SMIS *</span>
                            <input type="text" x-model="form.smis" required maxlength="20" class="form-input" placeholder="เช่น 86010001">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">รหัส percode</span>
                            <input type="text" x-model="form.percode" maxlength="20" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">สังกัด/กระทรวง</span>
                            <input type="text" x-model="form.ministry" maxlength="20" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ชื่อโรงเรียน *</span>
                            <input type="text" x-model="form.schoolname" required class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ชื่อภาษาอังกฤษ</span>
                            <input type="text" x-model="form.schoolname_eng" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="space-y-1.5 md:col-span-2">
                            <span class="text-xs font-bold text-slate-500">เครือข่ายสถานศึกษา *</span>
                            <select x-model="form.schoolgroup" required class="form-input">
                                <option value="">เลือกเครือข่าย</option>
                                <template x-for="group in groups" :key="group.code">
                                    <option :value="group.code" x-text="group.code + ' - ' + group.name"></option>
                                </template>
                            </select>
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">สถานะ ID</span>
                            <input type="text" x-model="form.statusID" maxlength="1" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">รายละเอียดสถานะ</span>
                            <input type="text" x-model="form.statusDetail" maxlength="20" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">หมู่ที่</span>
                            <input type="text" x-model="form.muti" maxlength="10" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ถนน</span>
                            <input type="text" x-model="form.road" maxlength="100" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">หมู่บ้าน</span>
                            <input type="text" x-model="form.muban" maxlength="100" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ตำบล</span>
                            <input type="text" x-model="form.tambon" maxlength="100" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">อำเภอ</span>
                            <input type="text" x-model="form.amper" maxlength="100" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">จังหวัด</span>
                            <input type="text" x-model="form.province" maxlength="100" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">รหัสไปรษณีย์</span>
                            <input type="text" x-model="form.postcode" maxlength="100" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ระยะทาง (กม.)</span>
                            <input type="text" x-model="form.length_km" maxlength="10" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">โทรศัพท์</span>
                            <input type="text" x-model="form.tel" maxlength="20" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">อีเมล</span>
                            <input type="email" x-model="form.email" maxlength="150" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">เว็บไซต์</span>
                            <input type="text" x-model="form.website" maxlength="150" class="form-input">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">Latitude</span>
                            <input type="text" x-model="form.lat" maxlength="80" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">Longitude</span>
                            <input type="text" x-model="form.lng" maxlength="80" class="form-input">
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-500">ลิงก์แผนที่</span>
                            <input type="text" x-model="form.maplink" maxlength="255" class="form-input">
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/70 shrink-0">
                    <button type="button" @click="modal.open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">ยกเลิก</button>
                    <button type="submit" :disabled="saving" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition inline-flex items-center gap-2">
                        <i x-show="saving" class="fa-solid fa-circle-notch animate-spin"></i>
                        <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบโรงเรียน</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบ <span class="font-bold text-slate-700" x-text="deleteModal.schoolName"></span> หรือไม่?</p>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button>
                    <button type="button" @click="deleteSchool()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .form-input {
                width: 100%;
                border: 1px solid #e2e8f0;
                border-radius: 0.75rem;
                background: #f8fafc;
                padding: 0.625rem 1rem;
                font-size: 0.75rem;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            }
            .form-input:focus {
                background: #fff;
                border-color: #f97316;
                box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
            }
        </style>
        <script>
            function schoolManager() {
                const blankForm = () => ({
                    id: null,
                    smis: '',
                    percode: '',
                    ministry: '',
                    schoolname: '',
                    schoolname_eng: '',
                    schoolgroup: '',
                    muti: '',
                    road: '',
                    muban: '',
                    tambon: '',
                    amper: '',
                    province: 'ชุมพร',
                    postcode: '',
                    lat: '',
                    lng: '',
                    length_km: '',
                    maplink: '',
                    tel: '',
                    email: '',
                    website: '',
                    statusID: '1',
                    statusDetail: 'เปิด'
                });

                return {
                    loading: true,
                    saving: false,
                    importing: false,
                    search: '',
                    groupFilter: '',
                    schools: [],
                    groups: [],
                    form: blankForm(),
                    modal: { open: false },
                    deleteModal: { open: false, schoolId: null, schoolName: '' },
                    toast: { show: false, message: '', type: 'success' },

                    init() {
                        this.fetchSchools();
                    },

                    fetchSchools() {
                        this.loading = true;
                        axios.get('{{ route('admin.schools.data') }}')
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.schools = response.data.data;
                                    this.groups = response.data.groups;
                                }
                            })
                            .catch(() => this.showToast('ไม่สามารถโหลดข้อมูลโรงเรียนได้', 'error'))
                            .finally(() => this.loading = false);
                    },

                    filteredSchools() {
                        const keyword = this.search.trim().toLowerCase();
                        return this.schools.filter(school => {
                            const matchesGroup = !this.groupFilter || school.schoolgroup === this.groupFilter;
                            const text = [
                                school.smis,
                                school.schoolname,
                                school.schoolname_eng,
                                school.schoolgroup_name,
                                school.tambon,
                                school.amper,
                                school.tel
                            ].join(' ').toLowerCase();
                            return matchesGroup && (!keyword || text.includes(keyword));
                        });
                    },

                    addressLine(school) {
                        return [
                            school.muban,
                            school.tambon ? `ต.${school.tambon}` : '',
                            school.amper ? `อ.${school.amper}` : '',
                            school.province ? `จ.${school.province}` : ''
                        ].filter(Boolean).join(' ') || '-';
                    },

                    openCreateModal() {
                        this.form = blankForm();
                        this.modal.open = true;
                    },

                    openEditModal(school) {
                        this.form = { ...blankForm(), ...school };
                        this.modal.open = true;
                    },

                    saveSchool() {
                        this.saving = true;
                        axios.post('{{ route('admin.schools.save') }}', this.form)
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
                                const msg = error.response?.data?.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                                this.showToast(msg, 'error');
                            })
                            .finally(() => this.saving = false);
                    },

                    importSchools(event) {
                        const file = event.target.files?.[0];
                        event.target.value = '';

                        if (!file) {
                            return;
                        }

                        if (!/\.(csv|txt|xlsx)$/i.test(file.name)) {
                            this.showToast('รองรับไฟล์ .csv, .txt หรือ .xlsx', 'error');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('file', file);
                        this.importing = true;

                        axios.post('{{ route('admin.schools.import') }}', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        })
                            .then(response => {
                                if (response.data.status === 'success') {
                                    const summary = response.data.summary || {};
                                    const message = [
                                        response.data.message,
                                        `เพิ่มโรงเรียน ${summary.schools_created || 0}`,
                                        `แก้ไขโรงเรียน ${summary.schools_updated || 0}`,
                                        `ข้าม ${summary.skipped_rows || 0}`
                                    ].join(' | ');
                                    this.showToast(message, 'success');
                                    this.fetchSchools();
                                } else {
                                    this.showToast(response.data.message || 'นำเข้าไม่สำเร็จ', 'error');
                                }
                            })
                            .catch(error => {
                                const data = error.response?.data || {};
                                const warnings = Array.isArray(data.summary?.warnings) ? data.summary.warnings.slice(0, 3).join(' | ') : '';
                                this.showToast(warnings || data.message || 'เกิดข้อผิดพลาดในการนำเข้าไฟล์', 'error');
                            })
                            .finally(() => this.importing = false);
                    },

                    confirmDelete(school) {
                        this.deleteModal = { open: true, schoolId: school.id, schoolName: school.schoolname };
                    },

                    deleteSchool() {
                        const id = this.deleteModal.schoolId;
                        this.deleteModal.open = false;
                        axios.delete(`{{ url('/admin/schools') }}/${id}`)
                            .then(response => {
                                this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', response.data.status === 'success' ? 'success' : 'error');
                                this.fetchSchools();
                            })
                            .catch(() => this.showToast('เกิดข้อผิดพลาดในการลบข้อมูลโรงเรียน', 'error'));
                    },

                    showToast(message, type = 'success') {
                        this.toast = { show: true, message, type };
                        setTimeout(() => this.toast.show = false, 3500);
                    }
                };
            }
        </script>
    @endpush
</x-layout>
