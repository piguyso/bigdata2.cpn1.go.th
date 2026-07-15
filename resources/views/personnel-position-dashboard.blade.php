<x-layout>
    <x-slot:title>ข้อมูลแยกตามตำแหน่ง | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data='personnelPositionDashboard(@json($dashboardPayload), @json(route('api.personnel.position')))' x-init="fetchDashboard()">
        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="/" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span>ข้อมูลบุคลากร</span>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">แยกตามตำแหน่ง</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ข้อมูลแยกตามตำแหน่ง</h2>
                <p class="text-slate-500 text-sm mt-1">แสดงตำแหน่งของสำนักงานเขตพื้นที่และโรงเรียนจากฐานข้อมูล local</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-extrabold text-slate-600">
                    <i class="fa-solid fa-location-dot text-orange-500 mr-2"></i>
                    <span x-text="dashboard.selectedArea"></span>
                </div>
                <a href="{{ route('personnel.position', ['year' => $dashboardPayload['selectedYear'] ?? null, 'term' => $dashboardPayload['selectedTerm'] ?? null, 'school_smis' => $dashboardPayload['selectedSchoolSmis'] ?? null]) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-xs font-extrabold text-white hover:bg-orange-600 transition">
                    <i class="fa-solid fa-rotate-right"></i>
                    โหลดข้อมูลใหม่
                </a>
            </div>
        </header>

        <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-5 mb-6">
            <form x-ref="filterForm" method="GET" action="{{ route('personnel.position') }}" @submit.prevent="fetchDashboard()" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">ปีการศึกษา</label>
                    <select class="form-input w-full" name="year" onchange="this.form.requestSubmit()">
                        @foreach(($dashboardPayload['availableYears'] ?? []) as $year)
                            <option value="{{ $year }}" @selected((string) $year === (string) ($dashboardPayload['selectedYear'] ?? ''))>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">รอบ</label>
                    <select class="form-input w-full" name="term" onchange="this.form.requestSubmit()">
                        @foreach(($dashboardPayload['availableTerms'] ?? []) as $term)
                            <option value="{{ $term }}" @selected((string) $term === (string) ($dashboardPayload['selectedTerm'] ?? ''))>รอบ {{ $term }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1 xl:col-span-2">
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">โรงเรียน</label>
                    <input x-ref="schoolSmisInput" type="hidden" name="school_smis" :value="selectedSchoolSmis">
                    <div class="relative" @click.away="schoolDropdownOpen = false">
                        <button type="button" @click="toggleSchoolDropdown()" class="form-input w-full text-left flex items-center justify-between gap-3">
                            <span class="truncate" x-text="selectedSchoolOption() ? selectedSchoolOption().label : 'ภาพรวมทั้งเขต'"></span>
                            <i class="fa-solid fa-chevron-down text-[11px] text-slate-400 shrink-0"></i>
                        </button>

                        <div x-show="schoolDropdownOpen" x-transition x-cloak class="absolute z-30 mt-2 w-full rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                            <div class="p-3 border-b border-slate-100">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" x-ref="schoolSearchInput" class="form-input w-full pr-4" style="padding-left: 2.75rem;" x-model="schoolSearch" placeholder="ค้นหาชื่อโรงเรียนหรือรหัส SMIS">
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto py-1">
                                <template x-if="filteredSchools().length === 0">
                                    <div class="px-4 py-6 text-center text-xs font-bold text-slate-400">ไม่พบโรงเรียนที่ค้นหา</div>
                                </template>
                                <template x-for="school in filteredSchools()" :key="school.schoolSmis || 'area'">
                                    <button type="button" @click="selectSchool(school)" class="w-full px-4 py-3 text-left hover:bg-orange-50 transition flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <template x-if="school.logoUrl">
                                                <img :src="school.logoUrl" :alt="school.schoolName" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                            </template>
                                            <div x-show="!school.logoUrl" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-school text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-extrabold text-slate-700 truncate" x-text="school.schoolName"></div>
                                                <div class="mt-1 text-[11px] font-bold text-slate-400" x-text="school.schoolSmis || 'ทุกโรงเรียนในเขตพื้นที่'"></div>
                                            </div>
                                        </div>
                                        <i x-show="String(selectedSchoolSmis || '') === String(school.schoolSmis || '')" class="fa-solid fa-check text-orange-500 text-xs mt-1 shrink-0" x-cloak></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <div x-show="!hasAnyData()" class="bg-white border border-rose-100 rounded-3xl p-10 text-center" x-cloak>
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ไม่พบข้อมูลตำแหน่งในฐานข้อมูล local</h3>
            <p class="text-xs text-slate-400 mt-2">กรุณานำเข้าข้อมูลภาพรวมบุคลากรจาก HRMS ก่อน</p>
        </div>

        <div x-show="hasAnyData()" class="space-y-6" x-cloak>
            <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <template x-for="item in dashboard.overview" :key="item.label">
                    <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg shrink-0" :class="item.iconBg">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-extrabold text-slate-400 uppercase" x-text="item.label"></span>
                            <span class="text-2xl font-extrabold text-slate-800" x-text="formatNumber(item.value)"></span>
                            <span class="text-[9px] text-slate-400 block mt-0.5" x-text="'ปี ' + dashboard.selectedYear + ' รอบ ' + dashboard.selectedTerm"></span>
                        </div>
                    </div>
                </template>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase">สรุปตำแหน่งเขตพื้นที่</div>
                        <span class="text-[10px] font-bold text-slate-400" x-text="formatDateTime(dashboard.fetchedAt)"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="group in dashboard.areaPositionGroups" :key="group.key">
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-bold text-slate-500 truncate" x-text="group.label"></div>
                                <div class="mt-1 text-lg font-extrabold text-slate-800" x-text="formatNumber(dashboard.areaTotal[group.key].all)"></div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">
                                    คนครอง <span x-text="formatNumber(dashboard.areaTotal[group.key].position)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase">สรุปตำแหน่งโรงเรียน</div>
                        <span class="text-[10px] font-bold text-slate-400">
                            <span x-text="formatNumber(dashboard.rows.length)"></span> รายการ
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="group in dashboard.positionGroups" :key="group.key">
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-bold text-slate-500 truncate" x-text="group.label"></div>
                                <div class="mt-1 text-lg font-extrabold text-slate-800" x-text="formatNumber(dashboard.total[group.key].all)"></div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">
                                    คนครอง <span x-text="formatNumber(dashboard.total[group.key].position)"></span>
                                </div>
                            </div>
                        </template>
                        <div class="rounded-2xl bg-orange-50 border border-orange-100 px-4 py-3">
                            <div class="text-[10px] font-bold text-orange-700">รวมโรงเรียน</div>
                            <div class="mt-1 text-lg font-extrabold text-orange-700" x-text="formatNumber(dashboard.total.person.all)"></div>
                            <div class="text-[10px] font-bold text-orange-600 mt-0.5">
                                ว่างรวม <span x-text="formatNumber(dashboard.total.person.emptyAll)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-building-user text-orange-500"></i> สำนักงานเขตพื้นที่
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">ตำแหน่งบุคลากรในสำนักงานเขตพื้นที่ ไม่รวมโรงเรียน</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                    <template x-for="group in dashboard.areaPositionGroups" :key="group.key">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[11px] font-extrabold text-slate-500 truncate" x-text="group.label"></div>
                                    <div class="mt-1 text-3xl font-extrabold text-slate-800" x-text="formatNumber(dashboard.areaTotal[group.key].all)"></div>
                                </div>
                                <div class="w-10 h-10 rounded-2xl bg-white border border-slate-100 text-orange-500 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2 text-[10px] font-bold">
                                <div class="rounded-xl bg-white border border-slate-100 px-3 py-2">
                                    <div class="text-slate-400">จ.18</div>
                                    <div class="text-slate-700 mt-0.5" x-text="formatNumber(dashboard.areaTotal[group.key].j18)"></div>
                                </div>
                                <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-3 py-2">
                                    <div class="text-emerald-600">คนครอง</div>
                                    <div class="text-emerald-700 mt-0.5" x-text="formatNumber(dashboard.areaTotal[group.key].position)"></div>
                                </div>
                                <div class="rounded-xl bg-amber-50 border border-amber-100 px-3 py-2">
                                    <div class="text-amber-600">ว่างรวม</div>
                                    <div class="text-amber-700 mt-0.5" x-text="formatNumber(dashboard.areaTotal[group.key].emptyAll)"></div>
                                </div>
                                <div class="rounded-xl bg-rose-50 border border-rose-100 px-3 py-2">
                                    <div class="text-rose-600">ติดเงื่อนไข</div>
                                    <div class="text-rose-700 mt-0.5" x-text="formatNumber(dashboard.areaTotal[group.key].condition)"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 rounded-2xl bg-slate-900 text-white px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold">รวมสำนักงานเขตพื้นที่</div>
                        <div class="text-[10px] text-slate-300 mt-0.5" x-text="dashboard.areaRows[0]?.label || 'สำนักงานเขตพื้นที่'"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-right">
                        <div>
                            <div class="text-[10px] text-slate-300">รวม</div>
                            <div class="text-lg font-extrabold" x-text="formatNumber(dashboard.areaTotal.person.all)"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-300">คนครอง</div>
                            <div class="text-lg font-extrabold" x-text="formatNumber(dashboard.areaTotal.person.position)"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-300">ว่างรวม</div>
                            <div class="text-lg font-extrabold" x-text="formatNumber(dashboard.areaTotal.person.emptyAll)"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-school text-orange-500"></i> โรงเรียน
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">
                            แสดง <span class="font-bold text-slate-500" x-text="formatNumber(dashboard.rows.length)"></span>
                            รายการตามตัวกรอง
                        </p>
                    </div>
                    <div class="text-[10px] font-bold text-slate-400">
                        <span x-text="'ปี ' + dashboard.selectedYear + ' รอบ ' + dashboard.selectedTerm"></span>
                    </div>
                </div>
                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="min-w-[1500px] w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th rowspan="2" class="px-4 py-3 font-extrabold w-14 text-center align-middle">ที่</th>
                                <th rowspan="2" class="px-4 py-3 font-extrabold min-w-[260px] align-middle">โรงเรียน</th>
                                <th rowspan="2" class="px-4 py-3 font-extrabold align-middle">อำเภอ/ตำบล</th>
                                <th rowspan="2" class="px-4 py-3 font-extrabold text-right align-middle">รวมทั้งหมด</th>
                                <template x-for="group in dashboard.positionGroups" :key="group.key">
                                    <th colspan="6" class="px-3 py-3 font-extrabold text-center border-l border-slate-200" x-text="group.label"></th>
                                </template>
                            </tr>
                            <tr>
                                <template x-for="group in dashboard.positionGroups" :key="'school-head-' + group.key">
                                    <template x-for="column in metricColumns" :key="group.key + column.key">
                                        <th class="px-3 py-2 font-extrabold text-right border-l border-slate-100" x-text="column.label"></th>
                                    </template>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="row in dashboard.rows" :key="row.schoolSmis || row.schoolCode || row.index">
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-bold text-slate-400" x-text="row.index"></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <template x-if="row.logoUrl">
                                                <img :src="row.logoUrl" :alt="row.schoolName" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                            </template>
                                            <div x-show="!row.logoUrl" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-school text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-extrabold text-slate-800 truncate" x-text="row.schoolName"></div>
                                                <div class="text-[10px] text-slate-400">
                                                    <span x-text="row.schoolSmis || '-'"></span>
                                                    <span x-show="row.schoolSize"> / </span>
                                                    <span x-text="row.schoolSize"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        <div class="font-bold" x-text="row.district || '-'"></div>
                                        <div class="text-[10px] text-slate-400" x-text="row.subdistrict"></div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-900" x-text="formatNumber(row.person.all)"></td>
                                    <template x-for="group in dashboard.positionGroups" :key="'school-row-' + row.index + group.key">
                                        <template x-for="column in metricColumns" :key="row.index + group.key + column.key">
                                            <td class="px-3 py-3 text-right font-bold" :class="column.className" x-text="formatNumber(row[group.key][column.key])"></td>
                                        </template>
                                    </template>
                                </tr>
                            </template>
                            <template x-if="dashboard.rows.length === 0">
                                <tr>
                                    <td colspan="22" class="px-4 py-10 text-center text-slate-400 font-bold">ไม่พบข้อมูลโรงเรียนที่เลือก</td>
                                </tr>
                            </template>
                            <tr class="bg-slate-900 text-white">
                                <td class="px-4 py-4 text-center font-extrabold" colspan="3">รวมโรงเรียน</td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.person.all)"></td>
                                <template x-for="group in dashboard.positionGroups" :key="'school-total-' + group.key">
                                    <template x-for="column in metricColumns" :key="'school-total-' + group.key + column.key">
                                        <td class="px-3 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total[group.key][column.key])"></td>
                                    </template>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <style>
        .form-input {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #334155;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function personnelPositionDashboard(payload, apiUrl) {
            return {
                apiUrl,
                loading: false,
                dashboard: payload || { rows: [], areaRows: [], total: {}, areaTotal: {}, overview: [], positionGroups: [], areaPositionGroups: [] },
                selectedSchoolSmis: payload?.selectedSchoolSmis || '',
                schoolDropdownOpen: false,
                schoolSearch: '',
                metricColumns: [
                    { key: 'all', label: 'รวม', className: 'text-slate-900' },
                    { key: 'j18', label: 'จ.18', className: 'text-slate-600' },
                    { key: 'position', label: 'คนครอง', className: 'text-emerald-700' },
                    { key: 'empty', label: 'ว่าง', className: 'text-amber-700' },
                    { key: 'condition', label: 'ติดเงื่อนไข', className: 'text-rose-700' },
                    { key: 'emptyAll', label: 'ว่างรวม', className: 'text-orange-700' },
                ],
                hasAnyData() {
                    return (this.dashboard?.rows || []).length > 0 || (this.dashboard?.areaRows || []).length > 0;
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                },
                selectedSchoolOption() {
                    return (this.dashboard?.availableSchools || []).find(item => String(item.schoolSmis || '') === String(this.selectedSchoolSmis || '')) || null;
                },
                filteredSchools() {
                    const keyword = String(this.schoolSearch || '').trim().toLowerCase();
                    const schools = this.dashboard?.availableSchools || [];

                    if (!keyword) {
                        return schools;
                    }

                    return schools.filter(item => {
                        return String(item.schoolName || '').toLowerCase().includes(keyword)
                            || String(item.schoolSmis || '').toLowerCase().includes(keyword)
                            || String(item.label || '').toLowerCase().includes(keyword);
                    });
                },
                toggleSchoolDropdown() {
                    this.schoolDropdownOpen = !this.schoolDropdownOpen;
                    if (this.schoolDropdownOpen) {
                        this.schoolSearch = '';
                        this.$nextTick(() => this.$refs.schoolSearchInput?.focus());
                    }
                },
                selectSchool(school) {
                    const schoolSmis = school.schoolSmis || '';
                    this.selectedSchoolSmis = schoolSmis;
                    this.schoolDropdownOpen = false;
                    this.$nextTick(() => {
                        if (this.$refs.schoolSmisInput) {
                            this.$refs.schoolSmisInput.value = schoolSmis;
                        }

                        if (this.$refs.filterForm?.requestSubmit) {
                            this.$refs.filterForm.requestSubmit();
                            return;
                        }

                        this.$refs.filterForm?.submit();
                    });
                },
                fetchDashboard() {
                    if (!this.apiUrl || !this.$refs.filterForm) {
                        return;
                    }

                    const params = Object.fromEntries(new FormData(this.$refs.filterForm).entries());
                    this.loading = true;
                    axios.get(this.apiUrl, { params })
                        .then(response => {
                            this.dashboard = response.data || this.dashboard;
                            this.selectedSchoolSmis = this.dashboard.selectedSchoolSmis || '';
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },
                formatDateTime(value) {
                    if (!value) {
                        return '-';
                    }

                    try {
                        return new Date(value).toLocaleString('th-TH', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                        });
                    } catch (e) {
                        return value;
                    }
                },
            };
        }
    </script>
</x-layout>
