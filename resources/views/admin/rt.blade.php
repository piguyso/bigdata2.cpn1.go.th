<x-layout>
    <x-slot:title>นำเข้าข้อมูล RT | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="rtManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message"
             x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">RT Import</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้าข้อมูล RT</h2>
                <p class="text-slate-500 text-sm mt-1">อัปโหลดไฟล์ CSV หรือ XLSX ผลการประเมิน RT ระบบจะสแกนทุก sheet และอ่านเฉพาะแถวที่มีรหัสโรงเรียน</p>
            </div>
            <a href="{{ route('admin.rt.template') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-arrow-down"></i> ดาวน์โหลด Template
            </a>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีปัจจุบัน</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="activeYear || '-'"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีที่มีข้อมูล</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="dataSets.length"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ข้อมูลที่นำเข้าแล้ว</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="formatNumber(recordCount)"></p>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <span class="text-xs font-bold text-slate-500">โหมดนำเข้า</span>
                            <select x-model="form.mode" class="form-input">
                                <option value="replace">แทนที่ข้อมูลปีนี้ทั้งหมด</option>
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
                                <p class="text-sm font-extrabold text-slate-800 truncate" x-text="form.fileName || 'เลือกไฟล์ RT (.csv)'"></p>
                                <p class="text-xs text-slate-400 mt-1">รองรับไฟล์ข้อมูลดิบ RT (.csv)</p>
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
                        <button type="button" @click="openDeleteModal()" :disabled="deleteLoading || !form.academic_year" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2">
                            <i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!deleteLoading" class="fa-solid fa-trash-can"></i>
                            <span x-text="deleteLoading ? 'กำลังลบข้อมูล...' : 'ลบข้อมูลปีนี้'"></span>
                        </button>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-slate-500">
                            <span class="font-bold text-slate-700">สถานะชุดข้อมูลที่เลือก:</span>
                            <span x-text="selectedDataSetSummary() ? 'มีข้อมูลพร้อมลบ/แทนที่' : 'ยังไม่มีข้อมูลที่นำเข้า'"></span>
                        </div>
                        <div class="text-slate-400 font-bold" x-show="selectedDataSetSummary()" x-cloak>
                            <span x-text="formatNumber(selectedDataSetSummary()?.records_count || 0)"></span> โรงเรียน
                            <span class="mx-1">/</span>
                            เฉลี่ยรวม <span x-text="formatScore(selectedDataSetSummary()?.avg_total_percent || 0)"></span>
                        </div>
                    </div>

                    <div x-show="preview.summary" x-cloak class="space-y-4">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <template x-for="card in summaryCards()" :key="card.label">
                                <div class="border border-slate-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="card.label"></p>
                                    <p class="text-xl font-extrabold text-slate-900 mt-1" x-text="card.value"></p>
                                </div>
                            </template>
                        </div>

                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-800" x-show="preview.summary?.warnings?.length">
                            <template x-for="warning in preview.summary.warnings" :key="warning">
                                <p class="font-bold" x-text="warning"></p>
                            </template>
                        </div>

                        <div class="border border-slate-100 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between gap-3">
                                <h4 class="text-xs font-extrabold text-slate-700">ตัวอย่างข้อมูลจากไฟล์</h4>
                                <span class="text-[10px] font-bold text-slate-400" x-text="'scan: ' + ((preview.summary?.sheet_names || []).length || 0) + ' sheets'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-white text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3 text-left">รหัส</th>
                                            <th class="px-4 py-3 text-left">โรงเรียน</th>
                                            <th class="px-4 py-3 text-right">เข้าสอบ</th>
                                            <th class="px-4 py-3 text-right">อ่านออกเสียง</th>
                                            <th class="px-4 py-3 text-right">อ่านรู้เรื่อง</th>
                                            <th class="px-4 py-3 text-right">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <template x-for="row in preview.summary.sample_rows" :key="row.rt_school_code">
                                            <tr>
                                                <td class="px-4 py-3 font-bold text-slate-700" x-text="row.rt_school_code"></td>
                                                <td class="px-4 py-3">
                                                    <div class="font-bold text-slate-700" x-text="row.school_name || '-'"></div>
                                                    <div class="text-[10px] mt-1" :class="row.matched_school ? 'text-emerald-600' : 'text-rose-500'" x-text="row.matched_school ? ('จับคู่ได้: ' + (row.system_smis || '-')) : 'ยังไม่พบใน system_school'"></div>
                                                </td>
                                                <td class="px-4 py-3 text-right text-slate-500" x-text="formatNumber(row.students_count)"></td>
                                                <td class="px-4 py-3 text-right text-slate-500" x-text="formatScore(row.reading_aloud_percent)"></td>
                                                <td class="px-4 py-3 text-right text-slate-500" x-text="formatScore(row.reading_comprehension_percent)"></td>
                                                <td class="px-4 py-3 text-right font-extrabold text-slate-700" x-text="formatScore(row.total_percent)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
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
                                    <p class="text-sm font-extrabold text-slate-800" x-text="'ปี ' + item.academic_year"></p>
                                    <p class="text-[11px] text-slate-400 mt-1 break-all" x-text="item.source_filename"></p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700" x-text="item.sheet_name || 'Local05'"></span>
                                    <button type="button" 
                                            @click="confirmDeleteImport(item)" 
                                            class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-rose-50 border border-slate-100 bg-white shadow-sm shrink-0"
                                            title="ลบชุดข้อมูลนี้">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <div class="bg-slate-50 rounded-xl px-3 py-2">
                                    <p class="text-slate-400 font-bold">นำเข้าได้</p>
                                    <p class="text-slate-800 font-extrabold mt-1" x-text="formatNumber(item.imported_rows)"></p>
                                </div>
                                <div class="bg-slate-50 rounded-xl px-3 py-2">
                                    <p class="text-slate-400 font-bold">ไม่ match</p>
                                    <p class="text-slate-800 font-extrabold mt-1" x-text="formatNumber(item.unmatched_rows)"></p>
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
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูล RT</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">
                    ต้องการลบข้อมูล RT ปีการศึกษา
                    <span class="font-bold text-slate-700" x-text="deleteModal.academicYear"></span>
                    ทั้งหมดหรือไม่?
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
            function rtManager() {
                return {
                    years: [], imports: [], dataSets: [], activeYear: '', latestImportedYear: '', recordCount: 0,
                    previewLoading: false, importLoading: false, deleteLoading: false,
                    form: { academic_year: '', mode: 'replace', file: null, fileName: '' },
                    preview: { uploadToken: '', sourceFilename: '', summary: null },
                    deleteModal: { open: false, academicYear: '' },
                    toast: { show: false, message: '', type: 'success' },
                    init() { this.fetchData(); },
                    fetchData() {
                        axios.get('{{ route('admin.rt.data') }}')
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
                            .catch(() => this.showToast('ไม่สามารถโหลดข้อมูล RT ได้', 'error'));
                    },
                    handleFileChange(event) {
                        const file = event.target.files?.[0] || null;
                        this.form.file = file;
                        this.form.fileName = file ? file.name : '';
                        this.preview = { uploadToken: '', sourceFilename: '', summary: null };
                    },
                    previewFile() {
                        if (!this.form.academic_year) return this.showToast('กรุณาเลือกปีการศึกษา', 'error');
                        if (!this.form.file) return this.showToast('กรุณาเลือกไฟล์ RT ก่อน', 'error');
                        this.previewLoading = true;
                        const formData = new FormData();
                        formData.append('academic_year', this.form.academic_year);
                        formData.append('xlsx', this.form.file);
                        axios.post('{{ route('admin.rt.preview') }}', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                            .then(response => {
                                this.preview = {
                                    uploadToken: response.data.upload_token,
                                    sourceFilename: response.data.source_filename,
                                    summary: response.data.preview
                                };
                                this.showToast(response.data.message || 'ตรวจสอบไฟล์เรียบร้อยแล้ว', 'success');
                            })
                            .catch(error => this.showToast(error.response?.data?.message || 'ไม่สามารถตรวจสอบไฟล์ได้', 'error'))
                            .finally(() => this.previewLoading = false);
                    },
                    importFile() {
                        if (!this.preview.uploadToken) return this.showToast('กรุณาตรวจสอบไฟล์ก่อนนำเข้า', 'error');
                        this.importLoading = true;
                        axios.post('{{ route('admin.rt.import') }}', {
                            academic_year: this.form.academic_year,
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
                        }).finally(() => this.importLoading = false);
                    },
                    openDeleteModal() {
                        if (!this.selectedDataSetSummary()) return this.showToast('ยังไม่มีข้อมูลของปีที่เลือกสำหรับลบ', 'error');
                        this.deleteModal = { open: true, academicYear: this.form.academic_year };
                    },
                    deleteDataSet() {
                        this.deleteLoading = true;
                        axios.delete('{{ route('admin.rt.delete') }}', { data: { academic_year: this.deleteModal.academicYear } })
                            .then(response => {
                                this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', 'success');
                                this.deleteModal.open = false;
                                this.fetchData();
                            })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error'))
                            .finally(() => this.deleteLoading = false);
                    },
                    confirmDeleteImport(item) {
                        window.showConfirm({
                            title: 'ยืนยันการลบข้อมูลนำเข้า RT',
                            text: `คุณต้องการลบข้อมูลนำเข้า RT ปีการศึกษา ${item.academic_year} ใช่หรือไม่? ข้อมูลผลคะแนน RT ทั้งหมดในชุดนี้จะถูกลบออกจากระบบโดยสมบูรณ์`,
                            confirmButtonText: 'ลบข้อมูล',
                            cancelButtonText: 'ยกเลิก',
                            type: 'danger',
                            onConfirm: () => {
                                axios.delete(`/admin/rt/import/${item.id}`)
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
                        });
                    },
                    summaryCards() {
                        if (!this.preview.summary) return [];
                        return [
                            { label: 'แถวทั้งหมด', value: this.formatNumber(this.preview.summary.total_rows) },
                            { label: 'แถวที่อ่านได้', value: this.formatNumber(this.preview.summary.valid_rows) },
                            { label: 'แถวไม่สมบูรณ์', value: this.formatNumber(this.preview.summary.invalid_rows) },
                            { label: 'ไม่ match โรงเรียน', value: this.formatNumber(this.preview.summary.unmatched_rows) },
                        ];
                    },
                    selectedDataSetSummary() {
                        return this.dataSets.find(item => String(item.academic_year) === String(this.form.academic_year)) || null;
                    },
                    formatNumber(value) { return Number(value || 0).toLocaleString('th-TH'); },
                    formatScore(value) {
                        const number = Number(value || 0);
                        return number.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    formatDateTime(value) {
                        if (!value) return '-';
                        const date = new Date(value);
                        return Number.isNaN(date.getTime()) ? value : date.toLocaleString('th-TH');
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
