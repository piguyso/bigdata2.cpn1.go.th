<x-layout>
    <x-slot:title>ผลการทดสอบระดับชาติ {{ $examTitle }} | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="basicExamDashboard()" x-init="init()">
        <header class="mb-8 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="{{ url('/') }}" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ผลการทดสอบระดับชาติ</span>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">{{ $examTitle }}</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ผลการทดสอบระดับชาติ {{ $examTitle }}</h2>
                <p class="text-slate-500 text-sm mt-1">{{ $pageDescription }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-extrabold text-slate-600">
                    <i class="fa-solid fa-location-dot text-orange-500 mr-2"></i>
                    {{ $webSubtitle }}
                </div>
                <button type="button"
                        @click="fetchDashboard(true)"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-xs font-extrabold text-white hover:bg-orange-600 transition">
                    <i class="fa-solid fa-rotate-right"></i>
                    โหลดข้อมูลใหม่
                </button>
            </div>
        </header>

        <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">ปีการศึกษา</label>
                    <select class="form-input w-full" x-model="filters.year" @change="fetchDashboard()">
                        <template x-for="year in availableYears" :key="year">
                            <option :value="year" x-text="year"></option>
                        </template>
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">โรงเรียน</label>
                    <div class="relative" @click.away="schoolDropdownOpen = false">
                        <button type="button"
                                @click="toggleSchoolDropdown()"
                                class="form-input w-full text-left flex items-center justify-between gap-3">
                            <span class="truncate"
                                  :class="selectedSchoolOption() ? 'text-slate-700' : 'text-slate-400'"
                                  x-text="selectedSchoolOption() ? selectedSchoolOption().label : 'เลือกโรงเรียน'"></span>
                            <i class="fa-solid fa-chevron-down text-[11px] text-slate-400 shrink-0"></i>
                        </button>

                        <div x-show="schoolDropdownOpen"
                             x-transition
                             x-cloak
                             class="absolute z-30 mt-2 w-full rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                            <div class="p-3 border-b border-slate-100">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text"
                                           x-ref="schoolSearchInput"
                                           class="form-input w-full pr-4"
                                           style="padding-left: 2.75rem;"
                                           x-model="schoolSearch"
                                           placeholder="ค้นหาชื่อโรงเรียนหรือรหัส SMIS">
                                </div>
                            </div>

                            <div class="max-h-72 overflow-y-auto py-2">
                                <template x-if="filteredSchools.length === 0">
                                    <div class="px-4 py-6 text-center text-xs font-bold text-slate-400">ไม่พบโรงเรียนที่ค้นหา</div>
                                </template>

                                <template x-for="school in filteredSchools" :key="school.schoolCode">
                                    <button type="button"
                                            @click="selectSchool(school)"
                                            class="w-full px-4 py-3 text-left hover:bg-orange-50 transition flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <template x-if="school.logoUrl">
                                                <img :src="school.logoUrl" :alt="school.schoolName" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                            </template>
                                            <div x-show="!school.logoUrl" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-school text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-extrabold text-slate-700 truncate" x-text="school.schoolName"></div>
                                                <div class="mt-1 text-[11px] font-bold text-slate-400" x-text="school.schoolSmis || 'ภาพรวมทั้งเขตฯ'"></div>
                                            </div>
                                        </div>
                                        <i x-show="filters.schoolCode === school.schoolCode" class="fa-solid fa-check text-orange-500 text-xs mt-1 shrink-0" x-cloak></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div x-show="loading" class="py-28 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังดึงข้อมูล {{ $examTitle }}...</span>
        </div>

        <div x-show="!loading && error" class="bg-white border border-rose-100 rounded-3xl p-10 text-center" x-cloak>
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ไม่สามารถดึงข้อมูล {{ $examTitle }} ได้</h3>
            <p class="text-xs text-slate-400 mt-2" x-text="error"></p>
        </div>

        <div x-show="!loading && !error && dashboard" class="space-y-6" x-cloak>
            <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <template x-for="item in dashboard.overview" :key="item.label">
                    <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg shrink-0" :class="item.iconBg">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-extrabold text-slate-400 uppercase" x-text="item.label"></span>
                            <div class="flex items-baseline gap-1.5 mt-0.5">
                                <span class="text-2xl font-extrabold text-slate-800" x-text="formatValue(item.value)"></span>
                                <span class="text-[10px] font-bold text-slate-400" x-text="item.suffix"></span>
                            </div>
                            <span class="text-[9px] text-slate-400 block mt-0.5" x-text="item.note"></span>
                        </div>
                    </div>
                </template>
            </section>

            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)] gap-6">
                <section class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-file-lines text-sky-500"></i> คะแนนรายด้าน
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-1">เทียบคะแนนที่เลือกกับภาพรวมเขตพื้นที่ในปีเดียวกัน</p>
                        </div>
                        <span class="rounded-2xl bg-orange-50 px-3 py-1.5 text-[10px] font-extrabold text-orange-600">{{ $examTitle }}</span>
                    </div>

                    <div x-show="dashboard.subjects.length === 0" class="py-14 text-center text-sm text-slate-400 font-bold">
                        ไม่พบข้อมูล {{ $examTitle }} สำหรับปีที่เลือก
                    </div>

                    <div x-show="dashboard.subjects.length > 0" class="space-y-4" x-cloak>
                        <template x-for="subject in dashboard.subjects" :key="subject.subjectCode">
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-5">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                                    <div>
                                        <h4 class="text-sm font-extrabold text-slate-800" x-text="subject.subjectName"></h4>
                                        <p class="text-[11px] text-slate-400 mt-1" x-text="'ผู้เข้าสอบ/รายการ ' + formatNumber(subject.studentCount)"></p>
                                    </div>
                                    <div class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-extrabold"
                                         :class="subject.diffFromArea >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                        <i class="fa-solid mr-2" :class="subject.diffFromArea >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'"></i>
                                        <span x-text="signedScore(subject.diffFromArea) + ' จากเขตฯ'"></span>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="row in subjectRows(subject)" :key="row.label">
                                        <div class="grid grid-cols-[92px_minmax(0,1fr)_56px] items-center gap-3">
                                            <div class="text-[11px] font-extrabold text-slate-500" x-text="row.label"></div>
                                            <div class="h-2.5 w-full rounded-full bg-white border border-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full" :class="row.barClass" :style="'width:' + scorePercent(row.value) + '%'"></div>
                                            </div>
                                            <div class="text-right text-xs font-extrabold text-slate-700" x-text="formatScore(row.value)"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-school text-orange-500"></i> ข้อมูลโรงเรียน
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-1">โรงเรียนที่กำลังดูผลคะแนนอยู่ตอนนี้</p>
                            </div>
                            <span class="rounded-2xl bg-orange-50 px-3 py-1.5 text-[10px] font-extrabold text-orange-600" x-text="dashboard.selectedSchool?.maxClassLevel || '{{ $examTitle }}'"></span>
                        </div>

                        <div class="space-y-3 text-sm" x-show="dashboard.selectedSchool" x-cloak>
                            <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ชื่อโรงเรียน</div>
                                <div class="mt-2 flex items-center gap-3">
                                    <template x-if="dashboard.selectedSchool?.logoUrl">
                                        <img :src="dashboard.selectedSchool.logoUrl" :alt="dashboard.selectedSchool.schoolName" class="w-12 h-12 rounded-2xl object-contain bg-white border border-slate-100 p-1.5 shrink-0">
                                    </template>
                                    <div x-show="!dashboard.selectedSchool?.logoUrl" class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-school"></i>
                                    </div>
                                    <div class="font-extrabold text-slate-800 min-w-0 truncate" x-text="dashboard.selectedSchool.schoolName"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">SMIS</div>
                                    <div class="mt-1 font-extrabold text-slate-700" x-text="dashboard.selectedSchool.smisCode"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">อำเภอ</div>
                                    <div class="mt-1 font-extrabold text-slate-700" x-text="dashboard.selectedSchool.district"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-chart-line text-violet-500"></i> แนวโน้มคะแนน
                        </h3>
                        <div x-show="chartSeries.length === 0" class="py-12 text-center text-xs font-bold text-slate-400">
                            ยังไม่มีข้อมูลแนวโน้ม
                        </div>
                        <div x-show="chartSeries.length > 0" class="space-y-4" x-cloak>
                            <div class="h-64 rounded-3xl bg-slate-50 border border-slate-100 p-4 relative overflow-hidden">
                                <svg viewBox="0 0 1000 320" class="w-full h-full">
                                    <template x-for="series in chartSeries" :key="series.subjectCode">
                                        <g>
                                            <template x-for="segment in seriesSegments(series)" :key="segment.key">
                                                <line :x1="segment.x1" :y1="segment.y1" :x2="segment.x2" :y2="segment.y2" :stroke="series.color" stroke-width="4" stroke-linecap="round"></line>
                                            </template>
                                            <template x-for="point in series.points" :key="series.subjectCode + '-' + point.year">
                                                <circle :cx="point.x" :cy="point.y" r="7" :fill="series.color" stroke="white" stroke-width="3"></circle>
                                            </template>
                                        </g>
                                    </template>
                                </svg>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="series in chartSeries" :key="series.subjectCode">
                                    <div class="inline-flex items-center gap-2 rounded-xl border border-slate-100 bg-white px-3 py-2 text-[11px] font-bold text-slate-500">
                                        <span class="w-2.5 h-2.5 rounded-full" :style="'background:' + series.color"></span>
                                        <span x-text="series.subjectName"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .form-input {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                background: #fff;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                font-weight: 700;
                color: #475569;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .form-input:focus {
                border-color: #f97316;
                box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
            }
        </style>
        <script>
            function basicExamDashboard() {
                return {
                    apiUrl: @js($apiRoute),
                    dashboard: null,
                    loading: true,
                    error: '',
                    filters: { year: '', schoolCode: '__area__' },
                    availableYears: [],
                    schools: [],
                    schoolSearch: '',
                    schoolDropdownOpen: false,
                    chartColors: ['#0ea5e9', '#f97316', '#8b5cf6', '#10b981', '#ef4444'],
                    init() { this.fetchDashboard(); },
                    fetchDashboard(force = false) {
                        this.loading = true;
                        this.error = '';
                        axios.get(this.apiUrl, {
                            params: {
                                year: this.filters.year || undefined,
                                school_code: this.filters.schoolCode || undefined,
                                refresh: force ? 1 : undefined,
                            }
                        }).then(response => {
                            this.dashboard = response.data;
                            this.availableYears = response.data.availableYears || [];
                            this.schools = response.data.schools || [];
                            this.filters.year = String(response.data.selectedYear || this.availableYears[0] || '');
                            this.filters.schoolCode = response.data.selectedSchool?.schoolCode || '__area__';
                        }).catch(error => {
                            this.error = error.response?.data?.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล';
                        }).finally(() => this.loading = false);
                    },
                    toggleSchoolDropdown() {
                        this.schoolDropdownOpen = !this.schoolDropdownOpen;
                        if (this.schoolDropdownOpen) {
                            this.$nextTick(() => this.$refs.schoolSearchInput?.focus());
                        }
                    },
                    selectedSchoolOption() {
                        return this.schools.find((school) => school.schoolCode === this.filters.schoolCode) || null;
                    },
                    selectSchool(school) {
                        this.filters.schoolCode = school.schoolCode;
                        this.schoolDropdownOpen = false;
                        this.fetchDashboard();
                    },
                    get filteredSchools() {
                        const query = (this.schoolSearch || '').trim().toLowerCase();
                        if (!query) return this.schools;
                        return this.schools.filter((school) => {
                            const schoolName = String(school.schoolName || '').toLowerCase();
                            const schoolSmis = String(school.schoolSmis || '').toLowerCase();
                            const label = String(school.label || '').toLowerCase();
                            return schoolName.includes(query) || schoolSmis.includes(query) || label.includes(query);
                        });
                    },
                    subjectRows(subject) {
                        return [
                            { label: 'ที่เลือก', value: subject.schoolAvg, barClass: 'bg-sky-500' },
                            { label: 'เขตพื้นที่', value: subject.areaAvg, barClass: 'bg-orange-500' },
                        ];
                    },
                    formatNumber(value) { return new Intl.NumberFormat('th-TH').format(Number(value || 0)); },
                    formatValue(value) { return Number.isInteger(Number(value)) ? this.formatNumber(value) : this.formatScore(value); },
                    formatScore(value) {
                        return Number(value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    signedScore(value) {
                        const numeric = Number(value || 0);
                        return (numeric > 0 ? '+' : '') + this.formatScore(numeric);
                    },
                    scorePercent(value) { return Math.max(0, Math.min(100, Number(value || 0))); },
                    get trendYears() {
                        return (this.dashboard?.trend?.years || []).slice().sort((a, b) => a - b);
                    },
                    get chartSeries() {
                        const series = this.dashboard?.trend?.series || [];
                        const years = this.trendYears;
                        if (!series.length || !years.length) return [];
                        const minX = 70, maxX = 930, minY = 24, maxY = 280;
                        return series.map((item, index) => {
                            const color = this.chartColors[index % this.chartColors.length];
                            const points = item.points.slice().sort((a, b) => a.year - b.year).map(point => {
                                const x = years.length === 1 ? 500 : minX + ((point.year - years[0]) / Math.max(years[years.length - 1] - years[0], 1)) * (maxX - minX);
                                const y = maxY - (this.scorePercent(point.schoolAvg) / 100) * (maxY - minY);
                                return { ...point, x, y, color };
                            });
                            return { ...item, color, points };
                        });
                    },
                    seriesSegments(series) {
                        const points = series?.points || [];
                        if (points.length === 1) {
                            const point = points[0];
                            return [{ key: `${series.subjectCode}-${point.year}-single`, x1: Math.max(point.x - 28, 0), y1: point.y, x2: Math.min(point.x + 28, 1000), y2: point.y }];
                        }
                        return points.slice(0, -1).map((point, index) => {
                            const next = points[index + 1];
                            return { key: `${series.subjectCode}-${point.year}-${next.year}`, x1: point.x, y1: point.y, x2: next.x, y2: next.y };
                        });
                    },
                };
            }
        </script>
    @endpush
</x-layout>

