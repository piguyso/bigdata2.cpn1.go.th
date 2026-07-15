<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">ข้อมูลนักเรียน</h2>
            <p class="text-sm text-slate-500 mt-1">Dashboard จากข้อมูล BIGDATA แยกตามชนิดข้อมูล ปีการศึกษา และรอบ</p>
        </div>
    </x-slot>

    <div class="py-8" x-data="studentDataDashboard()" x-init="load()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-[150px_120px_1fr] gap-3">
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">ปีการศึกษา</span>
                        <input x-model="filters.academic_year" @change="load()" class="w-full rounded-lg border-slate-300" maxlength="4">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">รอบ</span>
                        <select x-model="filters.term" @change="load()" class="w-full rounded-lg border-slate-300">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-bold text-slate-500">ค้นหาโรงเรียน</span>
                        <input x-model="search" class="w-full rounded-lg border-slate-300" placeholder="ชื่อโรงเรียน, SMIS, อำเภอ">
                    </label>
                </div>
            </div>

            <div class="flex gap-2 overflow-x-auto pb-1">
                <template x-for="(type, key) in dataTypes" :key="key">
                    <button type="button" @click="selectType(key)" class="shrink-0 px-4 py-2 rounded-lg text-sm font-bold border"
                            :class="filters.data_type === key ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            x-text="type.label"></button>
                </template>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <template x-for="card in summaryCards()" :key="card.label">
                    <div class="bg-white border border-slate-200 rounded-lg p-4">
                        <div class="text-xs font-bold text-slate-500" x-text="card.label"></div>
                        <div class="mt-1 text-2xl font-extrabold" :class="card.class" x-text="formatNumber(card.value)"></div>
                    </div>
                </template>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-800" x-text="dashboard.label || 'ข้อมูลนักเรียน'"></h3>
                        <p class="text-sm text-slate-500" x-text="'ปี ' + filters.academic_year + ' รอบ ' + filters.term"></p>
                    </div>
                    <div x-show="loading" class="text-sm font-bold text-sky-600">กำลังโหลด...</div>
                </div>

                <div x-show="categoryRows().length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                    <template x-for="category in categoryRows().slice(0, 6)" :key="category.label">
                        <div class="border border-slate-200 rounded-lg p-4">
                            <div class="text-sm font-bold text-slate-700 truncate" x-text="category.label"></div>
                            <div class="mt-2 text-xl font-extrabold text-orange-700" x-text="formatNumber(category.total)"></div>
                            <div class="text-xs text-slate-500" x-text="formatNumber(category.records) + ' รายการ'"></div>
                        </div>
                    </template>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">โรงเรียน</th>
                                <th class="px-4 py-3 text-left">กลุ่ม/อำเภอ</th>
                                <th class="px-4 py-3 text-left">หมวด</th>
                                <th class="px-4 py-3 text-right">ชาย</th>
                                <th class="px-4 py-3 text-right">หญิง</th>
                                <th class="px-4 py-3 text-right">รวม</th>
                                <th class="px-4 py-3 text-left">รายการเด่น</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="school in filteredSchools()" :key="school.school_smis + school.category">
                                <tr class="border-t border-slate-100 hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div class="font-extrabold text-slate-800" x-text="school.schoolname"></div>
                                        <div class="text-xs text-slate-500" x-text="'SMIS ' + school.school_smis"></div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        <div x-text="school.school_group || '-'"></div>
                                        <div class="text-xs text-slate-400" x-text="school.district || '-'"></div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600" x-text="school.category || '-'"></td>
                                    <td class="px-4 py-3 text-right text-sky-700 font-bold" x-text="formatNumber(school.male)"></td>
                                    <td class="px-4 py-3 text-right text-rose-700 font-bold" x-text="formatNumber(school.female)"></td>
                                    <td class="px-4 py-3 text-right text-orange-700 font-extrabold" x-text="formatNumber(school.total)"></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="metric in school.top_metrics" :key="metric.key">
                                                <span class="px-2 py-1 rounded bg-slate-100 text-xs text-slate-600" x-text="metric.label + ' ' + formatNumber(metric.total)"></span>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!loading && filteredSchools().length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 font-bold">ไม่พบข้อมูลตามเงื่อนไข</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function studentDataDashboard() {
            return {
                dataTypes: @json($dataTypes),
                dashboard: { summary: {}, schools: [], categories: [] },
                filters: { academic_year: '2569', term: '1', data_type: 'class_gender' },
                search: '',
                loading: false,
                load() {
                    this.loading = true;
                    axios.get('{{ route('api.student-data.dashboard') }}', { params: this.filters }).then(({ data }) => {
                        this.dashboard = data;
                        this.dataTypes = data.data_types || this.dataTypes;
                        this.filters.academic_year = data.academic_year || this.filters.academic_year;
                        this.filters.term = data.term || this.filters.term;
                        this.filters.data_type = data.data_type || this.filters.data_type;
                    }).finally(() => this.loading = false);
                },
                selectType(key) {
                    this.filters.data_type = key;
                    this.load();
                },
                summaryCards() {
                    const s = this.dashboard.summary || {};
                    return [
                        { label: 'โรงเรียน', value: s.schools || 0, class: 'text-slate-800' },
                        { label: 'รายการ', value: s.records || 0, class: 'text-slate-800' },
                        { label: 'ชาย', value: s.male || 0, class: 'text-sky-700' },
                        { label: 'หญิง', value: s.female || 0, class: 'text-rose-700' },
                        { label: 'รวม', value: s.total || 0, class: 'text-orange-700' },
                    ];
                },
                categoryRows() {
                    return this.dashboard.categories || [];
                },
                filteredSchools() {
                    const q = this.search.trim().toLowerCase();
                    const schools = this.dashboard.schools || [];
                    if (!q) return schools;
                    return schools.filter((school) => [
                        school.schoolname,
                        school.school_smis,
                        school.district,
                        school.school_group,
                        school.category,
                    ].join(' ').toLowerCase().includes(q));
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                },
            };
        }
    </script>
</x-app-layout>
