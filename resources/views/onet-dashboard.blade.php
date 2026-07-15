<x-layout>
    <x-slot:title>ผลการทดสอบระดับชาติ ONET | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="onetDashboard()" x-init="init()">
        <header class="mb-8 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="/" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ผลการทดสอบระดับชาติ</span>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ONET</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ผลการทดสอบระดับชาติ ONET</h2>
                <p class="text-slate-500 text-sm mt-1">
                    แสดงข้อมูล O-NET ของ สพป.ชุมพร เขต 1 โดยอ่านจากฐานข้อมูลภายในที่นำเข้ามาจาก HRMS
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-extrabold text-slate-600">
                    <i class="fa-solid fa-location-dot text-orange-500 mr-2"></i>
                    สพป.ชุมพร เขต 1
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
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">ระดับชั้น</label>
                    <select class="form-input w-full" x-model="filters.grade" @change="onGradeChange()">
                        <template x-for="option in gradeOptions" :key="option.code">
                            <option :value="option.code" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">ปีการศึกษา</label>
                    <select class="form-input w-full" x-model="filters.year" @change="fetchDashboard()">
                        <template x-for="year in availableYears" :key="year">
                            <option :value="year" x-text="year"></option>
                        </template>
                    </select>
                </div>
                <div class="md:col-span-1 xl:col-span-2">
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
                                    <div class="px-4 py-6 text-center text-xs font-bold text-slate-400">
                                        ไม่พบโรงเรียนที่ค้นหา
                                    </div>
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
                                        <i x-show="filters.schoolCode === school.schoolCode"
                                           class="fa-solid fa-check text-orange-500 text-xs mt-1 shrink-0"
                                           x-cloak></i>
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
            <span class="text-xs font-extrabold text-slate-400">กำลังดึงข้อมูล O-NET...</span>
        </div>

        <div x-show="!loading && error" class="bg-white border border-rose-100 rounded-3xl p-10 text-center" x-cloak>
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ไม่สามารถดึงข้อมูล O-NET ได้</h3>
            <p class="text-xs text-slate-400 mt-2" x-text="error"></p>
        </div>

        <div x-show="!loading && !error && dashboard" class="space-y-6" x-cloak>
            <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <template x-for="item in dashboard.overview" :key="item.label">
                    <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg shrink-0"
                             :class="item.iconBg">
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
                                <i class="fa-solid fa-file-lines text-sky-500"></i> คะแนนรายวิชา
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-1">เทียบโรงเรียน จังหวัด ภาค และประเทศ ในปีที่เลือก</p>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-extrabold text-slate-400 uppercase" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.schoolType : '-'"></div>
                            <div class="text-xs font-bold text-slate-500 mt-1" x-text="dashboard.selectedSchool ? ('สูงสุด ' + dashboard.selectedSchool.maxClassLevel) : '-'"></div>
                        </div>
                    </div>

                    <div x-show="dashboard.subjects.length === 0" class="py-14 text-center text-sm text-slate-400 font-bold">
                        ไม่พบข้อมูล O-NET สำหรับปีและระดับชั้นที่เลือก
                    </div>

                    <div x-show="dashboard.subjects.length > 0" class="space-y-4" x-cloak>
                        <template x-for="subject in dashboard.subjects" :key="subject.subjectCode">
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-5">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                                    <div>
                                        <h4 class="text-sm font-extrabold text-slate-800" x-text="subject.subjectName"></h4>
                                        <p class="text-[11px] text-slate-400 mt-1" x-text="'ผู้เข้าสอบ ' + formatNumber(subject.studentCount) + ' คน'"></p>
                                    </div>
                                    <div class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-extrabold"
                                         :class="subject.diffFromProvince >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                        <i class="fa-solid mr-2" :class="subject.diffFromProvince >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'"></i>
                                        <span x-text="signedScore(subject.diffFromProvince) + ' จากจังหวัด'"></span>
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
                            <span class="rounded-2xl bg-orange-50 px-3 py-1.5 text-[10px] font-extrabold text-orange-600" x-text="gradeLabel(filters.grade)"></span>
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
                                    <div class="font-extrabold text-slate-800 min-w-0 truncate" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.schoolName : '-'"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">รหัส SMIS</div>
                                    <div class="mt-1 font-extrabold text-slate-800" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.smisCode : '-'"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">รหัส O-NET</div>
                                    <div class="mt-1 font-extrabold text-slate-800" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.schoolCode : '-'"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">อำเภอ</div>
                                    <div class="mt-1 font-extrabold text-slate-800" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.district : '-'"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ตำบล</div>
                                    <div class="mt-1 font-extrabold text-slate-800" x-text="dashboard.selectedSchool ? dashboard.selectedSchool.subdistrict : '-'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-chart-line text-violet-500"></i> แนวโน้มคะแนนรายปี
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-1">เส้นกราฟแสดงคะแนนเฉลี่ยของโรงเรียนแยกตามวิชา</p>
                            </div>
                        </div>

                        <div x-show="chartSeries.length === 0" class="py-12 text-center text-sm text-slate-400 font-bold">
                            ยังไม่พบข้อมูลย้อนหลังสำหรับสร้างกราฟ
                        </div>

                        <div x-show="chartSeries.length > 0" class="space-y-4" x-cloak>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="series in chartSeries" :key="series.subjectCode">
                                    <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2 text-[11px] font-bold text-slate-600">
                                        <span class="inline-block h-2.5 w-2.5 rounded-full" :style="'background:' + series.color"></span>
                                        <span x-text="series.subjectName"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                                <div class="relative h-[360px] overflow-hidden rounded-2xl" x-ref="trendChartBox">
                                    <div class="absolute inset-x-0 top-0 bottom-10 pointer-events-none">
                                        <template x-for="grid in [0, 1, 2, 3, 4]" :key="'onet-grid-' + grid">
                                            <div class="absolute inset-x-0 border-t border-dashed border-slate-200"
                                                 :style="'top: ' + (grid * 25) + '%'"></div>
                                        </template>
                                    </div>

                                    <svg class="absolute inset-0 w-full h-[320px] pointer-events-none"
                                         viewBox="0 0 1000 320"
                                         preserveAspectRatio="none"
                                         x-html="trendSvgMarkup()"></svg>

                                    <div class="absolute inset-0 h-[320px]"
                                         @mousemove="handleTrendHover($event)"
                                         @mouseleave="hideTrendTooltip()">
                                        <template x-for="series in chartSeries" :key="'points-' + series.subjectCode">
                                            <div>
                                                <template x-for="point in series.points" :key="series.subjectCode + '-' + point.year">
                                                    <button type="button"
                                                            class="absolute w-9 h-9 -translate-x-1/2 -translate-y-1/2 rounded-full bg-transparent cursor-pointer"
                                                            :style="pointHitStyle(point)"
                                                            @mouseenter="showTrendTooltip(point, series)"
                                                            @mouseleave="hideTrendTooltip()"
                                                            @focus="showTrendTooltip(point, series)"
                                                            @blur="hideTrendTooltip()"
                                                            aria-label="ดูข้อมูลจุดกราฟ"></button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-between px-2 text-[11px] font-extrabold text-slate-400">
                                        <template x-for="year in trendYears" :key="'axis-' + year">
                                            <span x-text="year"></span>
                                        </template>
                                    </div>

                                    <div x-show="tooltip"
                                         x-cloak
                                         class="absolute z-10 w-56 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-2xl pointer-events-none"
                                         :style="tooltipStyle()">
                                        <div class="text-[11px] font-extrabold" x-text="tooltip ? tooltip.subjectName : ''"></div>
                                        <div class="mt-2 text-xs text-slate-200" x-text="tooltip ? ('ปีการศึกษา ' + tooltip.year) : ''"></div>
                                        <div class="mt-1 text-xs" :style="'color:' + (tooltip ? tooltip.color : '#fff')" x-text="tooltip ? ('คะแนนเฉลี่ย ' + formatScore(tooltip.schoolAvg)) : ''"></div>
                                        <div class="mt-1 text-xs text-slate-300" x-text="tooltip ? ('จังหวัด ' + formatScore(tooltip.provinceAvg)) : ''"></div>
                                        <div class="mt-1 text-xs text-slate-300" x-text="tooltip ? ('ประเทศ ' + formatScore(tooltip.countryAvg)) : ''"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </aside>
            </div>

            <div class="text-[11px] text-slate-400">
                อัปเดตล่าสุดจาก API เวลา
                <span class="font-bold text-slate-500" x-text="formatDateTime(dashboard.fetchedAt)"></span>
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
        </style>
        <script>
            function onetDashboard() {
                return {
                    loading: true,
                    error: '',
                    dashboard: null,
                    gradeOptions: [],
                    availableYears: [],
                    schools: [],
                    schoolDropdownOpen: false,
                    schoolSearch: '',
                    filters: {
                        grade: 'P6',
                        year: '',
                        schoolCode: '',
                    },
                    tooltip: null,
                    tooltipPosition: { left: 0, top: 0 },
                    chartColors: ['#0ea5e9', '#f97316', '#8b5cf6', '#10b981', '#ef4444', '#14b8a6'],
                    init() {
                        this.fetchDashboard();
                    },
                    fetchDashboard(forceReload = false) {
                        this.loading = true;
                        this.error = '';

                        const params = new URLSearchParams();
                        params.set('grade', this.filters.grade || 'P6');
                        if (this.filters.year) {
                            params.set('year', this.filters.year);
                        }
                        if (this.filters.schoolCode) {
                            params.set('school_code', this.filters.schoolCode);
                        }
                        if (forceReload) {
                            params.set('_ts', Date.now());
                        }

                        axios.get('{{ route('api.onet.dashboard') }}', { params })
                            .then(({ data }) => {
                                this.dashboard = data;
                                this.gradeOptions = data.gradeOptions || [];
                                this.availableYears = data.availableYears || [];
                                this.schools = data.schools || [];
                                this.filters.grade = data.selectedGrade || this.filters.grade;
                                this.filters.year = String(data.selectedYear || '');
                                this.filters.schoolCode = data.selectedSchool?.schoolCode || '';
                                this.schoolSearch = '';
                            })
                            .catch((error) => {
                                this.error = error.response?.data?.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ API O-NET';
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    },
                    onGradeChange() {
                        this.filters.schoolCode = '';
                        this.schoolDropdownOpen = false;
                        this.fetchDashboard();
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
                    gradeLabel(code) {
                        const option = this.gradeOptions.find(item => item.code === code);
                        return option ? option.label : code;
                    },
                    get filteredSchools() {
                        const query = (this.schoolSearch || '').trim().toLowerCase();
                        if (!query) {
                            return this.schools;
                        }

                        return this.schools.filter((school) => {
                            const schoolName = String(school.schoolName || '').toLowerCase();
                            const schoolSmis = String(school.schoolSmis || '').toLowerCase();
                            const label = String(school.label || '').toLowerCase();

                            return schoolName.includes(query) || schoolSmis.includes(query) || label.includes(query);
                        });
                    },
                    formatNumber(value) {
                        return new Intl.NumberFormat('th-TH').format(Number(value || 0));
                    },
                    formatValue(value) {
                        return Number.isInteger(Number(value)) ? this.formatNumber(value) : this.formatScore(value);
                    },
                    formatScore(value) {
                        return Number(value || 0).toLocaleString('th-TH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                    signedScore(value) {
                        const numeric = Number(value || 0);
                        const prefix = numeric > 0 ? '+' : '';
                        return prefix + this.formatScore(numeric);
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
                    scorePercent(value) {
                        return Math.max(0, Math.min(100, Number(value || 0)));
                    },
                    subjectRows(subject) {
                        return [
                            { label: 'โรงเรียน', value: subject.schoolAvg, barClass: 'bg-sky-500' },
                            { label: 'จังหวัด', value: subject.provinceAvg, barClass: 'bg-orange-500' },
                            { label: 'ภาค', value: subject.regionalAvg, barClass: 'bg-violet-500' },
                            { label: 'ประเทศ', value: subject.countryAvg, barClass: 'bg-emerald-500' },
                        ];
                    },
                    get trendYears() {
                        return (this.dashboard?.trend?.years || []).slice().sort((a, b) => a - b);
                    },
                    get chartSeries() {
                        const series = this.dashboard?.trend?.series || [];
                        const years = this.trendYears;

                        if (!series.length || !years.length) {
                            return [];
                        }

                        const chartWidth = 1000;
                        const chartHeight = 320;
                        const minX = 70;
                        const maxX = 930;
                        const minY = 24;
                        const maxY = 280;

                        return series.map((item, index) => {
                            const color = this.chartColors[index % this.chartColors.length];
                            const points = item.points
                                .slice()
                                .sort((a, b) => a.year - b.year)
                                .map(point => {
                                    const x = years.length === 1
                                        ? chartWidth / 2
                                        : minX + ((point.year - years[0]) / Math.max(years[years.length - 1] - years[0], 1)) * (maxX - minX);
                                    const y = maxY - (Math.max(0, Math.min(100, Number(point.schoolAvg || 0))) / 100) * (maxY - minY);

                                    return {
                                        ...point,
                                        x,
                                        y,
                                        color,
                                    };
                                });

                            return {
                                ...item,
                                color,
                                points,
                            };
                        });
                    },
                    seriesSegments(series) {
                        const points = series?.points || [];
                        if (!points.length) {
                            return [];
                        }

                        if (points.length === 1) {
                            const point = points[0];
                            return [{
                                key: `${series.subjectCode}-${point.year}-single`,
                                x1: Math.max(point.x - 28, 0),
                                y1: point.y,
                                x2: Math.min(point.x + 28, 1000),
                                y2: point.y,
                            }];
                        }

                        return points.slice(0, -1).map((point, index) => {
                            const next = points[index + 1];
                            return {
                                key: `${series.subjectCode}-${point.year}-${next.year}`,
                                x1: point.x,
                                y1: point.y,
                                x2: next.x,
                                y2: next.y,
                            };
                        });
                    },
                    trendSvgMarkup() {
                        return this.chartSeries.map((series) => {
                            const lineMarkup = this.seriesSegments(series).map((segment) => {
                                return `<line x1="${segment.x1}" y1="${segment.y1}" x2="${segment.x2}" y2="${segment.y2}" stroke="${series.color}" stroke-width="4" stroke-linecap="round" />`;
                            }).join('');
                            return lineMarkup;
                        }).join('');
                    },
                    pointHitStyle(point) {
                        const leftPercent = (Number(point.x) / 1000) * 100;
                        const topPercent = (Number(point.y) / 320) * 100;
                        return [
                            `left:${leftPercent}%`,
                            `top:${topPercent}%`,
                        ].join(';');
                    },
                    showTrendTooltip(point, series) {
                        this.tooltip = {
                            ...point,
                            color: series.color,
                        };
                        this.positionTooltipFromPoint(point);
                    },
                    handleTrendHover(event) {
                        if (!this.tooltip) {
                            return;
                        }

                        const box = this.$refs.trendChartBox?.getBoundingClientRect();
                        if (!box) {
                            return;
                        }

                        this.tooltipPosition = {
                            left: event.clientX - box.left,
                            top: event.clientY - box.top,
                        };
                    },
                    hideTrendTooltip() {
                        this.tooltip = null;
                    },
                    positionTooltipFromPoint(point) {
                        const box = this.$refs.trendChartBox;
                        if (!box) {
                            return;
                        }

                        const width = box.clientWidth || 1;
                        const height = box.clientHeight || 1;

                        this.tooltipPosition = {
                            left: (point.x / 1000) * width,
                            top: (point.y / 320) * Math.max(height - 40, 1),
                        };
                    },
                    tooltipStyle() {
                        const box = this.$refs.trendChartBox;
                        if (!box) {
                            return '';
                        }

                        const tooltipWidth = 224;
                        const tooltipHeight = 120;
                        const padding = 16;
                        let left = this.tooltipPosition.left + 16;
                        let top = this.tooltipPosition.top - tooltipHeight - 12;

                        if (left + tooltipWidth > box.clientWidth - padding) {
                            left = box.clientWidth - tooltipWidth - padding;
                        }

                        if (left < padding) {
                            left = padding;
                        }

                        if (top < padding) {
                            top = this.tooltipPosition.top + 16;
                        }

                        if (top + tooltipHeight > box.clientHeight - padding) {
                            top = box.clientHeight - tooltipHeight - padding;
                        }

                        return `left:${left}px; top:${top}px;`;
                    },
                };
            }
        </script>
    @endpush
</x-layout>
