<x-layout>
    <x-slot:title>{{ $pageBreadcrumb }} | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="schoolSizePage()" x-init="init()">
        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="{{ url('/') }}" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">{{ $pageBreadcrumb }}</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">{{ $pageTitle }}</h2>
                <p class="text-slate-500 text-sm mt-1">แสดงรายชื่อโรงเรียนและจำนวนนักเรียนตามปีการศึกษาและรอบข้อมูลที่เลือก</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <select x-model="filters.academic_year" @change="fetchOptionsAndSchools()" class="form-input min-w-[180px]">
                    <option value="">เลือกปีการศึกษา</option>
                    <template x-for="year in availableYears" :key="year">
                        <option :value="String(year)" x-text="'ปีการศึกษา ' + year"></option>
                    </template>
                </select>
                <select x-model="filters.term" @change="fetchSchools()" class="form-input min-w-[140px]">
                    <option value="">เลือกรอบ</option>
                    <template x-for="termOption in availableTerms" :key="termOption">
                        <option :value="String(termOption)" x-text="'รอบ ' + termOption"></option>
                    </template>
                </select>
                <a :href="exportLink()"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-xs font-extrabold hover:bg-emerald-700 transition whitespace-nowrap">
                    <i class="fa-solid fa-file-excel"></i>
                    Export XLSX
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase">{{ $summaryLabel }}</div>
                <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ $sizeLabel }}</div>
                @if ($showSizeLinks)
                    <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-bold text-slate-500">
                        <a :href="pageFilterLink('all')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition">{{ $allLabel }}</a>
                        <a :href="pageFilterLink('small')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition">เล็ก</a>
                        <a :href="pageFilterLink('medium')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition">กลาง</a>
                        <a :href="pageFilterLink('large')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition">ใหญ่</a>
                        <a :href="pageFilterLink('special')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition">ใหญ่พิเศษ</a>
                    </div>
                @endif
            </div>
            <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase">จำนวนโรงเรียน</div>
                <div class="mt-2 text-2xl font-extrabold text-slate-800" x-text="formatNumber(summary.schools)"></div>
            </div>
            <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase">นักเรียนรวม</div>
                <div class="mt-2 text-2xl font-extrabold text-slate-800" x-text="formatNumber(summary.students)"></div>
            </div>
        </div>

        <div x-show="loading" class="py-24 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังโหลดรายชื่อโรงเรียน...</span>
        </div>

        <div x-show="!loading && schools.length === 0" class="bg-white border border-dashed border-slate-200 rounded-3xl p-12 text-center" x-cloak>
            <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-school"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">{{ $emptyTitle }}</h3>
            <p class="text-xs text-slate-400 mt-2">ลองเปลี่ยนปีการศึกษาหรือรอบข้อมูลอีกครั้ง</p>
        </div>

        <section x-show="!loading && schools.length > 0" class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden" x-cloak>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
                <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-ul text-orange-500"></i> รายชื่อโรงเรียน
                </h3>
                <span class="text-[10px] font-bold text-slate-400" x-text="selectedLabel()"></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold">
                            <th class="px-4 py-3">โรงเรียน</th>
                            <th class="px-4 py-3">เครือข่าย</th>
                            <th class="px-4 py-3">อำเภอ</th>
                            <th class="px-4 py-3 text-right">นักเรียน</th>
                            <th class="px-4 py-3 text-right">ห้อง</th>
                            <th class="px-4 py-3 text-center">แนวโน้ม</th>
                            <th class="px-4 py-3 text-center">ข้อมูลนักเรียน</th>
                            <th class="px-4 py-3 text-center">ข้อมูลโรงเรียน</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="school in schools" :key="school.school_smis">
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <template x-if="school.logo_url">
                                            <img :src="school.logo_url" :alt="school.schoolname" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                        </template>
                                        <div x-show="!school.logo_url" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-school text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-800 truncate" x-text="school.schoolname || 'ไม่พบชื่อโรงเรียนในระบบ'"></div>
                                            <div class="text-[10px] text-slate-400 mt-1" x-text="'SMIS ' + school.school_smis"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-500" x-text="school.schoolgroup_name || '-'"></td>
                                <td class="px-4 py-3 text-slate-500" x-text="school.amper || '-'"></td>
                                <td class="px-4 py-3 text-right font-extrabold text-slate-700" x-text="formatNumber(school.student_total)"></td>
                                <td class="px-4 py-3 text-right text-slate-500" x-text="formatNumber(school.room_total)"></td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            @click="openTrend(school)"
                                            class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:bg-orange-50 hover:text-orange-600 transition"
                                            title="ดูแนวโน้มนักเรียนรายปี">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            @click="openStudentDetail(school)"
                                            class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:bg-sky-50 hover:text-sky-600 transition"
                                            title="ดูข้อมูลนักเรียนแยกชั้น">
                                        <i class="fa-solid fa-table-list"></i>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            @click="openSchoolInfo(school)"
                                            class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition"
                                            title="ดูข้อมูลโรงเรียน">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <div x-show="trendModal.open"
             x-transition.opacity
             class="fixed inset-0 z-[9998] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center px-4"
             x-cloak>
            <div @click.outside="closeTrend()"
                 class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                    <div class="min-w-0 flex items-center gap-3">
                        <template x-if="trendModal.school?.logo_url">
                            <img :src="trendModal.school.logo_url" :alt="trendModal.school.schoolname" class="w-11 h-11 rounded-2xl object-contain bg-white border border-slate-100 p-1.5 shrink-0">
                        </template>
                        <div x-show="!trendModal.school?.logo_url" class="w-11 h-11 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-school"></i>
                        </div>
                        <div class="min-w-0">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase">แนวโน้มนักเรียนรายปี</div>
                        <h3 class="text-lg font-extrabold text-slate-800 truncate mt-1" x-text="trendModal.school?.schoolname || 'โรงเรียน'"></h3>
                        <p class="text-xs text-slate-400 mt-1">
                            <span x-text="'SMIS ' + (trendModal.school?.school_smis || trendModal.school?.smis || '-')"></span>
                            <span class="mx-1">/</span>
                            <span x-text="trendModal.school?.schoolgroup_name || '-'"></span>
                        </p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeTrend()"
                            class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div x-show="trendModal.loading" class="py-20 flex flex-col items-center justify-center gap-3">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
                        <span class="text-xs font-extrabold text-slate-400">กำลังโหลดแนวโน้มรายปี...</span>
                    </div>

                    <div x-show="!trendModal.loading && trendModal.points.length === 0" class="py-16 text-center">
                        <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-700">ยังไม่มีข้อมูลแนวโน้ม</h4>
                        <p class="text-xs text-slate-400 mt-2">ต้องมีข้อมูล SchoolMIS อย่างน้อยหนึ่งปีการศึกษา</p>
                    </div>

                    <div x-show="!trendModal.loading && trendModal.points.length > 0" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ปีแรก</div>
                                <div class="text-lg font-extrabold text-slate-800 mt-1" x-text="trendModal.summary.first ? trendModal.summary.first.academic_year : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="trendModal.summary.first ? formatNumber(trendModal.summary.first.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ปีล่าสุด</div>
                                <div class="text-lg font-extrabold text-slate-800 mt-1" x-text="trendModal.summary.latest ? trendModal.summary.latest.academic_year : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="trendModal.summary.latest ? formatNumber(trendModal.summary.latest.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">เพิ่ม/ลด</div>
                                <div class="text-lg font-extrabold mt-1" :class="trendChangeClass()" x-text="signedNumber(trendModal.summary.change) + ' คน'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="signedNumber(trendModal.summary.changePercent) + '%'"></div>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">จำนวนปี</div>
                                <div class="text-lg font-extrabold text-slate-800 mt-1" x-text="formatNumber(trendModal.points.length)"></div>
                                <div class="text-xs text-slate-500 mt-1">ใช้รอบล่าสุดของแต่ละปี</div>
                            </div>
                        </div>

                        <div class="border border-slate-100 rounded-3xl p-4">
                            <svg x-ref="schoolTrendChart"
                                 viewBox="0 0 720 280"
                                 class="w-full h-[280px]"
                                 role="img"
                                 @mousemove="handleTrendHover($event)"
                                 @mouseleave="hideTrendTooltip()">
                                <line x1="48" y1="28" x2="48" y2="230" stroke="#e2e8f0" stroke-width="2"></line>
                                <line x1="48" y1="230" x2="690" y2="230" stroke="#e2e8f0" stroke-width="2"></line>
                                <polyline :points="trendLinePoints()"
                                          fill="none"
                                          stroke="#f97316"
                                          stroke-width="4"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          pointer-events="none"></polyline>
                                <template x-for="point in trendModal.chartPoints" :key="point.academic_year">
                                    <g>
                                        <circle :cx="point.x" :cy="point.y" r="16" fill="transparent"
                                                class="cursor-pointer"
                                                tabindex="0"
                                                @mouseenter="showTrendTooltip(point)"
                                                @focus="showTrendTooltip(point)"
                                                @blur="hideTrendTooltip()"></circle>
                                        <circle :cx="point.x" :cy="point.y" :r="trendModal.tooltip.point?.academic_year === point.academic_year ? 8 : 6" fill="#f97316" stroke="#fff" stroke-width="3" class="pointer-events-none"></circle>
                                        <text :x="point.x" y="255" text-anchor="middle" class="fill-slate-500 text-[11px] font-bold" x-text="point.academic_year"></text>
                                        <text :x="point.x" :y="point.y - 14" text-anchor="middle" class="fill-slate-700 text-[11px] font-bold" x-text="formatNumber(point.student_total)"></text>
                                    </g>
                                </template>
                                <g x-show="trendModal.tooltip.show" x-cloak>
                                    <rect :x="trendTooltipBox().x" :y="trendTooltipBox().y" width="164" height="64" rx="12" fill="#0f172a" opacity="0.95"></rect>
                                    <text :x="trendTooltipBox().x + 14" :y="trendTooltipBox().y + 24" class="fill-white text-[12px] font-bold" x-text="trendTooltipYear()"></text>
                                    <text :x="trendTooltipBox().x + 14" :y="trendTooltipBox().y + 44" class="fill-orange-100 text-[12px] font-bold" x-text="trendTooltipStudents()"></text>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="studentDetailModal.open"
             x-transition.opacity
             class="fixed inset-0 z-[9999] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center px-4"
             x-cloak>
            <div @click.outside="closeStudentDetail()"
                 class="bg-white w-full max-w-5xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase">ข้อมูลนักเรียนแยกตามชั้น</div>
                        <h3 class="text-lg font-extrabold text-slate-800 truncate mt-1" x-text="studentDetailModal.school?.schoolname || 'โรงเรียน'"></h3>
                        <p class="text-xs text-slate-400 mt-1">
                            <span x-text="'SMIS ' + (studentDetailModal.school?.school_smis || '-')"></span>
                            <span class="mx-1">/</span>
                            <span x-text="studentDetailModal.school ? 'ปี ' + studentDetailModal.school.academic_year + ' รอบ ' + studentDetailModal.school.term : selectedLabel()"></span>
                        </p>
                    </div>
                    <button type="button"
                            @click="closeStudentDetail()"
                            class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div x-show="studentDetailModal.loading" class="py-20 flex flex-col items-center justify-center gap-3">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-sky-500"></i>
                        <span class="text-xs font-extrabold text-slate-400">กำลังโหลดข้อมูลนักเรียนแยกชั้น...</span>
                    </div>

                    <div x-show="!studentDetailModal.loading && !studentDetailModal.school" class="py-16 text-center">
                        <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                            <i class="fa-solid fa-table-list"></i>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-700">ไม่พบข้อมูลนักเรียน</h4>
                        <p class="text-xs text-slate-400 mt-2">ลองตรวจสอบปีการศึกษาหรือรอบข้อมูลอีกครั้ง</p>
                    </div>

                    <div x-show="!studentDetailModal.loading && studentDetailModal.school" class="space-y-5">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ห้องเรียน</div>
                                <div class="text-2xl font-extrabold text-slate-800 mt-1" x-text="formatNumber(studentDetailModal.summary.rooms)"></div>
                            </div>
                            <div class="bg-sky-50 border border-sky-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-sky-500 uppercase">ชาย</div>
                                <div class="text-2xl font-extrabold text-sky-700 mt-1" x-text="formatNumber(studentDetailModal.summary.male)"></div>
                            </div>
                            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-rose-500 uppercase">หญิง</div>
                                <div class="text-2xl font-extrabold text-rose-700 mt-1" x-text="formatNumber(studentDetailModal.summary.female)"></div>
                            </div>
                            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-orange-500 uppercase">รวม</div>
                                <div class="text-2xl font-extrabold text-orange-700 mt-1" x-text="formatNumber(studentDetailModal.summary.total)"></div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
                                <h4 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-table-list text-sky-500"></i> รายชั้นเรียน
                                </h4>
                                <span class="text-[10px] font-bold text-slate-400">แสดงเฉพาะชั้นที่มีข้อมูล</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-500 font-bold">
                                            <th class="px-4 py-3">ชั้น</th>
                                            <th class="px-4 py-3 text-right">ห้องเรียน</th>
                                            <th class="px-4 py-3 text-right">ชาย</th>
                                            <th class="px-4 py-3 text-right">หญิง</th>
                                            <th class="px-4 py-3 text-right">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <template x-for="row in studentDetailModal.gradeRows" :key="row.key">
                                            <tr>
                                                <td class="px-4 py-3 font-extrabold text-slate-700" x-text="row.label"></td>
                                                <td class="px-4 py-3 text-right text-slate-500" x-text="formatNumber(row.rooms)"></td>
                                                <td class="px-4 py-3 text-right text-sky-600 font-bold" x-text="formatNumber(row.male)"></td>
                                                <td class="px-4 py-3 text-right text-rose-600 font-bold" x-text="formatNumber(row.female)"></td>
                                                <td class="px-4 py-3 text-right text-slate-800 font-extrabold" x-text="formatNumber(row.total)"></td>
                                            </tr>
                                        </template>
                                        <tr class="bg-orange-50/70">
                                            <td class="px-4 py-3 font-extrabold text-orange-700">รวมทั้งหมด</td>
                                            <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(studentDetailModal.summary.rooms)"></td>
                                            <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(studentDetailModal.summary.male)"></td>
                                            <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(studentDetailModal.summary.female)"></td>
                                            <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(studentDetailModal.summary.total)"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div x-show="studentDetailModal.levelRows.length > 0" class="bg-slate-50 border border-slate-100 rounded-3xl p-5">
                            <h4 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-layer-group text-indigo-500"></i> สรุปตามช่วงชั้น
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="row in studentDetailModal.levelRows" :key="row.key">
                                    <div class="bg-white border border-slate-100 rounded-2xl p-4">
                                        <div class="font-extrabold text-xs text-slate-700" x-text="row.label"></div>
                                        <div class="flex justify-between text-[11px] text-slate-500 mt-2">
                                            <span x-text="'ห้อง ' + formatNumber(row.rooms)"></span>
                                            <span x-text="'ชาย ' + formatNumber(row.male)"></span>
                                            <span x-text="'หญิง ' + formatNumber(row.female)"></span>
                                            <span class="font-extrabold text-slate-800" x-text="'รวม ' + formatNumber(row.total)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="schoolInfoModal.open"
             x-transition.opacity
             class="fixed inset-0 z-[9999] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center px-4"
             x-cloak>
            <div @click.outside="closeSchoolInfo()"
                 class="bg-white w-full max-w-4xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase">ข้อมูลโรงเรียน</div>
                        <h3 class="text-lg font-extrabold text-slate-800 truncate mt-1" x-text="schoolInfoModal.school?.schoolname || 'โรงเรียน'"></h3>
                        <p class="text-xs text-slate-400 mt-1">
                            <span x-text="'SMIS ' + (schoolInfoModal.school?.smis || '-')"></span>
                            <span class="mx-1">/</span>
                            <span x-text="schoolInfoModal.school?.schoolgroup_name || '-'"></span>
                        </p>
                    </div>
                    <button type="button"
                            @click="closeSchoolInfo()"
                            class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div x-show="schoolInfoModal.loading" class="py-20 flex flex-col items-center justify-center gap-3">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-500"></i>
                        <span class="text-xs font-extrabold text-slate-400">กำลังโหลดข้อมูลโรงเรียน...</span>
                    </div>

                    <div x-show="!schoolInfoModal.loading && !schoolInfoModal.school" class="py-16 text-center">
                        <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-700">ไม่พบข้อมูลโรงเรียน</h4>
                    </div>

                    <div x-show="!schoolInfoModal.loading && schoolInfoModal.school" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">เครือข่าย</div>
                                <div class="text-xl font-extrabold text-slate-800 mt-1" x-text="schoolInfoModal.school?.schoolgroup_name || '-'"></div>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ระยะทางถึงเขต</div>
                                <div class="text-xl font-extrabold text-slate-800 mt-1" x-text="schoolInfoModal.school?.length_km ? schoolInfoModal.school.length_km + ' กม.' : '-'"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-4">
                            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-100">
                                    <h4 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                        <i class="fa-solid fa-school text-emerald-500"></i> รายละเอียด
                                    </h4>
                                </div>
                                <div class="divide-y divide-slate-50 text-xs">
                                    <template x-for="item in schoolInfoRows()" :key="item.label">
                                        <div class="grid grid-cols-[150px_minmax(0,1fr)] gap-4 px-5 py-3">
                                            <div class="font-bold text-slate-400" x-text="item.label"></div>
                                            <div class="font-bold text-slate-700 break-words" x-text="item.value || '-'"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-5 space-y-4">
                                <h4 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-rose-500"></i> พิกัด
                                </h4>
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between gap-3">
                                        <span class="text-slate-400 font-bold">Latitude</span>
                                        <span class="font-extrabold text-slate-700" x-text="schoolInfoModal.school?.lat || '-'"></span>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <span class="text-slate-400 font-bold">Longitude</span>
                                        <span class="font-extrabold text-slate-700" x-text="schoolInfoModal.school?.lng || '-'"></span>
                                    </div>
                                </div>
                                <template x-if="schoolInfoModal.school?.maplink">
                                    <a :href="schoolInfoModal.school.maplink"
                                       target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-xs font-extrabold hover:bg-emerald-700 transition">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                        เปิดแผนที่
                                    </a>
                                </template>
                                <template x-if="schoolInfoModal.school?.website">
                                    <a :href="websiteUrl(schoolInfoModal.school.website)"
                                       target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-extrabold hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-globe"></i>
                                        เว็บไซต์โรงเรียน
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
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
            function schoolSizePage() {
                return {
                    loading: true,
                    availableYears: [],
                    availableTerms: [],
                    schools: [],
                    summary: {
                        schools: 0,
                        students: 0
                    },
                    trendModal: {
                        open: false,
                        loading: false,
                        school: null,
                        points: [],
                        chartPoints: [],
                        tooltip: {
                            show: false,
                            x: 0,
                            y: 0,
                            point: null
                        },
                        summary: {
                            first: null,
                            latest: null,
                            change: 0,
                            changePercent: 0
                        }
                    },
                    studentDetailModal: {
                        open: false,
                        loading: false,
                        school: null,
                        gradeRows: [],
                        levelRows: [],
                        summary: {
                            rooms: 0,
                            male: 0,
                            female: 0,
                            total: 0
                        }
                    },
                    schoolInfoModal: {
                        open: false,
                        loading: false,
                        school: null
                    },
                    filters: {
                        academic_year: @js($academicYear),
                        term: @js($term > 0 ? (string) $term : '')
                    },
                    size: @js($size),
                    sizeLabel: @js($sizeLabel),
                    isAll: @js($isAll),
                    filterType: @js($filterType),
                    filterValue: @js($filterValue),
                    allRoute: @js($allRoute),
                    sizeRoutePrefix: @js($sizeRoutePrefix),

                    init() {
                        this.fetchOptionsAndSchools();
                    },

                    fetchOptionsAndSchools() {
                        axios.get('{{ route("api.dashboard.stats") }}', {
                            params: {
                                academic_year: this.filters.academic_year || '',
                                term: this.filters.term || ''
                            }
                        }).then((response) => {
                            if (response.data.status !== 'success') {
                                return;
                            }

                            const data = response.data.data;
                            this.availableYears = data.availableYears || [];
                            this.availableTerms = data.availableTerms || [];
                            this.filters.academic_year = data.selectedYear ? String(data.selectedYear) : '';
                            this.filters.term = data.selectedTerm ? String(data.selectedTerm) : '';
                            this.fetchSchools();
                        }).catch(() => {
                            this.loading = false;
                        });
                    },

                    fetchSchools() {
                        this.loading = true;
                        axios.get('{{ route("api.dashboard.drilldown") }}', {
                            params: {
                                academic_year: this.filters.academic_year || '',
                                term: this.filters.term || '',
                                type: this.filterType,
                                value: this.filterValue
                            }
                        }).then((response) => {
                            this.schools = response.data.data || [];
                            this.summary.schools = this.schools.length;
                            this.summary.students = this.schools.reduce((total, school) => total + Number(school.student_total || 0), 0);
                            this.syncUrl();
                        }).catch(() => {
                            this.schools = [];
                            this.summary = { schools: 0, students: 0 };
                        }).finally(() => {
                            this.loading = false;
                        });
                    },

                    openTrend(school) {
                        this.trendModal.open = true;
                        this.trendModal.loading = true;
                        this.trendModal.school = school;
                        this.trendModal.points = [];
                        this.trendModal.chartPoints = [];
                        this.hideTrendTooltip();
                        this.trendModal.summary = {
                            first: null,
                            latest: null,
                            change: 0,
                            changePercent: 0
                        };

                        axios.get('{{ route("api.dashboard.school-trend") }}', {
                            params: {
                                school_smis: school.school_smis
                            }
                        }).then((response) => {
                            const data = response.data.data || {};
                            this.trendModal.school = {
                                ...school,
                                ...(data.school || {})
                            };
                            this.trendModal.points = data.points || [];
                            this.trendModal.summary = data.summary || this.trendModal.summary;
                            this.trendModal.chartPoints = this.buildChartPoints(this.trendModal.points);
                        }).catch(() => {
                            this.trendModal.points = [];
                            this.trendModal.chartPoints = [];
                            this.hideTrendTooltip();
                        }).finally(() => {
                            this.trendModal.loading = false;
                        });
                    },

                    closeTrend() {
                        this.trendModal.open = false;
                    },

                    openStudentDetail(school) {
                        this.studentDetailModal.open = true;
                        this.studentDetailModal.loading = true;
                        this.studentDetailModal.school = school;
                        this.studentDetailModal.gradeRows = [];
                        this.studentDetailModal.levelRows = [];
                        this.studentDetailModal.summary = {
                            rooms: 0,
                            male: 0,
                            female: 0,
                            total: 0
                        };

                        axios.get('{{ route("api.dashboard.school-student-detail") }}', {
                            params: {
                                school_smis: school.school_smis,
                                academic_year: this.filters.academic_year || '',
                                term: this.filters.term || ''
                            }
                        }).then((response) => {
                            const data = response.data.data || {};
                            this.studentDetailModal.school = data.school;
                            this.studentDetailModal.gradeRows = data.gradeRows || [];
                            this.studentDetailModal.levelRows = data.levelRows || [];
                            this.studentDetailModal.summary = data.summary || this.studentDetailModal.summary;
                        }).catch(() => {
                            this.studentDetailModal.school = null;
                            this.studentDetailModal.gradeRows = [];
                            this.studentDetailModal.levelRows = [];
                        }).finally(() => {
                            this.studentDetailModal.loading = false;
                        });
                    },

                    closeStudentDetail() {
                        this.studentDetailModal.open = false;
                    },

                    openSchoolInfo(school) {
                        this.schoolInfoModal.open = true;
                        this.schoolInfoModal.loading = true;
                        this.schoolInfoModal.school = null;

                        axios.get('{{ route("api.dashboard.school-info") }}', {
                            params: {
                                school_smis: school.school_smis
                            }
                        }).then((response) => {
                            this.schoolInfoModal.school = response.data.data || null;
                        }).catch(() => {
                            this.schoolInfoModal.school = null;
                        }).finally(() => {
                            this.schoolInfoModal.loading = false;
                        });
                    },

                    closeSchoolInfo() {
                        this.schoolInfoModal.open = false;
                    },

                    schoolInfoRows() {
                        const school = this.schoolInfoModal.school || {};

                        return [
                            { label: 'ชื่อโรงเรียน', value: school.schoolname },
                            { label: 'ชื่ออังกฤษ', value: school.schoolname_eng },
                            { label: 'SMIS', value: school.smis },
                            { label: 'PERCODE', value: school.percode },
                            { label: 'เครือข่าย', value: school.schoolgroup_name },
                            { label: 'ที่อยู่', value: school.full_address },
                            { label: 'ตำบล', value: school.tambon },
                            { label: 'อำเภอ', value: school.amper },
                            { label: 'จังหวัด', value: school.province },
                            { label: 'รหัสไปรษณีย์', value: school.postcode },
                            { label: 'โทรศัพท์', value: school.tel },
                            { label: 'อีเมล', value: school.email },
                            { label: 'เว็บไซต์', value: school.website }
                        ];
                    },

                    websiteUrl(value) {
                        if (!value) {
                            return '#';
                        }

                        return /^https?:\/\//i.test(value) ? value : 'https://' + value;
                    },

                    pageFilterLink(size) {
                        const params = new URLSearchParams();

                        if (this.filters.academic_year) {
                            params.set('academic_year', this.filters.academic_year);
                        }

                        if (this.filters.term) {
                            params.set('term', this.filters.term);
                        }

                        const baseUrl = size === 'all'
                            ? this.allRoute
                            : this.sizeRoutePrefix + '/' + size;

                        const query = params.toString();
                        return query ? (baseUrl + '?' + query) : baseUrl;
                    },

                    exportLink() {
                        const params = new URLSearchParams();

                        if (this.filters.academic_year) {
                            params.set('academic_year', this.filters.academic_year);
                        }

                        if (this.filters.term) {
                            params.set('term', this.filters.term);
                        }

                        params.set('type', this.filterType);
                        params.set('value', this.filterValue);

                        return '{{ route('dashboard.schools.export') }}' + '?' + params.toString();
                    },

                    buildChartPoints(points) {
                        if (!points.length) {
                            return [];
                        }

                        const values = points.map((point) => Number(point.student_total || 0));
                        const min = Math.min(...values);
                        const max = Math.max(...values);
                        const span = Math.max(1, max - min);
                        const left = 64;
                        const width = 596;
                        const top = 36;
                        const height = 178;
                        const step = points.length > 1 ? width / (points.length - 1) : 0;

                        return points.map((point, index) => {
                            const value = Number(point.student_total || 0);
                            const x = points.length > 1 ? left + (step * index) : left + (width / 2);
                            const y = top + height - (((value - min) / span) * height);

                            return {
                                ...point,
                                x,
                                y
                            };
                        });
                    },

                    trendLinePoints() {
                        return this.trendModal.chartPoints
                            .map((point) => point.x + ',' + point.y)
                            .join(' ');
                    },

                    showTrendTooltip(point) {
                        this.trendModal.tooltip = {
                            show: true,
                            x: point.x,
                            y: point.y,
                            point
                        };
                    },

                    handleTrendHover(event) {
                        const points = this.trendModal.chartPoints;

                        if (!Array.isArray(points) || points.length === 0) {
                            this.hideTrendTooltip();
                            return;
                        }

                        const svg = this.$refs.schoolTrendChart;

                        if (!svg) {
                            return;
                        }

                        const rect = svg.getBoundingClientRect();

                        if (!rect.width) {
                            return;
                        }

                        const relativeX = ((event.clientX - rect.left) / rect.width) * 720;
                        let nearestPoint = points[0];
                        let nearestDistance = Math.abs(relativeX - points[0].x);

                        points.forEach((point) => {
                            const distance = Math.abs(relativeX - point.x);

                            if (distance < nearestDistance) {
                                nearestPoint = point;
                                nearestDistance = distance;
                            }
                        });

                        this.showTrendTooltip(nearestPoint);
                    },

                    hideTrendTooltip() {
                        this.trendModal.tooltip = {
                            show: false,
                            x: 0,
                            y: 0,
                            point: null
                        };
                    },

                    trendTooltipBox() {
                        const tooltip = this.trendModal.tooltip;
                        const x = Math.max(54, Math.min(502, Number(tooltip.x || 0) - 82));
                        const y = Math.max(16, Number(tooltip.y || 0) - 88);

                        return { x, y };
                    },

                    trendTooltipYear() {
                        const point = this.trendModal.tooltip.point;

                        if (!point) {
                            return '';
                        }

                        return 'ปีการศึกษา ' + point.academic_year + ' / รอบ ' + point.term;
                    },

                    trendTooltipStudents() {
                        const point = this.trendModal.tooltip.point;

                        if (!point) {
                            return '';
                        }

                        return 'นักเรียน ' + this.formatNumber(point.student_total) + ' คน';
                    },

                    syncUrl() {
                        const url = new URL(window.location.href);
                        if (this.filters.academic_year) {
                            url.searchParams.set('academic_year', this.filters.academic_year);
                        } else {
                            url.searchParams.delete('academic_year');
                        }

                        if (this.filters.term) {
                            url.searchParams.set('term', this.filters.term);
                        } else {
                            url.searchParams.delete('term');
                        }

                        window.history.replaceState({}, '', url.toString());
                    },

                    selectedLabel() {
                        if (!this.filters.academic_year || !this.filters.term) {
                            return '';
                        }

                        return 'ปี ' + this.filters.academic_year + ' / รอบ ' + this.filters.term;
                    },

                    formatNumber(value) {
                        return Number(value || 0).toLocaleString('th-TH');
                    },

                    signedNumber(value) {
                        const numeric = Number(value || 0);
                        const prefix = numeric > 0 ? '+' : '';

                        return prefix + numeric.toLocaleString('th-TH');
                    },

                    trendChangeClass() {
                        const change = Number(this.trendModal.summary.change || 0);

                        if (change > 0) {
                            return 'text-emerald-600';
                        }

                        if (change < 0) {
                            return 'text-rose-600';
                        }

                        return 'text-slate-800';
                    }
                };
            }
        </script>
    @endpush
</x-layout>

