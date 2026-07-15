<x-layout>
    <x-slot:title>ข้อมูลนักเรียนเพิ่มเติม | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="studentDataDashboard()" x-init="init()">
        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">Student Extra Dashboard</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">ข้อมูลนักเรียนเพิ่มเติม</h2>
                <p class="text-slate-500 text-sm mt-1">แสดงข้อมูล BIGDATA รายโรงเรียน แยกตามชนิดข้อมูล ปีการศึกษา และรอบ</p>
            </div>
            <form class="grid grid-cols-2 sm:grid-cols-[140px_100px_180px_220px] gap-3" @submit.prevent="load(1)">
                <label class="space-y-1.5 block">
                    <span class="text-xs font-bold text-slate-500">ปีการศึกษา</span>
                    <select x-model="filters.academic_year" @change="clearCategoryAndLoad()" class="form-input">
                        <template x-for="year in academicYears" :key="year.year">
                            <option :value="year.year" x-text="year.name || ('ปีการศึกษา ' + year.year)"></option>
                        </template>
                    </select>
                </label>
                <label class="space-y-1.5 block">
                    <span class="text-xs font-bold text-slate-500">รอบ</span>
                    <select x-model="filters.term" @change="clearCategoryAndLoad()" class="form-input">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </label>
                <label class="space-y-1.5 block">
                    <span class="text-xs font-bold text-slate-500">ต่อหน้า</span>
                    <select x-model="filters.per_page" @change="load(1)" class="form-input">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="36">36</option>
                        <option value="48">48</option>
                    </select>
                </label>
                <label class="space-y-1.5 block col-span-2 sm:col-span-1">
                    <span class="text-xs font-bold text-slate-500">ค้นหาโรงเรียน</span>
                    <input x-model="filters.search" @input.debounce.450ms="load(1)" class="form-input" placeholder="ชื่อโรงเรียน, SMIS, อำเภอ">
                </label>
            </form>
        </header>

        <section class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
            <template x-for="card in summaryCards()" :key="card.label">
                <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="card.label"></p>
                    <p class="text-2xl font-extrabold mt-1" :class="card.class" x-text="formatNumber(card.value)"></p>
                </div>
            </template>
        </section>

        <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-orange-500"></i>
                    ชนิดข้อมูล
                </h3>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                <template x-for="(type, key) in dataTypes" :key="key">
                    <button type="button"
                            @click="selectType(key)"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl text-xs font-bold border transition text-left leading-snug"
                            :class="filters.data_type === key ? 'bg-orange-600 text-white border-orange-600 shadow-md shadow-orange-100' : 'bg-white text-slate-600 border-slate-200 hover:bg-orange-50 hover:text-orange-600'"
                            x-text="type.label"></button>
                </template>
            </div>
        </div>

        <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-school text-orange-500"></i>
                        <span x-text="dashboard.label || 'ข้อมูลนักเรียนเพิ่มเติม'"></span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1" x-text="'ปี ' + filters.academic_year + ' รอบ ' + filters.term"></p>
                </div>
                <div class="flex flex-col sm:items-end gap-1">
                    <div class="text-xs font-bold text-sky-600" x-show="loading" x-cloak>กำลังโหลด...</div>
                    <div class="text-xs font-bold text-slate-400" x-show="!loading" x-cloak x-text="paginationText()"></div>
                    <a :href="exportUrl()"
                       class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm shadow-emerald-100">
                        <i class="fa-solid fa-file-excel"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div x-show="categoryRows().length > 0" class="p-5 border-b border-slate-100" x-cloak>
                <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-xs font-extrabold text-slate-700">สรุปตามหมวด</p>
                        <p class="text-[11px] text-slate-400 font-bold" x-show="filters.category" x-cloak x-text="'กำลังแสดง: ' + filters.category"></p>
                    </div>
                    <button type="button"
                            x-show="filters.category"
                            x-cloak
                            @click="clearCategoryAndLoad()"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-[11px] font-bold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        <i class="fa-solid fa-xmark text-slate-400"></i>
                        แสดงทุกหมวด
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <template x-for="category in categoryRows().slice(0, 6)" :key="category.label">
                        <button type="button"
                                @click="selectCategory(category.label)"
                                class="text-left border rounded-2xl px-4 py-3 transition hover:border-orange-200 hover:bg-orange-50/60 focus:outline-none focus:ring-2 focus:ring-orange-200"
                                :class="filters.category === category.label ? 'bg-orange-50 border-orange-200 shadow-sm' : 'bg-slate-50 border-slate-100'">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-xs font-extrabold truncate" :class="filters.category === category.label ? 'text-orange-700' : 'text-slate-700'" x-text="category.label"></div>
                                    <div class="text-[11px] text-slate-400 font-bold mt-1" x-text="formatNumber(category.records) + ' รายการ'"></div>
                                </div>
                                <i class="fa-solid fa-filter text-[10px] mt-1" :class="filters.category === category.label ? 'text-orange-500' : 'text-slate-300'"></i>
                            </div>
                            <div class="mt-2 text-xl font-extrabold text-orange-700" x-text="formatNumber(category.total)"></div>
                        </button>
                    </template>
                </div>
            </div>

            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold">
                                <th class="px-4 py-3">โรงเรียน</th>
                                <th class="px-4 py-3">เครือข่าย</th>
                                <th class="px-4 py-3">อำเภอ</th>
                                <th class="px-4 py-3">หมวด</th>
                                <th class="px-4 py-3 text-right">ชาย</th>
                                <th class="px-4 py-3 text-right">หญิง</th>
                                <th class="px-4 py-3 text-right">รวม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="school in dashboard.schools" :key="school.school_smis + '-' + school.category">
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-4 py-3 min-w-[260px]">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <template x-if="school.logo_url">
                                                <img :src="school.logo_url" :alt="school.schoolname" class="w-8 h-8 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                            </template>
                                            <div x-show="!school.logo_url" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-school text-[11px]"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-extrabold text-slate-800 truncate" x-text="school.schoolname"></div>
                                                <div class="text-[10px] text-slate-400 mt-1 tabular-nums" x-text="'SMIS ' + school.school_smis"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 min-w-[150px]" x-text="school.school_group || '-'"></td>
                                    <td class="px-4 py-3 text-slate-500 min-w-[120px]" x-text="school.district || '-'"></td>
                                    <td class="px-4 py-3 text-slate-500 min-w-[150px]" x-text="school.category || '-'"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-sky-700 tabular-nums" x-text="formatNumber(school.male)"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-rose-700 tabular-nums" x-text="formatNumber(school.female)"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-orange-700 tabular-nums" x-text="formatNumber(school.total)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="!loading && (!dashboard.schools || dashboard.schools.length === 0)" x-cloak class="py-12 text-center text-slate-400 font-bold">
                    ไม่พบข้อมูลตามเงื่อนไข
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-4">
                    <div class="text-xs font-bold text-slate-400" x-text="paginationText()"></div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="goPage((dashboard.pagination?.current_page || 1) - 1)"
                                :disabled="loading || (dashboard.pagination?.current_page || 1) <= 1"
                                class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50">
                            ก่อนหน้า
                        </button>
                        <span class="px-3 py-2 rounded-xl bg-slate-50 text-xs font-extrabold text-slate-600" x-text="pageLabel()"></span>
                        <button type="button"
                                @click="goPage((dashboard.pagination?.current_page || 1) + 1)"
                                :disabled="loading || (dashboard.pagination?.current_page || 1) >= (dashboard.pagination?.last_page || 1)"
                                class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50">
                            ถัดไป
                        </button>
                    </div>
                </div>
            </div>
        </section>
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
            function studentDataDashboard() {
                return {
                    dataTypes: @json($dataTypes),
                    academicYears: @json($academicYears),
                    dashboard: { summary: {}, schools: [], categories: [], pagination: {} },
                    filters: { academic_year: @json($activeAcademicYear), term: '1', data_type: @json($defaultDataType), page: 1, per_page: 12, search: '', category: '' },
                    loading: false,
                    init() {
                        this.load();
                    },
                    load(page = null) {
                        if (page !== null) {
                            this.filters.page = page;
                        }
                        this.loading = true;
                        axios.get('{{ route('api.student-data.dashboard') }}', { params: this.filters }).then(({ data }) => {
                            this.dashboard = data;
                            this.dataTypes = data.data_types || this.dataTypes;
                            this.academicYears = data.academic_years || this.academicYears;
                            this.filters.academic_year = data.academic_year || this.filters.academic_year;
                            this.filters.term = data.term || this.filters.term;
                            this.filters.data_type = data.data_type || this.filters.data_type;
                            this.filters.category = data.category || this.filters.category;
                            this.filters.page = data.pagination?.current_page || this.filters.page;
                            this.filters.per_page = data.pagination?.per_page || this.filters.per_page;
                        }).finally(() => this.loading = false);
                    },
                    selectType(key) {
                        this.filters.data_type = key;
                        this.filters.category = '';
                        this.load(1);
                    },
                    selectCategory(label) {
                        this.filters.category = this.filters.category === label ? '' : label;
                        this.load(1);
                    },
                    clearCategoryAndLoad() {
                        this.filters.category = '';
                        this.load(1);
                    },
                    goPage(page) {
                        const lastPage = this.dashboard.pagination?.last_page || 1;
                        if (page < 1 || page > lastPage) return;
                        this.load(page);
                    },
                    summaryCards() {
                        const s = this.dashboard.summary || {};
                        return [
                            { label: 'โรงเรียน', value: s.schools || 0, class: 'text-slate-900' },
                            { label: 'รายการ', value: s.records || 0, class: 'text-slate-900' },
                            { label: 'ชาย', value: s.male || 0, class: 'text-sky-700' },
                            { label: 'หญิง', value: s.female || 0, class: 'text-rose-700' },
                            { label: 'รวม', value: s.total || 0, class: 'text-orange-700' },
                        ];
                    },
                    categoryRows() {
                        return this.dashboard.categories || [];
                    },
                    paginationText() {
                        const p = this.dashboard.pagination || {};
                        if (!p.total) return '0 รายการ';
                        return `${this.formatNumber(p.from || 0)}-${this.formatNumber(p.to || 0)} จาก ${this.formatNumber(p.total)} รายการ`;
                    },
                    pageLabel() {
                        const p = this.dashboard.pagination || {};
                        return `${this.formatNumber(p.current_page || 1)} / ${this.formatNumber(p.last_page || 1)}`;
                    },
                    exportUrl() {
                        const params = new URLSearchParams();
                        ['academic_year', 'term', 'data_type', 'search', 'category'].forEach((key) => {
                            if (this.filters[key]) {
                                params.set(key, this.filters[key]);
                            }
                        });
                        return '{{ route('student-data.export') }}' + '?' + params.toString();
                    },
                    formatNumber(value) {
                        return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                    },
                };
            }
        </script>
    @endpush
</x-layout>
