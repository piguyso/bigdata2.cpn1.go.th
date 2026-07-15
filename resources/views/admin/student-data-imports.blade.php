<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">นำเข้าข้อมูล BIGDATA นักเรียน</h2>
                <p class="text-sm text-slate-500 mt-1">นำเข้า CSV/XLSX หน้าเดียว โดยเลือกปีการศึกษา รอบ และชนิดข้อมูล</p>
            </div>
            <a :href="templateUrl()" x-data="studentDataImportPage()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700">
                Download Template
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="studentDataImportPage()" x-init="load()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">ปีการศึกษา</span>
                        <select x-model="form.academic_year" class="w-full rounded-lg border-slate-300">
                            <template x-for="year in years" :key="year.id || year.year">
                                <option :value="year.year" x-text="year.year"></option>
                            </template>
                        </select>
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">รอบ</span>
                        <select x-model="form.term" class="w-full rounded-lg border-slate-300">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </label>
                    <label class="space-y-1 md:col-span-2">
                        <span class="text-xs font-bold text-slate-500">ชนิดข้อมูล</span>
                        <select x-model="form.data_type" class="w-full rounded-lg border-slate-300">
                            <template x-for="(type, key) in dataTypes" :key="key">
                                <option :value="key" x-text="type.label"></option>
                            </template>
                        </select>
                    </label>
                    <div class="flex items-end">
                        <a :href="templateUrl()" class="w-full text-center px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700">
                            Download Template
                        </a>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-3 items-end">
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">ไฟล์ CSV/XLSX</span>
                        <input type="file" accept=".csv,.txt,.xlsx" @change="pickFile($event)" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <button type="button" @click="preview()" :disabled="loading || !file" class="px-5 py-2.5 rounded-lg bg-sky-600 text-white text-sm font-bold disabled:opacity-50">
                        ตรวจสอบไฟล์
                    </button>
                    <button type="button" @click="importFile()" :disabled="loading || !previewData.upload_token" class="px-5 py-2.5 rounded-lg bg-orange-600 text-white text-sm font-bold disabled:opacity-50">
                        Import
                    </button>
                </div>

                <div x-show="message" class="mt-4 rounded-lg px-4 py-3 text-sm font-bold" :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'" x-text="message"></div>
            </div>

            <div x-show="previewData.preview" class="bg-white border border-slate-200 rounded-lg p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <template x-for="card in previewCards()" :key="card.label">
                        <div class="border border-slate-200 rounded-lg p-4">
                            <div class="text-xs font-bold text-slate-500" x-text="card.label"></div>
                            <div class="text-2xl font-extrabold text-slate-800 mt-1" x-text="formatNumber(card.value)"></div>
                        </div>
                    </template>
                </div>
                <div x-show="previewData.preview?.warnings?.length" class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                    <template x-for="warning in previewData.preview.warnings" :key="warning">
                        <div x-text="warning"></div>
                    </template>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">SMIS</th>
                                <th class="px-4 py-2 text-left">โรงเรียน</th>
                                <th class="px-4 py-2 text-left">หมวด</th>
                                <th class="px-4 py-2 text-right">รวม</th>
                                <th class="px-4 py-2 text-center">จับคู่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in previewData.preview?.sample_rows || []" :key="row.school_smis + row.category">
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-2" x-text="row.school_smis"></td>
                                    <td class="px-4 py-2" x-text="row.school_name || '-'"></td>
                                    <td class="px-4 py-2" x-text="row.category || '-'"></td>
                                    <td class="px-4 py-2 text-right font-bold" x-text="formatNumber(row.total)"></td>
                                    <td class="px-4 py-2 text-center" x-text="row.matched_school ? 'พบ' : 'ไม่พบ'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <h3 class="font-bold text-slate-800 mb-4">ชุดข้อมูลที่นำเข้าแล้ว</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">ปี/รอบ</th>
                                <th class="px-4 py-2 text-left">ชนิดข้อมูล</th>
                                <th class="px-4 py-2 text-right">รายการ</th>
                                <th class="px-4 py-2 text-right">รวม</th>
                                <th class="px-4 py-2 text-left">ล่าสุด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="set in dataSets" :key="set.academic_year + set.term + set.data_type">
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-2 font-bold" x-text="set.academic_year + '/' + set.term"></td>
                                    <td class="px-4 py-2" x-text="set.data_label"></td>
                                    <td class="px-4 py-2 text-right" x-text="formatNumber(set.records_count)"></td>
                                    <td class="px-4 py-2 text-right font-bold" x-text="formatNumber(set.total_count)"></td>
                                    <td class="px-4 py-2 text-slate-500" x-text="set.latest_updated_at || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function studentDataImportPage() {
            return {
                years: [],
                dataTypes: @json($dataTypes),
                dataSets: [],
                file: null,
                loading: false,
                message: '',
                messageType: 'success',
                previewData: {},
                form: { academic_year: '2569', term: '1', data_type: 'class_gender' },
                load() {
                    axios.get('{{ route('admin.student-data-imports.data') }}').then(({ data }) => {
                        this.years = data.years || [];
                        this.dataSets = data.data_sets || [];
                        this.dataTypes = data.data_types || this.dataTypes;
                        this.form.academic_year = data.active_year || this.form.academic_year;
                    });
                },
                templateUrl() {
                    return '{{ url('/admin/student-data-imports/template') }}/' + this.form.data_type;
                },
                pickFile(event) {
                    this.file = event.target.files[0] || null;
                    this.previewData = {};
                    this.message = '';
                },
                preview() {
                    const payload = new FormData();
                    payload.append('academic_year', this.form.academic_year);
                    payload.append('term', this.form.term);
                    payload.append('data_type', this.form.data_type);
                    payload.append('file', this.file);
                    this.loading = true;
                    axios.post('{{ route('admin.student-data-imports.preview') }}', payload).then(({ data }) => {
                        this.previewData = data;
                        this.messageType = 'success';
                        this.message = data.message;
                    }).catch((error) => this.showError(error)).finally(() => this.loading = false);
                },
                importFile() {
                    this.loading = true;
                    axios.post('{{ route('admin.student-data-imports.import') }}', {
                        academic_year: this.form.academic_year,
                        term: this.form.term,
                        data_type: this.form.data_type,
                        upload_token: this.previewData.upload_token,
                        source_filename: this.previewData.source_filename,
                    }).then(({ data }) => {
                        this.messageType = 'success';
                        this.message = data.message + ' (' + this.formatNumber(data.imported_rows) + ' รายการ)';
                        this.previewData = {};
                        this.load();
                    }).catch((error) => this.showError(error)).finally(() => this.loading = false);
                },
                previewCards() {
                    const p = this.previewData.preview || {};
                    return [
                        { label: 'แถวทั้งหมด', value: p.total_rows || 0 },
                        { label: 'อ่านได้', value: p.valid_rows || 0 },
                        { label: 'จับคู่ไม่ได้', value: p.unmatched_rows || 0 },
                        { label: 'ผิดโครงสร้าง', value: p.invalid_rows || 0 },
                    ];
                },
                showError(error) {
                    this.messageType = 'error';
                    this.message = error.response?.data?.message || 'เกิดข้อผิดพลาด';
                    if (error.response?.data?.preview) {
                        this.previewData = { preview: error.response.data.preview };
                    }
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                },
            };
        }
    </script>
</x-app-layout>
