<x-layout>
    <x-slot:title>นำเข้าข้อมูล ONET | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="onetImportManager()" x-init="init()">
        <div x-show="toast.show" x-transition class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold" :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'" x-text="toast.message" x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">ONET Import</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้าข้อมูล ONET</h2>
                <p class="text-slate-500 text-sm mt-1">ดึงข้อมูลจาก API ของ HRMS มาเก็บไว้ในฐานข้อมูล local ก่อน แล้วค่อยนำไปแสดงผลในหน้า ONET</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.academic-years.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2"><i class="fa-solid fa-calendar-days"></i> จัดการปีการศึกษา</a>
                <a href="{{ route('onet.dashboard') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2"><i class="fa-solid fa-chart-line"></i> ดูหน้า ONET</a>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีปัจจุบัน</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="activeYear || '-'"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">จำนวนปีในระบบ</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="years.length"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ข้อมูลที่นำเข้าแล้ว</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="recordCount"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ชุดล่าสุด</p><p class="text-sm font-extrabold text-slate-900 mt-2" x-text="latestImportedYear ? 'ปี ' + latestImportedYear : 'ยังไม่มีข้อมูล'"></p></div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] gap-6 items-start">
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-cloud-arrow-down text-orange-500"></i> ตรวจสอบและดึงข้อมูลจาก API</h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">ปีการศึกษา *</span>
                            <select x-model="form.academic_year" class="form-input">
                                <option value="">เลือกปีการศึกษา</option>
                                <template x-for="year in years" :key="year.id"><option :value="year.year" x-text="year.year + ' - ' + year.name"></option></template>
                            </select>
                        </label>
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">โหมดนำเข้า</span>
                            <select x-model="form.mode" class="form-input"><option value="replace">แทนที่ข้อมูลปีนี้ทั้งหมด</option></select>
                        </label>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="previewImport()" :disabled="previewLoading" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2"><i x-show="previewLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!previewLoading" class="fa-solid fa-magnifying-glass-chart"></i><span x-text="previewLoading ? 'กำลังตรวจสอบ...' : 'ตรวจสอบก่อนนำเข้า'"></span></button>
                        <button type="button" @click="runImport()" :disabled="importLoading || !preview.summary" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100 inline-flex items-center justify-center gap-2"><i x-show="importLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!importLoading" class="fa-solid fa-database"></i><span x-text="importLoading ? 'กำลังนำเข้า...' : 'ยืนยันนำเข้าข้อมูล'"></span></button>
                        <button type="button" @click="openDeleteModal()" :disabled="deleteLoading || !form.academic_year" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2"><i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!deleteLoading" class="fa-solid fa-trash-can"></i><span x-text="deleteLoading ? 'กำลังลบข้อมูล...' : 'ลบข้อมูลปีนี้'"></span></button>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-slate-500"><span class="font-bold text-slate-700">สถานะชุดข้อมูลที่เลือก:</span> <span x-text="selectedDataSetSummary() ? 'มีข้อมูลพร้อมลบ/แทนที่' : 'ยังไม่มีข้อมูลที่นำเข้า'"></span></div>
                        <div class="text-slate-400 font-bold" x-show="selectedDataSetSummary()" x-cloak><span x-text="'โรงเรียน ' + formatNumber(selectedDataSetSummary().schools_count)"></span><span class="mx-1.5">•</span><span x-text="'ระเบียน ' + formatNumber(selectedDataSetSummary().records_count)"></span></div>
                    </div>

                    <div x-show="preview.summary" class="space-y-4" x-cloak>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <template x-for="item in summaryCards()" :key="item.label"><div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.label"></p><p class="text-xl font-extrabold text-slate-900 mt-1" x-text="item.value"></p></div></template>
                        </div>

                        <div class="border border-slate-100 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100"><h4 class="font-bold text-slate-700 text-xs">ระดับชั้นที่ระบบจะดึงข้อมูล</h4></div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <template x-for="grade in preview.summary.grades" :key="grade.grade_code"><div class="rounded-2xl border border-slate-100 px-4 py-3"><div class="text-[10px] font-bold text-slate-400 uppercase" x-text="grade.grade_label"></div><div class="mt-1 text-lg font-extrabold text-slate-800" x-text="formatNumber(grade.schools_count)"></div><div class="text-[11px] text-slate-500 mt-1">โรงเรียนที่มีสิทธิ์นำเข้า</div></div></template>
                            </div>
                        </div>

                        <div x-show="preview.summary.sample_unmatched?.length" class="bg-amber-50 border border-amber-200 rounded-2xl p-4" x-cloak>
                            <p class="text-xs font-extrabold text-amber-700 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> โรงเรียนที่ยังไม่ match กับฐานข้อมูล local</p>
                            <div class="mt-3 space-y-2 text-xs text-amber-800">
                                <template x-for="row in preview.summary.sample_unmatched" :key="row.smis + row.school_name"><div><span class="font-bold" x-text="row.smis || '-'"></span><span x-text="' ' + row.school_name + ' (' + row.max_class_level + ')'"></span></div></template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70"><h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-orange-500"></i> ประวัติการนำเข้า</h3></div>
                <div class="divide-y divide-slate-100">
                    <template x-if="imports.length === 0"><div class="p-8 text-center text-slate-400 text-xs font-medium">ยังไม่มีประวัติการนำเข้าข้อมูล</div></template>
                    <template x-for="item in imports" :key="item.id"><div class="p-5 space-y-3"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-extrabold text-slate-800" x-text="'ปี ' + item.academic_year"></p><p class="text-[11px] text-slate-400 mt-1" x-text="'โรงเรียน ' + formatNumber(item.schools_count) + ' • ระเบียน ' + formatNumber(item.records_count)"></p></div><span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700" x-text="item.mode"></span></div><div class="flex items-center justify-between gap-3 text-[11px]"><span class="text-slate-400" x-text="item.created_by_name || 'system'"></span><span class="text-slate-400" x-text="formatDateTime(item.created_at)"></span></div></div></template>
                </div>
            </aside>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูล ONET</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบข้อมูลของ <span class="font-bold text-slate-700" x-text="'ปีการศึกษา ' + deleteModal.academicYear"></span> ทั้งหมดหรือไม่?</p>
                <div class="flex gap-2.5"><button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button><button type="button" @click="deleteDataSet()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .form-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #f8fafc; padding: 0.625rem 1rem; font-size: 0.75rem; outline: none; transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; }
            .form-input:focus { background: #fff; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15); }
        </style>
        <script>
            function onetImportManager() {
                return {
                    years: [], imports: [], dataSets: [], activeYear: '', latestImportedYear: '', recordCount: 0,
                    previewLoading: false, importLoading: false, deleteLoading: false,
                    form: { academic_year: '', mode: 'replace' }, preview: { summary: null }, deleteModal: { open: false, academicYear: '' },
                    toast: { show: false, message: '', type: 'success' },
                    init() { this.fetchData(); },
                    fetchData() {
                        axios.get('{{ route('admin.onet.data') }}').then(response => {
                            if (response.data.status === 'success') {
                                this.years = response.data.years || [];
                                this.imports = response.data.imports || [];
                                this.dataSets = response.data.data_sets || [];
                                this.activeYear = response.data.active_year || '';
                                this.latestImportedYear = response.data.latest_imported_year || '';
                                this.recordCount = response.data.record_count || 0;
                                if (!this.form.academic_year) this.form.academic_year = this.activeYear || (this.years[0]?.year || '');
                            }
                        }).catch(() => this.showToast('ไม่สามารถโหลดข้อมูล ONET ได้', 'error'));
                    },
                    previewImport() {
                        if (!this.form.academic_year) return this.showToast('กรุณาเลือกปีการศึกษา', 'error');
                        this.previewLoading = true;
                        axios.post('{{ route('admin.onet.preview') }}', { academic_year: this.form.academic_year })
                            .then(response => { this.preview.summary = response.data.preview; this.showToast(response.data.message || 'ตรวจสอบเรียบร้อยแล้ว', 'success'); })
                            .catch(error => this.showToast(error.response?.data?.message || 'ไม่สามารถตรวจสอบข้อมูล ONET ได้', 'error'))
                            .finally(() => this.previewLoading = false);
                    },
                    runImport() {
                        if (!this.preview.summary) return this.showToast('กรุณาตรวจสอบข้อมูลก่อนนำเข้า', 'error');
                        this.importLoading = true;
                        axios.post('{{ route('admin.onet.import') }}', { academic_year: this.form.academic_year, mode: this.form.mode })
                            .then(response => { this.showToast(response.data.message || 'นำเข้าข้อมูลเรียบร้อยแล้ว', 'success'); this.preview.summary = null; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล ONET', 'error'))
                            .finally(() => this.importLoading = false);
                    },
                    openDeleteModal() {
                        if (!this.form.academic_year) return this.showToast('กรุณาเลือกปีการศึกษาก่อน', 'error');
                        if (!this.selectedDataSetSummary()) return this.showToast('ยังไม่มีข้อมูลของปีที่เลือกสำหรับลบ', 'error');
                        this.deleteModal = { open: true, academicYear: this.form.academic_year };
                    },
                    deleteDataSet() {
                        this.deleteLoading = true;
                        axios.delete('{{ route('admin.onet.delete') }}', { data: { academic_year: this.deleteModal.academicYear } })
                            .then(response => { this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', 'success'); this.deleteModal.open = false; this.preview.summary = null; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล ONET', 'error'))
                            .finally(() => this.deleteLoading = false);
                    },
                    summaryCards() {
                        if (!this.preview.summary) return [];
                        return [
                            { label: 'โรงเรียนจาก API', value: this.preview.summary.schools_count },
                            { label: 'match กับ local', value: this.preview.summary.matched_schools_count },
                            { label: 'ยังไม่ match', value: this.preview.summary.unmatched_schools_count },
                            { label: 'ปีการศึกษา', value: this.preview.summary.academic_year },
                        ];
                    },
                    selectedDataSetSummary() { return this.dataSets.find(item => String(item.academic_year) === String(this.form.academic_year)) || null; },
                    formatNumber(value) { return Number(value || 0).toLocaleString('th-TH'); },
                    formatDateTime(value) { if (!value) return '-'; const date = new Date(value); return Number.isNaN(date.getTime()) ? value : date.toLocaleString('th-TH'); },
                    showToast(message, type = 'success') { this.toast = { show: true, message, type }; setTimeout(() => this.toast.show = false, 3500); }
                };
            }
        </script>
    @endpush
</x-layout>
