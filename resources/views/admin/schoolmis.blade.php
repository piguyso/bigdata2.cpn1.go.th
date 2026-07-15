<x-layout>
    <x-slot:title>นำเข้าข้อมูล SchoolMIS | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="schoolmisManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message"
             x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">SchoolMIS Import</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้าข้อมูล SchoolMIS</h2>
                <p class="text-slate-500 text-sm mt-1">อัปโหลดไฟล์ CSV หรือ XLSX แยกตามปีการศึกษาและรอบข้อมูล พร้อมตรวจสอบก่อนบันทึกจริง</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.academic-years.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-calendar-days"></i> จัดการปีการศึกษา
                </a>
                <a href="{{ route('admin.schools.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-school"></i> ข้อมูลโรงเรียน
                </a>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีปัจจุบัน</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="activeYear || '-'"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">จำนวนปีในระบบ</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="years.length"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ข้อมูลที่นำเข้าแล้ว</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="recordCount"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ชุดล่าสุด</p>
                <p class="text-sm font-extrabold text-slate-900 mt-2" x-text="latestImportedYear ? 'ปี ' + latestImportedYear : 'ยังไม่มีข้อมูล'"></p>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] gap-6 items-start">
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-arrow-up text-orange-500"></i>
                        อัปโหลดและตรวจสอบไฟล์
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">ปีการศึกษา *</span>
                            <select x-model="form.academic_year" class="form-input">
                                <option value="">เลือกปีการศึกษา</option>
                                <template x-for="year in years" :key="year.id">
                                    <option :value="year.year" x-text="year.year + ' - ' + year.name"></option>
                                </template>
                            </select>
                        </label>
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">รอบข้อมูล *</span>
                            <select x-model="form.term" class="form-input">
                                <option value="1">รอบ 1</option>
                                <option value="2">รอบ 2</option>
                                <option value="3">รอบ 3</option>
                            </select>
                        </label>
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">โหมดนำเข้า</span>
                            <select x-model="form.mode" class="form-input">
                                <option value="replace">แทนที่ข้อมูลปี/รอบนี้ทั้งหมด</option>
                            </select>
                        </label>
                    </div>

                    <label class="block border-2 border-dashed border-slate-200 rounded-2xl p-5 bg-slate-50/50 hover:border-orange-300 transition cursor-pointer">
                        <input type="file" class="hidden" accept=".csv,.txt,text/csv,text/plain" @change="handleFileChange($event)">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-extrabold text-slate-800 truncate" x-text="form.fileName || 'เลือกไฟล์ SchoolMIS (.csv)'"></p>
                                <p class="text-xs text-slate-400 mt-1">รองรับไฟล์ข้อมูลดิบ SchoolMIS (.csv) คอลัมน์รูปแบบมาตรฐาน</p>
                            </div>
                            <span class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 shadow-sm">เลือกไฟล์</span>
                        </div>
                    </label>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="previewFile()" :disabled="previewLoading" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2">
                            <i x-show="previewLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!previewLoading" class="fa-solid fa-magnifying-glass-chart"></i>
                            <span x-text="previewLoading ? 'กำลังตรวจสอบไฟล์...' : 'ตรวจสอบก่อนนำเข้า'"></span>
                        </button>
                        <button type="button" @click="importFile()" :disabled="importLoading || !preview.uploadToken" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100 inline-flex items-center justify-center gap-2">
                            <i x-show="importLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!importLoading" class="fa-solid fa-database"></i>
                            <span x-text="importLoading ? 'กำลังนำเข้า...' : 'ยืนยันนำเข้าข้อมูล'"></span>
                        </button>
                        <button type="button" @click="openDeleteModal()" :disabled="deleteLoading || !form.academic_year || !form.term" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2">
                            <i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!deleteLoading" class="fa-solid fa-trash-can"></i>
                            <span x-text="deleteLoading ? 'กำลังลบข้อมูล...' : 'ลบข้อมูลปี/รอบนี้'"></span>
                        </button>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-slate-500">
                            <span class="font-bold text-slate-700">สถานะชุดข้อมูลที่เลือก:</span>
                            <span x-text="selectedDataSetSummary() ? 'มีข้อมูลพร้อมลบ/แทนที่' : 'ยังไม่มีข้อมูลที่นำเข้า'"></span>
                        </div>
                        <div class="text-slate-400 font-bold" x-show="selectedDataSetSummary()" x-cloak>
                            <span x-text="'โรงเรียน ' + formatNumber(selectedDataSetSummary().records_count)"></span>
                            <span class="mx-1.5">•</span>
                            <span x-text="'นักเรียน ' + formatNumber(selectedDataSetSummary().students_count)"></span>
                            <span class="mx-1.5">•</span>
                            <span x-text="'ห้อง ' + formatNumber(selectedDataSetSummary().rooms_count)"></span>
                        </div>
                    </div>

                    <div x-show="preview.summary" class="space-y-4" x-cloak>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <template x-for="item in summaryCards()" :key="item.label">
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.label"></p>
                                    <p class="text-xl font-extrabold text-slate-900 mt-1" x-text="item.value"></p>
                                </div>
                            </template>
                        </div>

                        <div x-show="preview.summary?.warnings?.length" class="bg-amber-50 border border-amber-200 rounded-2xl p-4" x-cloak>
                            <p class="text-xs font-extrabold text-amber-700 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i> ข้อควรตรวจสอบ
                            </p>
                            <ul class="mt-2 space-y-1.5 text-xs text-amber-800 list-disc pl-4">
                                <template x-for="warning in preview.summary.warnings" :key="warning">
                                    <li x-text="warning"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                                    <h4 class="font-bold text-slate-700 text-xs">ตัวอย่างข้อมูลที่อ่านได้</h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-white text-slate-400 font-bold">
                                            <tr>
                                                <th class="px-4 py-3">SMIS</th>
                                                <th class="px-4 py-3">โรงเรียน</th>
                                                <th class="px-4 py-3">รวม</th>
                                                <th class="px-4 py-3">ห้อง</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <template x-for="row in preview.summary.sample_rows" :key="row.school_smis + '-' + row.raw_year_term">
                                                <tr>
                                                    <td class="px-4 py-3 font-bold text-slate-700" x-text="row.school_smis"></td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-slate-700" x-text="row.school_name || '-'"></div>
                                                        <div class="text-[10px] mt-1" :class="row.matched_school ? 'text-emerald-600' : 'text-rose-500'" x-text="row.matched_school ? 'จับคู่โรงเรียนได้' : 'ยังไม่พบใน system_school'"></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-500" x-text="row.student_total"></td>
                                                    <td class="px-4 py-3 text-slate-500" x-text="row.room_total"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                                    <h4 class="font-bold text-slate-700 text-xs">ข้อมูลไฟล์ที่ระบบตรวจพบ</h4>
                                </div>
                                <div class="p-4 space-y-3 text-xs">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-slate-400 font-bold">ชื่อไฟล์</span>
                                        <span class="text-slate-700 font-bold text-right break-all" x-text="preview.sourceFilename || '-'"></span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-slate-400 font-bold">ปี/รอบในไฟล์</span>
                                        <span class="text-slate-700 font-bold text-right" x-text="(preview.summary?.detected_year_terms || []).join(', ') || '-'"></span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-slate-400 font-bold">Schema</span>
                                        <span class="text-slate-700 font-bold text-right" x-text="(preview.summary?.schema_versions || []).join(', ') || '-'"></span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-slate-400 font-bold">โหมดนำเข้า</span>
                                        <span class="text-slate-700 font-bold text-right" x-text="form.mode === 'replace' ? 'แทนที่ข้อมูลปี/รอบนี้ทั้งหมด' : form.mode"></span>
                                    </div>
                                    <div x-show="preview.summary?.invalid_samples?.length" class="pt-2 border-t border-slate-100">
                                        <p class="text-slate-400 font-bold mb-2">ตัวอย่างแถวที่ข้าม</p>
                                        <div class="space-y-1.5">
                                            <template x-for="row in preview.summary.invalid_samples" :key="row.row_number">
                                                <div class="text-[11px] text-slate-500">
                                                    แถว <span class="font-bold text-slate-700" x-text="row.row_number"></span>
                                                    : <span x-text="row.reason"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>
                        ประวัติการนำเข้า
                    </h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <template x-if="imports.length === 0">
                        <div class="p-8 text-center text-slate-400 text-xs font-medium">ยังไม่มีประวัติการนำเข้าข้อมูล</div>
                    </template>
                    <template x-for="item in imports" :key="item.id">
                        <div class="p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="pr-2">
                                    <p class="text-sm font-extrabold text-slate-800" x-text="'ปี ' + item.academic_year + ' / รอบ ' + item.term"></p>
                                    <p class="text-[11px] text-slate-400 mt-1 break-all" x-text="item.source_filename"></p>
                                </div>
                                <div class="flex flex-col items-end gap-2 shrink-0">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700" x-text="item.mode === 'replace' ? 'replace' : item.mode"></span>
                                    <button type="button" 
                                            @click="confirmDeleteImport(item)" 
                                            class="text-slate-400 hover:text-rose-600 transition p-1.5 rounded-lg hover:bg-rose-50 border border-slate-100 bg-white shadow-sm mt-0.5"
                                            title="ลบชุดข้อมูลนี้">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <div class="bg-slate-50 rounded-xl px-3 py-2">
                                    <p class="text-slate-400 font-bold">นำเข้าได้</p>
                                    <p class="text-slate-800 font-extrabold mt-1" x-text="item.imported_rows"></p>
                                </div>
                                <div class="bg-slate-50 rounded-xl px-3 py-2">
                                    <p class="text-slate-400 font-bold">ไม่ match</p>
                                    <p class="text-slate-800 font-extrabold mt-1" x-text="item.unmatched_rows"></p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-[11px]">
                                <span class="text-slate-400" x-text="'schema: ' + (item.schema_version || '-')"></span>
                                <span class="text-slate-400" x-text="formatDateTime(item.created_at)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูล SchoolMIS</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">
                    ต้องการลบข้อมูลของ <span class="font-bold text-slate-700" x-text="'ปีการศึกษา ' + deleteModal.academicYear + ' รอบ ' + deleteModal.term"></span>
                    ทั้งหมดหรือไม่?
                    <span class="block mt-2 text-slate-500" x-show="deleteModal.recordsCount > 0" x-cloak>
                        ชุดข้อมูลนี้มี <span class="font-bold text-slate-700" x-text="formatNumber(deleteModal.recordsCount)"></span> โรงเรียน
                        และนักเรียนรวม <span class="font-bold text-slate-700" x-text="formatNumber(deleteModal.studentsCount)"></span> คน
                    </span>
                </p>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button>
                    <button type="button" @click="deleteDataSet()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button>
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
            function schoolmisManager() {
                return {
                    years: [],
                    imports: [],
                    dataSets: [],
                    activeYear: '',
                    latestImportedYear: '',
                    recordCount: 0,
                    previewLoading: false,
                    importLoading: false,
                    deleteLoading: false,
                    form: {
                        academic_year: '',
                        term: '1',
                        mode: 'replace',
                        file: null,
                        fileName: ''
                    },
                    preview: {
                        uploadToken: '',
                        sourceFilename: '',
                        summary: null
                    },
                    deleteModal: {
                        open: false,
                        academicYear: '',
                        term: '',
                        recordsCount: 0,
                        studentsCount: 0
                    },
                    toast: { show: false, message: '', type: 'success' },

                    init() {
                        this.fetchData();
                    },

                    fetchData() {
                        axios.get('{{ route('admin.schoolmis.data') }}')
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.years = response.data.years || [];
                                    this.imports = response.data.imports || [];
                                    this.dataSets = response.data.data_sets || [];
                                    this.activeYear = response.data.active_year || '';
                                    this.latestImportedYear = response.data.latest_imported_year || '';
                                    this.recordCount = response.data.record_count || 0;
                                    if (!this.form.academic_year) {
                                        this.form.academic_year = this.activeYear || (this.years[0]?.year || '');
                                    }
                                }
                            })
                            .catch(() => this.showToast('ไม่สามารถโหลดข้อมูล SchoolMIS ได้', 'error'));
                    },

                    handleFileChange(event) {
                        const file = event.target.files?.[0] || null;
                        this.form.file = file;
                        this.form.fileName = file ? file.name : '';
                        this.preview = { uploadToken: '', sourceFilename: '', summary: null };
                    },

                    previewFile() {
                        if (!this.form.academic_year) {
                            this.showToast('กรุณาเลือกปีการศึกษา', 'error');
                            return;
                        }

                        if (!this.form.file) {
                            this.showToast('กรุณาเลือกไฟล์ SchoolMIS ก่อน', 'error');
                            return;
                        }

                        this.previewLoading = true;
                        const formData = new FormData();
                        formData.append('academic_year', this.form.academic_year);
                        formData.append('term', this.form.term);
                        formData.append('csv', this.form.file);

                        axios.post('{{ route('admin.schoolmis.preview') }}', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        }).then(response => {
                            this.preview = {
                                uploadToken: response.data.upload_token,
                                sourceFilename: response.data.source_filename,
                                summary: response.data.preview
                            };
                            this.showToast(response.data.message || 'ตรวจสอบไฟล์เรียบร้อยแล้ว', 'success');
                        }).catch(error => {
                            const message = error.response?.data?.message || 'ไม่สามารถตรวจสอบไฟล์ได้';
                            this.showToast(message, 'error');
                        }).finally(() => {
                            this.previewLoading = false;
                        });
                    },

                    importFile() {
                        if (!this.preview.uploadToken) {
                            this.showToast('กรุณาตรวจสอบไฟล์ก่อนนำเข้า', 'error');
                            return;
                        }

                        this.importLoading = true;
                        axios.post('{{ route('admin.schoolmis.import') }}', {
                            academic_year: this.form.academic_year,
                            term: this.form.term,
                            mode: this.form.mode,
                            upload_token: this.preview.uploadToken,
                            source_filename: this.preview.sourceFilename
                        }).then(response => {
                            this.showToast(response.data.message || 'นำเข้าข้อมูลเรียบร้อยแล้ว', 'success');
                            this.preview = { uploadToken: '', sourceFilename: '', summary: null };
                            this.form.file = null;
                            this.form.fileName = '';
                            this.fetchData();
                        }).catch(error => {
                            this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล', 'error');
                        }).finally(() => {
                            this.importLoading = false;
                        });
                    },

                    confirmDeleteImport(item) {
                        if (confirm(`คุณต้องการลบข้อมูลนำเข้า SchoolMIS ปี ${item.academic_year} รอบ ${item.term} ใช่หรือไม่?\nข้อมูลผลคะแนนทั้งหมดในชุดนี้จะถูกลบออกจากระบบด้วย`)) {
                            axios.delete(`/admin/schoolmis/import/${item.id}`)
                                .then(response => {
                                    if (response.data.status === 'success') {
                                        this.showToast(response.data.message, 'success');
                                        this.fetchData();
                                    } else {
                                        this.showToast(response.data.message || 'เกิดข้อผิดพลาดในการลบ', 'error');
                                    }
                                })
                                .catch(error => {
                                    this.showToast(error.response?.data?.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                                });
                        }
                    },

                    openDeleteModal() {
                        if (!this.form.academic_year || !this.form.term) {
                            this.showToast('กรุณาเลือกปีการศึกษาและรอบข้อมูลก่อน', 'error');
                            return;
                        }

                        const selected = this.selectedDataSetSummary();
                        if (!selected) {
                            this.showToast('ยังไม่มีข้อมูลของปีและรอบที่เลือกสำหรับลบ', 'error');
                            return;
                        }

                        this.deleteModal = {
                            open: true,
                            academicYear: this.form.academic_year,
                            term: this.form.term,
                            recordsCount: Number(selected.records_count || 0),
                            studentsCount: Number(selected.students_count || 0)
                        };
                    },

                    deleteDataSet() {
                        this.deleteLoading = true;
                        const payload = {
                            academic_year: this.deleteModal.academicYear,
                            term: this.deleteModal.term
                        };

                        axios.delete('{{ route('admin.schoolmis.delete') }}', { data: payload })
                            .then(response => {
                                this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', 'success');
                                this.deleteModal.open = false;
                                this.preview = { uploadToken: '', sourceFilename: '', summary: null };
                                this.fetchData();
                            })
                            .catch(error => {
                                const message = error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล';
                                this.showToast(message, 'error');
                            })
                            .finally(() => {
                                this.deleteLoading = false;
                            });
                    },

                    summaryCards() {
                        if (!this.preview.summary) {
                            return [];
                        }

                        return [
                            { label: 'แถวทั้งหมด', value: this.preview.summary.total_rows },
                            { label: 'แถวที่อ่านได้', value: this.preview.summary.valid_rows },
                            { label: 'แถวไม่สมบูรณ์', value: this.preview.summary.invalid_rows },
                            { label: 'ไม่ match โรงเรียน', value: this.preview.summary.unmatched_rows },
                        ];
                    },

                    selectedDataSetSummary() {
                        return this.dataSets.find(item =>
                            String(item.academic_year) === String(this.form.academic_year) &&
                            String(item.term) === String(this.form.term)
                        ) || null;
                    },

                    formatNumber(value) {
                        return Number(value || 0).toLocaleString('th-TH');
                    },

                    formatDateTime(value) {
                        if (!value) {
                            return '-';
                        }

                        const date = new Date(value);
                        if (Number.isNaN(date.getTime())) {
                            return value;
                        }

                        return date.toLocaleString('th-TH');
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
