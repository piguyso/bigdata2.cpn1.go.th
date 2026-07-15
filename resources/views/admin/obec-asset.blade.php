<x-layout>
    <x-slot:title>นำเข้าข้อมูลจาก OBEC Asset | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="obecAssetImportManager()" x-init="init()">
        <div x-show="toast.show" x-transition class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold" :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'" x-text="toast.message" x-cloak></div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">OBEC Asset Snapshot</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้าข้อมูลจาก OBEC Asset</h2>
                <p class="text-slate-500 text-sm mt-1">ดึง logo โรงเรียน รายชื่อโรงเรียน และข้อมูลสิ่งก่อสร้างจากระบบ OBEC Asset มาเก็บไว้ในฐานข้อมูล local</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2"><i class="fa-solid fa-house"></i> กลับหน้าหลัก</a>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">เขตพื้นที่</p><p class="text-lg font-extrabold text-slate-900 mt-2" x-text="currentRemote.area_name || '-'"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปี/รอบที่เลือก</p><p class="text-lg font-extrabold text-slate-900 mt-2" x-text="form.academic_year ? ('ปี ' + form.academic_year + ' รอบ ' + form.term) : '-'"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ข้อมูลอาคารใน local</p><p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="formatNumber(recordCount)"></p></div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ชุดล่าสุด</p><p class="text-sm font-extrabold text-slate-900 mt-2" x-text="latestImportedLabel()"></p></div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] gap-6 items-start">
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-cloud-arrow-down text-orange-500"></i> ตรวจสอบและดึงข้อมูลจาก OBEC Asset</h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">ปีการศึกษา</span>
                            <select x-model="form.academic_year" class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold outline-none focus:border-orange-500">
                                <template x-for="year in years" :key="year.year">
                                    <option :value="year.year" x-text="year.year"></option>
                                </template>
                            </select>
                        </label>
                        <label class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">รอบ</span>
                            <select x-model="form.term" class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold outline-none focus:border-orange-500">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </label>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">AreaID</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800" x-text="currentRemote.area_code || '-'"></div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="previewImport()" :disabled="previewLoading" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2"><i x-show="previewLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!previewLoading" class="fa-solid fa-magnifying-glass-chart"></i><span x-text="previewLoading ? 'กำลังตรวจสอบ...' : 'ตรวจสอบก่อนนำเข้า'"></span></button>
                        <button type="button" @click="runImport()" :disabled="importLoading || !preview.summary" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100 inline-flex items-center justify-center gap-2"><i x-show="importLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!importLoading" class="fa-solid fa-database"></i><span x-text="importLoading ? 'กำลังนำเข้า...' : 'ยืนยันนำเข้าข้อมูล'"></span></button>
                        <button type="button" @click="openDeleteModal()" :disabled="deleteLoading || !latestImported" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2"><i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i><i x-show="!deleteLoading" class="fa-solid fa-trash-can"></i><span x-text="deleteLoading ? 'กำลังลบข้อมูล...' : 'ลบชุดข้อมูลล่าสุด'"></span></button>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-slate-500"><span class="font-bold text-slate-700">สถานะ:</span> <span x-text="latestImported ? 'มีข้อมูลใน local DB แล้ว การนำเข้าใหม่จะแทนที่ชุดเดิม' : 'ยังไม่มีข้อมูลใน local DB'"></span></div>
                        <div class="text-slate-400 font-bold" x-show="latestImported" x-cloak><span x-text="latestImportedLabel()"></span></div>
                    </div>

                    <div x-show="preview.summary" class="space-y-4" x-cloak>
                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                            <template x-for="item in summaryCards()" :key="item.label"><div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.label"></p><p class="text-xl font-extrabold text-slate-900 mt-1" x-text="formatNumber(item.value)"></p></div></template>
                        </div>

                        <div class="border border-slate-100 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100"><h4 class="font-bold text-slate-700 text-xs">ตัวอย่างโรงเรียนที่อ่านได้</h4></div>
                            <div class="max-h-72 overflow-auto divide-y divide-slate-100">
                                <template x-for="school in preview.schools_sample || []" :key="school.school_smis">
                                    <div class="px-4 py-3 flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-extrabold text-slate-700" x-text="school.school_name"></div>
                                            <div class="text-[11px] text-slate-400" x-text="school.school_smis"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-bold text-slate-400" x-text="formatNumber(school.logo_bytes) + ' bytes'"></span>
                                            <span class="px-2 py-1 rounded-md text-[10px] font-bold" :class="school.matched ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'" x-text="school.matched ? 'matched' : 'unmatched'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="preview.summary.detail_fetch_note" class="border border-sky-100 bg-sky-50 rounded-2xl p-4 text-xs font-bold text-sky-700" x-text="preview.summary.detail_fetch_note"></div>

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
                    <template x-for="item in imports" :key="item.id"><div class="p-5 space-y-3"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-extrabold text-slate-800" x-text="'ชุดนำเข้า #' + item.id"></p><p class="text-[11px] text-slate-400 mt-1" x-text="item.area_name"></p></div><span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700" x-text="item.mode"></span></div><div class="grid grid-cols-3 gap-2 text-center"><div class="rounded-xl bg-slate-50 px-2 py-2"><div class="text-[10px] text-slate-400 font-bold">โรงเรียน</div><div class="text-xs font-extrabold text-slate-700" x-text="formatNumber(item.school_rows_count)"></div></div><div class="rounded-xl bg-slate-50 px-2 py-2"><div class="text-[10px] text-slate-400 font-bold">logo</div><div class="text-xs font-extrabold text-slate-700" x-text="formatNumber(item.school_logos_count)"></div></div><div class="rounded-xl bg-slate-50 px-2 py-2"><div class="text-[10px] text-slate-400 font-bold">อาคาร</div><div class="text-xs font-extrabold text-slate-700" x-text="formatNumber(item.building_records_count)"></div></div></div><div class="flex items-center justify-between gap-3 text-[11px]"><span class="text-slate-400" x-text="item.created_by_name || 'system'"></span><span class="text-slate-400" x-text="formatDateTime(item.created_at)"></span></div></div></template>
                </div>
            </aside>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูล OBEC Asset</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบชุดข้อมูลล่าสุด <span class="font-bold text-slate-700" x-text="'#' + deleteModal.importId"></span> หรือไม่?</p>
                <div class="flex gap-2.5"><button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button><button type="button" @click="deleteDataSet()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function obecAssetImportManager() {
                return {
                    currentRemote: {},
                    imports: [],
                    dataSets: [],
                    years: [],
                    recordCount: 0,
                    latestImported: null,
                    form: { academic_year: '', term: '1' },
                    previewLoading: false,
                    importLoading: false,
                    deleteLoading: false,
                    preview: { summary: null, schools_sample: [] },
                    deleteModal: { open: false, importId: null },
                    toast: { show: false, message: '', type: 'success' },
                    init() { this.fetchData(); },
                    fetchData() {
                        axios.get('{{ route('admin.obec-asset.data') }}').then(response => {
                            if (response.data.status === 'success') {
                                this.currentRemote = response.data.current_remote || {};
                                this.years = response.data.years || [];
                                this.form.academic_year = this.form.academic_year || response.data.active_year || '';
                                this.imports = response.data.imports || [];
                                this.dataSets = response.data.data_sets || [];
                                this.recordCount = response.data.record_count || 0;
                                this.latestImported = response.data.latest_imported || null;
                            }
                        }).catch(() => this.showToast('ไม่สามารถโหลดข้อมูล OBEC Asset ได้', 'error'));
                    },
                    previewImport() {
                        this.previewLoading = true;
                        axios.post('{{ route('admin.obec-asset.preview') }}', this.form)
                            .then(response => { this.preview = response.data.preview || { summary: null, schools_sample: [] }; this.showToast(response.data.message || 'ตรวจสอบเรียบร้อยแล้ว', 'success'); })
                            .catch(error => this.showToast(error.response?.data?.message || 'ไม่สามารถตรวจสอบข้อมูล OBEC Asset ได้', 'error'))
                            .finally(() => this.previewLoading = false);
                    },
                    runImport() {
                        if (!this.preview.summary) return this.showToast('กรุณาตรวจสอบข้อมูลก่อนนำเข้า', 'error');
                        this.importLoading = true;
                        axios.post('{{ route('admin.obec-asset.import') }}', { ...this.form, mode: 'replace' })
                            .then(response => { this.showToast(response.data.message || 'นำเข้าข้อมูลเรียบร้อยแล้ว', 'success'); this.preview = { summary: null, schools_sample: [] }; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล OBEC Asset', 'error'))
                            .finally(() => this.importLoading = false);
                    },
                    openDeleteModal() {
                        if (!this.latestImported) return this.showToast('ยังไม่มีข้อมูลสำหรับลบ', 'error');
                        this.deleteModal = { open: true, importId: this.latestImported.id };
                    },
                    deleteDataSet() {
                        this.deleteLoading = true;
                        axios.delete('{{ route('admin.obec-asset.delete') }}', { data: { import_id: this.deleteModal.importId } })
                            .then(response => { this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', 'success'); this.deleteModal.open = false; this.preview = { summary: null, schools_sample: [] }; this.fetchData(); })
                            .catch(error => this.showToast(error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล OBEC Asset', 'error'))
                            .finally(() => this.deleteLoading = false);
                    },
                    latestImportedLabel() {
                        return this.latestImported ? ('ปี ' + (this.latestImported.academic_year || '-') + ' รอบ ' + (this.latestImported.term || '-') + ' #' + this.latestImported.id) : 'ยังไม่มีข้อมูล';
                    },
                    summaryCards() {
                        const summary = this.preview.summary || {};
                        return [
                            { label: 'โรงเรียน', value: summary.school_rows_count || 0 },
                            { label: 'logo', value: summary.school_logos_count || 0 },
                            { label: 'จับคู่ได้', value: summary.matched_schools_count || 0 },
                            { label: 'จับคู่ไม่ได้', value: summary.unmatched_schools_count || 0 },
                            { label: 'สิ่งก่อสร้าง', value: summary.building_records_count || 0 },
                        ];
                    },
                    previewWarnings() { return this.preview.warnings || []; },
                    formatNumber(value) { return Number(value || 0).toLocaleString('th-TH'); },
                    formatDateTime(value) { if (!value) return '-'; const date = new Date(value); return Number.isNaN(date.getTime()) ? value : date.toLocaleString('th-TH'); },
                    showToast(message, type = 'success') { this.toast = { show: true, message, type }; setTimeout(() => this.toast.show = false, 3500); }
                };
            }
        </script>
    @endpush
</x-layout>
