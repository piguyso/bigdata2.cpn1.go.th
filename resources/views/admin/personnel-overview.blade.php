<x-layout>
    <x-slot:title>นำเข้าข้อมูลภาพรวมบุคลากร | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="personnelOverviewImportManager()" x-init="init()">
        <div x-show="toast.show" x-transition class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold" :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'" x-text="toast.message" x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">HRMS Personnel Snapshot</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้าข้อมูลภาพรวมบุคลากร</h2>
                <p class="text-slate-500 text-sm mt-1">ดึงข้อมูล report_01 ถึง report_10, workload และข้อมูลเขตจาก HRMS มาเก็บเป็น snapshot ในฐานข้อมูล local</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('personnel.dashboard') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2"><i class="fa-solid fa-users"></i> ดูหน้าข้อมูลบุคลากร</a>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีจาก HRMS</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="currentRemote.academic_year || '-'"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">รอบจาก HRMS</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="currentRemote.term || '-'"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">แหล่งข้อมูลที่เก็บแล้ว</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="recordCount"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ชุดล่าสุด</p><p class="text-sm font-extrabold text-slate-900 mt-2" x-text="latestImportedLabel()"></p></div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] gap-6 items-start">
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-cloud-arrow-down text-orange-500"></i> ตรวจสอบและดึงข้อมูลจาก HRMS</h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">ส่วนราชการ</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800" x-text="currentRemote.area_name || '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">ปีการศึกษา</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800" x-text="currentRemote.academic_year || '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">รอบ</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800" x-text="currentRemote.term || '-'"></div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="previewImport()" :disabled="previewLoading" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2"><i x-show="previewLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!previewLoading" class="fa-solid fa-magnifying-glass-chart"></i><span x-text="previewLoading ? 'กำลังตรวจสอบ...' : 'ตรวจสอบก่อนนำเข้า'"></span></button>
                        <button type="button" @click="runImport()" :disabled="importLoading || !preview.summary" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100 inline-flex items-center justify-center gap-2"><i x-show="importLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!importLoading" class="fa-solid fa-database"></i><span x-text="importLoading ? 'กำลังนำเข้า...' : 'ยืนยันนำเข้าข้อมูล'"></span></button>
                        <button type="button" @click="openDeleteModal()" :disabled="deleteLoading || !selectedDataSetSummary()" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2"><i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!deleteLoading" class="fa-solid fa-trash-can"></i><span x-text="deleteLoading ? 'กำลังลบข้อมูล...' : 'ลบข้อมูลปี/รอบนี้'"></span></button>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-slate-500"><span class="font-bold text-slate-700">สถานะชุดข้อมูลปัจจุบัน:</span> <span x-text="selectedDataSetSummary() ? 'มีข้อมูลพร้อมแทนที่/ลบ' : 'ยังไม่มีข้อมูลใน local DB'"></span></div>
                        <div class="text-slate-400 font-bold" x-show="selectedDataSetSummary()" x-cloak><span x-text="dataSetBadge(selectedDataSetSummary())"></span></div>
                    </div>

                    <div x-show="preview.summary" class="space-y-4" x-cloak>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <template x-for="item in summaryCards()" :key="item.label"><div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.label"></p><p class="text-xl font-extrabold text-slate-900 mt-1" x-text="formatNumber(item.value)"></p></div></template>
                        </div>

                        <div class="border border-slate-100 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100"><h4 class="font-bold text-slate-700 text-xs">สรุปบุคลากรตามประเภท</h4></div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="item in detailedCards()" :key="item.label">
                                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase" x-text="item.label"></div>
                                        <div class="mt-1 text-lg font-extrabold text-slate-800" x-text="formatNumber(item.value)"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] gap-4">
                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100"><h4 class="font-bold text-slate-700 text-xs">ข้อมูลที่ normalize ได้</h4></div>
                                <div class="p-4 grid grid-cols-2 gap-3">
                                    <template x-for="item in normalizedCards()" :key="item.label">
                                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase" x-text="item.label"></div>
                                            <div class="mt-1 text-lg font-extrabold text-slate-800" x-text="formatNumber(item.value)"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100"><h4 class="font-bold text-slate-700 text-xs">จำนวนข้อมูลจากแต่ละ endpoint</h4></div>
                                <div class="max-h-72 overflow-auto divide-y divide-slate-100">
                                    <template x-for="source in previewSources()" :key="source.source_key">
                                        <div class="px-4 py-3 flex items-center justify-between gap-3">
                                            <span class="text-xs font-extrabold text-slate-700" x-text="source.source_key"></span>
                                            <span class="text-xs font-bold text-slate-500" x-text="formatNumber(source.records_count) + ' records'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-show="previewWarnings().length" class="border border-amber-100 bg-amber-50 rounded-2xl p-4" x-cloak>
                            <h4 class="font-bold text-amber-800 text-xs mb-2">รายการที่จับคู่โรงเรียน local ไม่ได้</h4>
                            <ul class="space-y-1 max-h-40 overflow-auto">
                                <template x-for="warning in previewWarnings()" :key="warning">
                                    <li class="text-[11px] text-amber-700" x-text="warning"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70"><h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-orange-500"></i> ประวัติการนำเข้า</h3></div>
                <div class="divide-y divide-slate-100">
                    <template x-if="imports.length === 0"><div class="p-8 text-center text-slate-400 text-xs font-medium">ยังไม่มีประวัติการนำเข้าข้อมูล</div></template>
                    <template x-for="item in imports" :key="item.id"><div class="p-5 space-y-3"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-extrabold text-slate-800" x-text="'ปี ' + item.academic_year + ' รอบ ' + item.term"></p><p class="text-[11px] text-slate-400 mt-1" x-text="item.area_name"></p></div><span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700" x-text="item.mode"></span></div><div class="flex items-center justify-between gap-3 text-[11px]"><span class="text-slate-400" x-text="item.created_by_name || 'system'"></span><span class="text-slate-400" x-text="formatDateTime(item.created_at)"></span></div></div></template>
                </div>
            </aside>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูลภาพรวมบุคลากร</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบข้อมูลของ <span class="font-bold text-slate-700" x-text="'ปี ' + deleteModal.academicYear + ' รอบ ' + deleteModal.term"></span> หรือไม่?</p>
                <div class="flex gap-2.5"><button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button><button type="button" @click="deleteDataSet()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function personnelOverviewImportManager() {
                return {
                    currentRemote: {},
                    imports: [],
                    dataSets: [],
                    recordCount: 0,
                    latestImported: null,
                    previewLoading: false,
                    importLoading: false,
                    deleteLoading: false,
                    preview: { summary: null },
                    deleteModal: { open: false, academicYear: '', term: '' },
                    toast: { show: false, message: '', type: 'success' },
                    init() { this.fetchData(); },
                    fetchData() {
                        axios.get('{{ route('admin.personnel-overview.data') }}').then(response => {
                            if (response.data.status === 'success') {
                                this.currentRemote = response.data.current_remote || {};
                                this.imports = response.data.imports || [];
                                this.dataSets = response.data.data_sets || [];
                                this.recordCount = response.data.record_count || 0;
                                this.latestImported = response.data.latest_imported || null;
                            }
                        }).catch(() => this.showToast('ไม่สามารถโหลดข้อมูลภาพรวมบุคลากรได้', 'error'));
                    },
                    previewImport() {
                        this.previewLoading = true;
                        axios.post('{{ route('admin.personnel-overview.preview') }}')
                            .then(response => { this.preview.summary = response.data.preview; this.showToast(response.data.message || 'ตรวจสอบเรียบร้อยแล้ว', 'success'); })
                            .catch(error => this.showToast(error.response?.data?.message || 'ไม่สามารถตรวจสอบข้อมูลภาพรวมบุคลากรได้', 'error'))
                            .finally(() => this.previewLoading = false);
                    },
                    runImport() {
                        if (!this.preview.summary) return this.showToast('กรุณาตรวจสอบข้อมูลก่อนนำเข้า', 'error');
                        this.importLoading = true;
                        axios.post('{{ route('admin.personnel-overview.import') }}', { mode: 'replace' })
                            .then(response => { this.showToast(response.data.message || 'นำเข้าข้อมูลเรียบร้อยแล้ว', 'success'); this.preview.summary = null; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการนำเข้าข้อมูลภาพรวมบุคลากร', 'error'))
                            .finally(() => this.importLoading = false);
                    },
                    openDeleteModal() {
                        const dataSet = this.selectedDataSetSummary();
                        if (!dataSet) return this.showToast('ยังไม่มีข้อมูลของปี/รอบปัจจุบันสำหรับลบ', 'error');
                        this.deleteModal = { open: true, academicYear: dataSet.academic_year, term: dataSet.term };
                    },
                    deleteDataSet() {
                        this.deleteLoading = true;
                        axios.delete('{{ route('admin.personnel-overview.delete') }}', { data: { academic_year: this.deleteModal.academicYear, term: this.deleteModal.term } })
                            .then(response => { this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', 'success'); this.deleteModal.open = false; this.preview.summary = null; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูลภาพรวมบุคลากร', 'error'))
                            .finally(() => this.deleteLoading = false);
                    },
                    selectedDataSetSummary() {
                        return this.dataSets.find(item => String(item.academic_year) === String(this.currentRemote.academic_year) && String(item.term) === String(this.currentRemote.term)) || null;
                    },
                    latestImportedLabel() {
                        return this.latestImported ? ('ปี ' + this.latestImported.academic_year + ' รอบ ' + this.latestImported.term) : 'ยังไม่มีข้อมูล';
                    },
                    summaryCards() {
                        if (!this.preview.summary?.summary) return [];
                        return [
                            { label: 'บุคลากรรวม', value: this.preview.summary.summary.total_personnel },
                            { label: 'ผู้อำนวยการ', value: this.preview.summary.summary.director_total },
                            { label: 'รองผู้อำนวยการ', value: this.preview.summary.summary.vice_director_total },
                            { label: 'ครู', value: this.preview.summary.summary.teacher_total },
                        ];
                    },
                    detailedCards() {
                        if (!this.preview.summary?.summary) return [];
                        return [
                            { label: 'ข้าราชการ', value: this.preview.summary.summary.government_officer_total },
                            { label: 'บุคลากร คศ.38 ค.', value: this.preview.summary.summary.civil_service_staff_total },
                            { label: 'พนักงานราชการ', value: this.preview.summary.summary.government_employee_total },
                            { label: 'ลูกจ้างชั่วคราว/จ้างเหมา', value: this.preview.summary.summary.temporary_employee_total },
                            { label: 'ลูกจ้างประจำ', value: this.preview.summary.summary.permanent_employee_total },
                        ];
                    },
                    normalizedCards() {
                        const normalized = this.preview.summary?.normalized || {};
                        return [
                            { label: 'report records', value: normalized.report_records_count || 0 },
                            { label: 'workload schools', value: normalized.workload_schools_count || 0 },
                            { label: 'จับคู่โรงเรียนได้', value: normalized.matched_schools_count || 0 },
                            { label: 'จับคู่ไม่ได้', value: normalized.unmatched_schools_count || 0 },
                        ];
                    },
                    previewSources() {
                        return this.preview.summary?.sources || [];
                    },
                    previewWarnings() {
                        return this.preview.summary?.warnings || [];
                    },
                    dataSetBadge(dataSet) {
                        if (!dataSet) return '';
                        if (dataSet.sources_count !== undefined) {
                            return 'sources ' + this.formatNumber(dataSet.sources_count) + ' / จับคู่ได้ ' + this.formatNumber(dataSet.matched_schools_count);
                        }
                        return 'บุคลากรรวม ' + this.formatNumber(dataSet.total_personnel);
                    },
                    formatNumber(value) { return Number(value || 0).toLocaleString('th-TH'); },
                    formatDateTime(value) { if (!value) return '-'; const date = new Date(value); return Number.isNaN(date.getTime()) ? value : date.toLocaleString('th-TH'); },
                    showToast(message, type = 'success') { this.toast = { show: true, message, type }; setTimeout(() => this.toast.show = false, 3500); }
                };
            }
        </script>
    @endpush
</x-layout>
