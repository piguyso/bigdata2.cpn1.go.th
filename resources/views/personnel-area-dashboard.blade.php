<x-layout>
    <x-slot:title>ข้อมูลบุคลากรในเขตพื้นที่ | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data='personnelAreaDashboard(@json($dashboardPayload), @json(route('api.personnel.area')))' x-init="fetchDashboard()">
        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="/" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ข้อมูลบุคลากร</span>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ข้อมูลบุคลากรในเขตพื้นที่</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ข้อมูลบุคลากรในเขตพื้นที่</h2>
                <p class="text-slate-500 text-sm mt-1" x-text="dashboard.selectedArea"></p>
            </div>

            <a href="{{ route('personnel.area', ['year' => $dashboardPayload['selectedYear'] ?? null, 'term' => $dashboardPayload['selectedTerm'] ?? null]) }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-xs font-extrabold text-white hover:bg-orange-600 transition">
                <i class="fa-solid fa-rotate-right"></i>
                โหลดข้อมูลใหม่
            </a>
        </header>

        <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-5 mb-6">
            <form x-ref="filterForm" method="GET" action="{{ route('personnel.area') }}" @submit.prevent="fetchDashboard()" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
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
            </form>
        </section>

        <!-- Loading State -->
        <div x-show="loading" class="py-28 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังโหลดข้อมูลบุคลากร...</span>
        </div>

        <div x-show="!loading && !dashboard.rows.length" class="bg-white border border-rose-100 rounded-3xl p-10 text-center" x-cloak>
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ไม่พบข้อมูล report02 ในฐานข้อมูล local</h3>
        </div>

        <div x-show="!loading && dashboard.rows.length" class="space-y-6" x-cloak>
            <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <template x-for="item in dashboard.overview" :key="item.label">
                    <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg shrink-0" :class="item.iconBg">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-extrabold text-slate-400 uppercase" x-text="item.label"></span>
                            <span class="text-2xl font-extrabold text-slate-800" x-text="formatNumber(item.value)"></span>
                        </div>
                    </div>
                </template>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-orange-500"></i> จำแนกตามตำแหน่ง
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">ปี <span x-text="dashboard.selectedYear"></span> รอบ <span x-text="dashboard.selectedTerm"></span></p>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        อัปเดตล่าสุด
                        <span class="font-bold text-slate-500" x-text="formatDateTime(dashboard.fetchedAt)"></span>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="min-w-[980px] w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-extrabold w-14 text-center">ที่</th>
                                <th class="px-4 py-3 font-extrabold">ตำแหน่ง</th>
                                <th class="px-4 py-3 font-extrabold text-right">รวมทั้งหมด</th>
                                <th class="px-4 py-3 font-extrabold text-right">คนครอง</th>
                                <th class="px-4 py-3 font-extrabold text-right">อัตราว่าง</th>
                                <th class="px-4 py-3 font-extrabold text-right">ช่วยราชการ</th>
                                <th class="px-4 py-3 font-extrabold text-right">ติดเงื่อนไข</th>
                                <th class="px-4 py-3 font-extrabold text-right">ไม่มีเงิน</th>
                                <th class="px-4 py-3 font-extrabold text-right">ว่างรวม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="row in dashboard.rows" :key="row.label">
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-4 py-3 text-center font-bold text-slate-400" x-text="row.index"></td>
                                    <td class="px-4 py-3 font-extrabold text-slate-800" x-text="row.label"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-800" x-text="formatNumber(row.all)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-700" x-text="formatNumber(row.position)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-700" x-text="formatNumber(row.empty)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-600" x-text="formatNumber(row.s04)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-rose-700" x-text="formatNumber(row.condition)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-600" x-text="formatNumber(row.noMoney)"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(row.emptyAll)"></td>
                                </tr>
                            </template>
                            <tr class="bg-slate-900 text-white">
                                <td class="px-4 py-4 text-center font-extrabold" colspan="2">รวมทั้งหมด</td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.all)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.position)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.empty)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.s04)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.condition)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.noMoney)"></td>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.total.emptyAll)"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-venus-mars text-orange-500"></i> แยกตามเพศ
                        </h3>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">report08 เฉพาะเขตพื้นที่</span>
                    </div>
                    <div class="space-y-4">
                        <template x-for="row in dashboard.areaReports.gender.rows" :key="row.label">
                            <div>
                                <div class="flex items-center justify-between gap-3 text-xs font-bold">
                                    <span class="text-slate-700" x-text="row.label"></span>
                                    <span class="text-slate-500">
                                        <span x-text="formatNumber(row.value)"></span>
                                        <span class="text-slate-400" x-text="'(' + formatPercent(row.percent) + '%)'"></span>
                                    </span>
                                </div>
                                <div class="mt-2 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full" :class="row.className" :style="barWidth(row.percent)"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-graduate text-orange-500"></i> แยกตามวุฒิการศึกษา
                        </h3>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">report07 เฉพาะเขตพื้นที่</span>
                    </div>
                    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                        <table class="min-w-[520px] w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-extrabold">วุฒิการศึกษา</th>
                                    <th class="px-4 py-3 font-extrabold text-right">จำนวน</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="row in dashboard.areaReports.education.rows" :key="row.key">
                                    <tr class="hover:bg-orange-50/40 transition">
                                        <td class="px-4 py-3 font-bold text-slate-700" x-text="row.label"></td>
                                        <td class="px-4 py-3 text-right font-extrabold text-slate-800" x-text="formatNumber(row.value)"></td>
                                    </tr>
                                </template>
                                <tr class="bg-slate-900 text-white">
                                    <td class="px-4 py-4 font-extrabold">รวมทั้งหมด</td>
                                    <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.areaReports.education.total)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-orange-500"></i> แยกตามวุฒิการศึกษาและเพศ
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">แสดงเฉพาะเมื่อ localdb มีข้อมูลแยกวุฒิและเพศของบุคลากรในเขตพื้นที่</p>
                    </div>
                </div>
                <div x-show="!dashboard.areaReports.educationGender.rows.length"
                     class="border border-dashed border-slate-200 rounded-2xl px-4 py-6 text-center text-xs font-bold text-slate-400">
                    <span x-text="dashboard.areaReports.educationGender.message || 'ไม่มีข้อมูลเฉพาะเขตพื้นที่'"></span>
                </div>
                <div x-show="dashboard.areaReports.educationGender.rows.length" class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="min-w-[720px] w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-extrabold">วุฒิการศึกษา</th>
                                <template x-for="column in dashboard.areaReports.educationGender.genderColumns" :key="column.key">
                                    <th class="px-4 py-3 font-extrabold text-right" x-text="column.label"></th>
                                </template>
                                <th class="px-4 py-3 font-extrabold text-right">รวม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="row in dashboard.areaReports.educationGender.rows" :key="row.key">
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-4 py-3 font-bold text-slate-700" x-text="row.label"></td>
                                    <td class="px-4 py-3 text-right font-bold text-indigo-700" x-text="formatNumber(row.male)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-rose-700" x-text="formatNumber(row.female)"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-800" x-text="formatNumber(row.male + row.female)"></td>
                                </tr>
                            </template>
                            <tr class="bg-slate-900 text-white">
                                <td class="px-4 py-4 font-extrabold">รวมทั้งหมด</td>
                                <template x-for="column in dashboard.areaReports.educationGender.genderColumns" :key="'total-' + column.key">
                                    <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(column.total)"></td>
                                </template>
                                <td class="px-4 py-4 text-right font-extrabold" x-text="formatNumber(dashboard.areaReports.gender.total)"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-orange-500"></i> แยกตามวุฒิการศึกษาและตำแหน่ง
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">ข้อมูลจาก report07 ระดับเขตพื้นที่</p>
                    </div>
                </div>
                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="min-w-[1080px] w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-extrabold min-w-[220px]">ตำแหน่ง</th>
                                <th class="px-4 py-3 font-extrabold text-right">รวม</th>
                                <template x-for="column in dashboard.areaReports.educationPosition.educationColumns" :key="column.key">
                                    <th class="px-4 py-3 font-extrabold text-right" x-text="column.label"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="row in dashboard.areaReports.educationPosition.rows" :key="row.key">
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-4 py-3 font-extrabold text-slate-800" x-text="row.label"></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-800" x-text="formatNumber(row.total)"></td>
                                    <template x-for="column in dashboard.areaReports.educationPosition.educationColumns" :key="row.key + column.key">
                                        <td class="px-4 py-3 text-right font-bold text-slate-600" x-text="formatNumber(row[column.key])"></td>
                                    </template>
                                </tr>
                            </template>
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
        function personnelAreaDashboard(payload, apiUrl) {
            return {
                apiUrl,
                loading: false,
                dashboard: payload || {
                    rows: [],
                    total: {},
                    overview: [],
                    areaReports: {
                        gender: { rows: [], total: 0 },
                        education: { rows: [], total: 0 },
                        educationGender: { rows: [], genderColumns: [] },
                        educationPosition: { rows: [], educationColumns: [] },
                    },
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                },
                formatPercent(value) {
                    return new Intl.NumberFormat('th-TH', { maximumFractionDigits: 1 }).format(Number(value || 0));
                },
                barWidth(value) {
                    return `width: ${Math.max(Number(value || 0), 3)}%`;
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
