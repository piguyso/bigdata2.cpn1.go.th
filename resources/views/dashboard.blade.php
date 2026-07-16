<x-layout>
    <x-slot:title>แดชบอร์ดสารสนเทศ SchoolMIS | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="dashboardManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-cloak>
            <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
            <span x-text="toast.message"></span>
        </div>

        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="{{ url('/') }}" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">แดชบอร์ดสารสนเทศ SchoolMIS</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">แดชบอร์ดสารสนเทศ SchoolMIS</h2>
                <p class="text-slate-500 text-sm mt-1">
                    สรุปข้อมูลนักเรียนจากชุดนำเข้า SchoolMIS แยกตามปีการศึกษา รอบข้อมูล เครือข่าย และโรงเรียน
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <select x-model="filters.academic_year" @change="fetchStats()" class="form-input min-w-[180px]">
                    <option value="">เลือกปีการศึกษา</option>
                    <template x-for="year in stats.availableYears" :key="year">
                        <option :value="year" x-text="'ปีการศึกษา ' + year"></option>
                    </template>
                </select>
                <select x-model="filters.term" @change="fetchStats()" class="form-input min-w-[140px]">
                    <option value="">เลือกรอบ</option>
                    <template x-for="term in stats.availableTerms" :key="term">
                        <option :value="String(term)" x-text="'รอบ ' + term"></option>
                    </template>
                </select>
            </div>
        </header>

        <div x-show="loadingStats" class="py-28 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังประมวลผลสารสนเทศ SchoolMIS...</span>
        </div>

        <div x-show="!loadingStats && !stats.selectedYear" class="bg-white border border-dashed border-slate-200 rounded-3xl p-12 text-center" x-cloak>
            <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-database"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ยังไม่มีข้อมูล SchoolMIS สำหรับแสดงผล</h3>
            <p class="text-xs text-slate-400 mt-2">กรุณานำเข้าข้อมูลที่หน้า <a href="{{ route('admin.schoolmis.index') }}" class="text-orange-600 font-bold hover:underline">/admin/schoolmis</a> ก่อน</p>
        </div>

        <div x-show="!loadingStats && stats.selectedYear" class="space-y-6" x-cloak>
            <section class="grid grid-cols-2 xl:grid-cols-5 gap-4">
                <template x-for="item in overviewCards()" :key="item.label">
                    <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg shrink-0"
                             :class="item.iconBg">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-extrabold text-slate-400 uppercase" x-text="item.label"></span>
                            <template x-if="item.href">
                                <a :href="item.href" class="inline-block text-2xl font-extrabold text-slate-800 hover:text-orange-600 transition" x-text="formatNumber(item.value)"></a>
                            </template>
                            <template x-if="item.clickable">
                                <button type="button"
                                        @click="openStudentTrend()"
                                        class="text-2xl font-extrabold text-slate-800 hover:text-orange-600 transition">
                                    <span x-text="formatNumber(item.value)"></span>
                                </button>
                            </template>
                            <template x-if="!item.href && !item.clickable">
                                <span class="text-2xl font-extrabold text-slate-800" x-text="formatNumber(item.value)"></span>
                            </template>
                            <span class="text-[9px] text-slate-400 block mt-0.5" x-text="item.note"></span>
                            <template x-if="item.sizeBreakdown">
                                <div class="mt-2 flex flex-wrap gap-1.5 text-[9px] font-bold text-slate-500">
                                    <a :href="cardSizeLink(item, 'small')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'เล็ก ' + formatNumber(item.sizeBreakdown.small)"></a>
                                    <a :href="cardSizeLink(item, 'medium')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'กลาง ' + formatNumber(item.sizeBreakdown.medium)"></a>
                                    <a :href="cardSizeLink(item, 'large')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่ ' + formatNumber(item.sizeBreakdown.large)"></a>
                                    <a :href="cardSizeLink(item, 'special')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่พิเศษ ' + formatNumber(item.sizeBreakdown.special)"></a>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </section>

            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] gap-6">
                <section class="space-y-6">
                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-chart-column text-orange-500"></i> โครงสร้างนักเรียนตามระดับ
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400" x-text="'ปี ' + stats.selectedYear + ' / รอบ ' + stats.selectedTerm"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="item in stats.levelSummary" :key="item.label">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span x-text="item.label"></span>
                                            <button type="button"
                                                    class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:bg-orange-50 hover:text-orange-600 transition shrink-0"
                                                    :title="'ดูแนวโน้ม ' + item.label"
                                                    @click="openLevelTrend(item)">
                                                <i class="fa-solid fa-chart-line text-[11px]"></i>
                                            </button>
                                        </div>
                                        <span x-text="formatNumber(item.total) + ' คน / ' + formatNumber(item.rooms) + ' ห้อง'"></span>
                                    </div>
                                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-orange-500 rounded-full" :style="barWidth(item.total, stats.overview.studentTotal)"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 font-bold">
                                        <span x-text="'ชาย ' + formatNumber(item.male)"></span>
                                        <span x-text="'หญิง ' + formatNumber(item.female)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-school text-sky-500"></i> โรงเรียนที่มีจำนวนนักเรียนสูงสุด
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400">สูงสุด 12 อันดับ</span>
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
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="school in stats.topSchools" :key="school.school_smis">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <template x-if="school.logo_url">
                                                        <img :src="school.logo_url" :alt="school.school_name" class="w-8 h-8 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
                                                    </template>
                                                    <div x-show="!school.logo_url" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-school text-[11px]"></i>
                                                    </div>
                                                    <div class="font-extrabold text-slate-800 truncate" x-text="school.school_name"></div>
                                                    <button type="button"
                                                            class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:bg-sky-50 hover:text-sky-600 transition shrink-0"
                                                            title="ดูข้อมูลโรงเรียน"
                                                            @click="openTopSchoolModal(school)">
                                                        <i class="fa-solid fa-table-cells-large text-[11px]"></i>
                                                    </button>
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-1" x-text="'SMIS ' + school.school_smis"></div>
                                            </td>
                                            <td class="px-4 py-3 text-slate-500" x-text="school.network"></td>
                                            <td class="px-4 py-3 text-slate-500" x-text="school.district"></td>
                                            <td class="px-4 py-3 text-right font-extrabold text-slate-700" x-text="formatNumber(school.students)"></td>
                                            <td class="px-4 py-3 text-right text-slate-500" x-text="formatNumber(school.rooms)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-venus-mars text-rose-500"></i> สัดส่วนเพศ
                        </h3>
                        <div class="space-y-3">
                            <template x-for="item in stats.genderSummary" :key="item.label">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                        <span x-text="item.label"></span>
                                        <span x-text="formatNumber(item.total) + ' คน (' + percent(item.total, stats.overview.studentTotal) + ')'"></span>
                                    </div>
                                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full" :class="item.label === 'ชาย' ? 'bg-sky-500' : 'bg-rose-500'" :style="barWidth(item.total, stats.overview.studentTotal)"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-5 rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 text-[10px] font-extrabold text-slate-500">
                                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>ชาย</span>
                                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>หญิง</span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400">ทุกปี / ทุกรอบ</span>
                            </div>
                            <div x-ref="genderTrendWrap" class="relative h-[220px]">
                                <div class="absolute inset-x-0 top-0 bottom-10 pointer-events-none">
                                    <template x-for="grid in [0, 1, 2, 3, 4]" :key="'gender-grid-' + grid">
                                        <div class="absolute inset-x-0 border-t border-dashed border-slate-200"
                                             :style="'top: ' + (grid * 25) + '%'"></div>
                                    </template>
                                </div>

                                <svg x-ref="genderTrendChart"
                                     class="absolute inset-0 w-full h-[180px]"
                                     viewBox="0 0 1000 180"
                                     preserveAspectRatio="none"
                                     @mousemove="handleGenderTrendHover($event)"
                                     @mouseleave="hideGenderTrendTooltip()">
                                    <polyline fill="none"
                                              stroke="#0ea5e9"
                                              stroke-width="4"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              pointer-events="none"
                                              :points="genderTrendLinePoints('male')"></polyline>
                                    <polyline fill="none"
                                              stroke="#f43f5e"
                                              stroke-width="4"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              pointer-events="none"
                                              :points="genderTrendLinePoints('female')"></polyline>

                                    <template x-for="point in genderTrendChartPoints()" :key="point.key">
                                        <g>
                                            <circle :cx="point.x" :cy="point.maleY" r="5" fill="#ffffff" stroke="#0ea5e9" stroke-width="3" pointer-events="none"></circle>
                                            <circle :cx="point.x" :cy="point.femaleY" r="5" fill="#ffffff" stroke="#f43f5e" stroke-width="3" pointer-events="none"></circle>
                                        </g>
                                    </template>
                                </svg>

                                <div x-show="genderTrendTooltip"
                                     x-cloak
                                     class="absolute z-10 w-52 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-2xl pointer-events-none"
                                     :style="genderTrendTooltipBox()">
                                    <div class="text-[11px] font-extrabold" x-text="genderTrendTooltip ? ('ปี ' + genderTrendTooltip.academic_year + ' / รอบ ' + genderTrendTooltip.term) : '-'"></div>
                                    <div class="mt-2 text-xs text-sky-200" x-text="'ชาย ' + formatNumber(genderTrendTooltip ? genderTrendTooltip.male_total : 0) + ' คน'"></div>
                                    <div class="mt-1 text-xs text-rose-200" x-text="'หญิง ' + formatNumber(genderTrendTooltip ? genderTrendTooltip.female_total : 0) + ' คน'"></div>
                                    <div class="mt-1 text-xs text-slate-300" x-text="'รวม ' + formatNumber(genderTrendTooltip ? genderTrendTooltip.student_total : 0) + ' คน'"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-indigo-500"></i> เครือข่ายสถานศึกษา
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400">เรียงตามจำนวนนักเรียน</span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="item in stats.networkSummary" :key="item.label">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                        <a :href="networkLink(item.label)"
                                           class="hover:text-orange-600 transition"
                                           x-text="item.label"></a>
                                        <span x-text="formatNumber(item.students) + ' คน'"></span>
                                    </div>
                                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" :style="barWidth(item.students, stats.overview.studentTotal)"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 font-bold">
                                        <a :href="networkLink(item.label)"
                                           class="hover:text-orange-600 transition"
                                           x-text="'โรงเรียน ' + formatNumber(item.schools) + ' แห่ง'"></a>
                                        <span x-text="'ห้อง ' + formatNumber(item.rooms)"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 text-[9px] font-bold text-slate-500">
                                        <a :href="networkSizeLink(item.label, 'small')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'เล็ก ' + formatNumber(item.sizeSummary?.small || 0)"></a>
                                        <a :href="networkSizeLink(item.label, 'medium')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'กลาง ' + formatNumber(item.sizeSummary?.medium || 0)"></a>
                                        <a :href="networkSizeLink(item.label, 'large')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่ ' + formatNumber(item.sizeSummary?.large || 0)"></a>
                                        <a :href="networkSizeLink(item.label, 'special')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่พิเศษ ' + formatNumber(item.sizeSummary?.special || 0)"></a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-emerald-500"></i> ภาพรวมรายอำเภอ
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400">สูงสุด 10 อันดับ</span>
                        </div>
                        <div class="space-y-2.5">
                            <template x-for="item in stats.districtSummary" :key="item.label">
                                <div class="space-y-1.5 rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 text-xs">
                                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                        <a :href="districtLink(item.label)"
                                           class="hover:text-orange-600 transition"
                                           x-text="item.label"></a>
                                        <span x-text="formatNumber(item.students) + ' คน'"></span>
                                    </div>
                                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" :style="barWidth(item.students, stats.overview.studentTotal)"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 font-bold">
                                        <a :href="districtLink(item.label)"
                                           class="hover:text-orange-600 transition"
                                           x-text="'โรงเรียน ' + formatNumber(item.schools) + ' แห่ง'"></a>
                                        <span x-text="'ห้อง ' + formatNumber(item.rooms || 0)"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 text-[9px] font-bold text-slate-500">
                                        <a :href="districtSizeLink(item.label, 'small')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'เล็ก ' + formatNumber(item.sizeSummary?.small || 0)"></a>
                                        <a :href="districtSizeLink(item.label, 'medium')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'กลาง ' + formatNumber(item.sizeSummary?.medium || 0)"></a>
                                        <a :href="districtSizeLink(item.label, 'large')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่ ' + formatNumber(item.sizeSummary?.large || 0)"></a>
                                        <a :href="districtSizeLink(item.label, 'special')" class="px-2 py-1 rounded-full bg-slate-100 hover:bg-orange-50 hover:text-orange-600 transition" x-text="'ใหญ่พิเศษ ' + formatNumber(item.sizeSummary?.special || 0)"></a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-5">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-file-circle-check text-orange-500"></i> ชุดข้อมูลที่ใช้แสดง
                        </h3>
                        <template x-if="stats.latestImport">
                            <div class="space-y-2 text-xs text-slate-600">
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-400 font-bold">ไฟล์</span>
                                    <span class="font-bold text-right break-all" x-text="stats.latestImport.source_filename"></span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-400 font-bold">แถวที่นำเข้า</span>
                                    <span class="font-bold" x-text="formatNumber(stats.latestImport.imported_rows)"></span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-400 font-bold">schema</span>
                                    <span class="font-bold" x-text="stats.latestImport.schema_version || '-'"></span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-400 font-bold">โหมด</span>
                                    <span class="font-bold" x-text="stats.latestImport.mode"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="!stats.latestImport">
                            <p class="text-xs text-slate-400 font-medium">ยังไม่พบประวัติการนำเข้าสำหรับปี/รอบที่เลือก</p>
                        </template>
                    </div>
                </aside>
            </div>
        </div>

        <div x-show="topSchoolModal.open"
             x-transition.opacity
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4"
             @click.self="closeTopSchoolModal()"
             @keydown.escape.window="closeTopSchoolModal()">
            <div class="w-full max-w-6xl max-h-[92vh] rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                    <div class="min-w-0">
                        <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-3">
                            <template x-if="topSchoolModal.school?.logo_url">
                                <img :src="topSchoolModal.school.logo_url" :alt="topSchoolModal.school.school_name" class="w-10 h-10 rounded-2xl object-contain bg-white border border-slate-100 p-1.5 shrink-0">
                            </template>
                            <div x-show="!topSchoolModal.school?.logo_url" class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-school"></i>
                            </div>
                            <span class="truncate" x-text="topSchoolModal.school?.school_name || 'ข้อมูลโรงเรียน'"></span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            <span x-text="'SMIS ' + (topSchoolModal.school?.school_smis || '-')"></span>
                            <span class="mx-1">/</span>
                            <span x-text="topSchoolModal.school?.network || '-'"></span>
                            <span class="mx-1">/</span>
                            <span x-text="topSchoolModal.school?.district || '-'"></span>
                        </p>
                    </div>
                    <button type="button"
                            @click="closeTopSchoolModal()"
                            class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="px-6 pt-5 border-b border-slate-100">
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                @click="setTopSchoolTab('trend')"
                                class="px-4 py-2 rounded-2xl text-xs font-extrabold transition"
                                :class="topSchoolModal.activeTab === 'trend' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            แนวโน้ม
                        </button>
                        <button type="button"
                                @click="setTopSchoolTab('students')"
                                class="px-4 py-2 rounded-2xl text-xs font-extrabold transition"
                                :class="topSchoolModal.activeTab === 'students' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            ข้อมูลนักเรียน
                        </button>
                        <button type="button"
                                @click="setTopSchoolTab('school')"
                                class="px-4 py-2 rounded-2xl text-xs font-extrabold transition"
                                :class="topSchoolModal.activeTab === 'school' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            ข้อมูลโรงเรียน
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div x-show="topSchoolModal.activeTab === 'trend'" class="space-y-5" x-cloak>
                        <div x-show="topSchoolModal.trend.loading" class="py-16 flex flex-col items-center justify-center gap-3">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-sky-500"></i>
                            <span class="text-xs font-extrabold text-slate-400">กำลังโหลดแนวโน้มนักเรียนรายปี...</span>
                        </div>

                        <div x-show="!topSchoolModal.trend.loading && topSchoolModal.trend.points.length === 0"
                             class="py-12 text-center text-sm text-slate-400 font-bold" x-cloak>
                            ยังไม่พบข้อมูลแนวโน้ม
                        </div>

                        <div x-show="!topSchoolModal.trend.loading && topSchoolModal.trend.points.length > 0" class="space-y-5" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ปีแรก</div>
                                    <div class="text-base font-extrabold text-slate-800 mt-1" x-text="topSchoolModal.trend.summary.first ? topSchoolModal.trend.summary.first.academic_year : '-'"></div>
                                    <div class="text-xs text-slate-500 mt-1" x-text="topSchoolModal.trend.summary.first ? formatNumber(topSchoolModal.trend.summary.first.student_total) + ' คน' : '-'"></div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ปีล่าสุด</div>
                                    <div class="text-base font-extrabold text-slate-800 mt-1" x-text="topSchoolModal.trend.summary.latest ? topSchoolModal.trend.summary.latest.academic_year : '-'"></div>
                                    <div class="text-xs text-slate-500 mt-1" x-text="topSchoolModal.trend.summary.latest ? formatNumber(topSchoolModal.trend.summary.latest.student_total) + ' คน' : '-'"></div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ผลต่าง</div>
                                    <div class="text-base font-extrabold mt-1"
                                         :class="topSchoolTrendChangeClass()"
                                         x-text="signedNumber(topSchoolModal.trend.summary.change) + ' คน'"></div>
                                    <div class="text-xs mt-1"
                                         :class="topSchoolTrendChangeClass()"
                                         x-text="signedNumber(topSchoolModal.trend.summary.changePercent) + '%'"></div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">จำนวนปี</div>
                                    <div class="text-base font-extrabold text-slate-800 mt-1" x-text="formatNumber(topSchoolModal.trend.points.length)"></div>
                                    <div class="text-xs text-slate-500 mt-1">ใช้รอบล่าสุดของแต่ละปี</div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                                <div class="relative h-[360px]">
                                    <div class="absolute inset-x-0 top-0 bottom-10 pointer-events-none">
                                        <template x-for="grid in [0, 1, 2, 3, 4]" :key="'top-school-grid-' + grid">
                                            <div class="absolute inset-x-0 border-t border-dashed border-slate-200"
                                                 :style="'top: ' + (grid * 25) + '%'"></div>
                                        </template>
                                    </div>

                                    <svg x-ref="topSchoolTrendChart"
                                         class="absolute inset-0 w-full h-[320px]"
                                         viewBox="0 0 1000 320"
                                         preserveAspectRatio="none"
                                         @mousemove="handleTopSchoolTrendHover($event)"
                                         @mouseleave="hideTopSchoolTrendTooltip()">
                                        <polyline fill="none"
                                                  stroke="#0ea5e9"
                                                  stroke-width="4"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  pointer-events="none"
                                                  :points="topSchoolTrendLinePoints()"></polyline>

                                        <template x-for="point in topSchoolModal.trend.chartPoints" :key="point.key">
                                            <g>
                                                <circle :cx="point.x" :cy="point.y" r="7" fill="#ffffff" stroke="#0ea5e9" stroke-width="4" pointer-events="none"></circle>
                                                <circle :cx="point.x"
                                                        :cy="point.y"
                                                        r="18"
                                                        fill="transparent"
                                                        class="cursor-pointer"
                                                        @mouseenter="showTopSchoolTrendTooltip(point)"
                                                        @mouseleave="hideTopSchoolTrendTooltip()"
                                                        @focus="showTopSchoolTrendTooltip(point)"
                                                        @blur="hideTopSchoolTrendTooltip()"></circle>
                                            </g>
                                        </template>
                                    </svg>

                                    <div x-show="topSchoolModal.trend.tooltip"
                                         x-cloak
                                         class="absolute z-10 w-52 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-2xl pointer-events-none"
                                         :style="topSchoolTrendTooltipBox()">
                                        <div class="text-[11px] font-extrabold" x-text="topSchoolTrendTooltipLabel()"></div>
                                        <div class="mt-2 text-xs text-sky-200" x-text="'นักเรียน ' + formatNumber(topSchoolModal.trend.tooltip ? topSchoolModal.trend.tooltip.student_total : 0) + ' คน'"></div>
                                        <div class="mt-1 text-xs text-slate-300" x-text="'ห้องเรียน ' + formatNumber(topSchoolModal.trend.tooltip ? topSchoolModal.trend.tooltip.room_total : 0) + ' ห้อง'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="topSchoolModal.activeTab === 'students'" class="space-y-5" x-cloak>
                        <div x-show="topSchoolModal.students.loading" class="py-16 flex flex-col items-center justify-center gap-3">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-sky-500"></i>
                            <span class="text-xs font-extrabold text-slate-400">กำลังโหลดข้อมูลนักเรียน...</span>
                        </div>

                        <div x-show="!topSchoolModal.students.loading && !topSchoolModal.students.school" class="py-12 text-center text-sm text-slate-400 font-bold" x-cloak>
                            ไม่พบข้อมูลนักเรียน
                        </div>

                        <div x-show="!topSchoolModal.students.loading && topSchoolModal.students.school" class="space-y-5" x-cloak>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ห้องเรียน</div>
                                    <div class="text-2xl font-extrabold text-slate-800 mt-1" x-text="formatNumber(topSchoolModal.students.summary.rooms)"></div>
                                </div>
                                <div class="bg-sky-50 border border-sky-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-sky-500 uppercase">ชาย</div>
                                    <div class="text-2xl font-extrabold text-sky-700 mt-1" x-text="formatNumber(topSchoolModal.students.summary.male)"></div>
                                </div>
                                <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-rose-500 uppercase">หญิง</div>
                                    <div class="text-2xl font-extrabold text-rose-700 mt-1" x-text="formatNumber(topSchoolModal.students.summary.female)"></div>
                                </div>
                                <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-orange-500 uppercase">รวม</div>
                                    <div class="text-2xl font-extrabold text-orange-700 mt-1" x-text="formatNumber(topSchoolModal.students.summary.total)"></div>
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
                                            <template x-for="row in topSchoolModal.students.gradeRows" :key="row.key">
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
                                                <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(topSchoolModal.students.summary.rooms)"></td>
                                                <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(topSchoolModal.students.summary.male)"></td>
                                                <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(topSchoolModal.students.summary.female)"></td>
                                                <td class="px-4 py-3 text-right font-extrabold text-orange-700" x-text="formatNumber(topSchoolModal.students.summary.total)"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div x-show="topSchoolModal.students.levelRows.length > 0" class="bg-slate-50 border border-slate-100 rounded-3xl p-5">
                                <h4 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 mb-3">
                                    <i class="fa-solid fa-layer-group text-indigo-500"></i> สรุปตามช่วงชั้น
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="row in topSchoolModal.students.levelRows" :key="row.key">
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

                    <div x-show="topSchoolModal.activeTab === 'school'" class="space-y-5" x-cloak>
                        <div x-show="topSchoolModal.schoolInfo.loading" class="py-16 flex flex-col items-center justify-center gap-3">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-500"></i>
                            <span class="text-xs font-extrabold text-slate-400">กำลังโหลดข้อมูลโรงเรียน...</span>
                        </div>

                        <div x-show="!topSchoolModal.schoolInfo.loading && !topSchoolModal.schoolInfo.school" class="py-12 text-center text-sm text-slate-400 font-bold" x-cloak>
                            ไม่พบข้อมูลโรงเรียน
                        </div>

                        <div x-show="!topSchoolModal.schoolInfo.loading && topSchoolModal.schoolInfo.school" class="space-y-5" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">เครือข่าย</div>
                                    <div class="text-xl font-extrabold text-slate-800 mt-1" x-text="topSchoolModal.schoolInfo.school?.schoolgroup_name || '-'"></div>
                                </div>
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">ระยะทางถึงเขต</div>
                                    <div class="text-xl font-extrabold text-slate-800 mt-1" x-text="topSchoolModal.schoolInfo.school?.length_km ? topSchoolModal.schoolInfo.school.length_km + ' กม.' : '-'"></div>
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
                                        <template x-for="item in topSchoolInfoRows()" :key="item.label">
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
                                            <span class="font-extrabold text-slate-700" x-text="topSchoolModal.schoolInfo.school?.lat || '-'"></span>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <span class="text-slate-400 font-bold">Longitude</span>
                                            <span class="font-extrabold text-slate-700" x-text="topSchoolModal.schoolInfo.school?.lng || '-'"></span>
                                        </div>
                                    </div>
                                    <template x-if="topSchoolModal.schoolInfo.school?.maplink">
                                        <a :href="topSchoolModal.schoolInfo.school.maplink"
                                           target="_blank"
                                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-xs font-extrabold hover:bg-emerald-700 transition">
                                            <i class="fa-solid fa-map-location-dot"></i>
                                            เปิดแผนที่
                                        </a>
                                    </template>
                                    <template x-if="topSchoolModal.schoolInfo.school?.website">
                                        <a :href="websiteUrl(topSchoolModal.schoolInfo.school.website)"
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

        <div x-show="studentTrendModal.open"
             x-transition.opacity
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4"
             @click.self="closeStudentTrend()"
             @keydown.escape.window="closeStudentTrend()">
            <div class="w-full max-w-5xl rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-sky-500"></i>
                            แนวโน้มนักเรียนรวมทุกปีการศึกษาและรอบ
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">เปรียบเทียบจำนวนนักเรียนรวมและห้องเรียนของทุกชุดข้อมูลที่นำเข้า</p>
                    </div>
                    <button type="button"
                            @click="closeStudentTrend()"
                            class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div x-show="studentTrendModal.loading" class="py-16 flex flex-col items-center justify-center gap-3" x-cloak>
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-sky-500"></i>
                        <span class="text-xs font-extrabold text-slate-400">กำลังประมวลผลแนวโน้มจำนวนนักเรียน...</span>
                    </div>

                    <div x-show="!studentTrendModal.loading && studentTrendModal.points.length === 0"
                         class="py-12 text-center text-sm text-slate-400 font-bold"
                         x-cloak>
                        ยังไม่พบข้อมูลสำหรับสร้างกราฟแนวโน้ม
                    </div>

                    <div x-show="!studentTrendModal.loading && studentTrendModal.points.length > 0" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">จุดเริ่มต้น</div>
                                <div class="text-base font-extrabold text-slate-800 mt-1" x-text="studentTrendModal.summary.first ? studentTrendModal.summary.first.label : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="studentTrendModal.summary.first ? formatNumber(studentTrendModal.summary.first.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ข้อมูลล่าสุด</div>
                                <div class="text-base font-extrabold text-slate-800 mt-1" x-text="studentTrendModal.summary.latest ? studentTrendModal.summary.latest.label : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="studentTrendModal.summary.latest ? formatNumber(studentTrendModal.summary.latest.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ผลต่าง</div>
                                <div class="text-base font-extrabold mt-1"
                                     :class="studentTrendChangeClass()"
                                     x-text="signedNumber(studentTrendModal.summary.change) + ' คน'"></div>
                                <div class="text-xs mt-1"
                                     :class="studentTrendChangeClass()"
                                     x-text="signedNumber(studentTrendModal.summary.changePercent) + '%'"></div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="relative h-[360px]">
                                <div class="absolute inset-x-0 top-0 bottom-10 pointer-events-none">
                                    <template x-for="grid in [0, 1, 2, 3, 4]" :key="grid">
                                        <div class="absolute inset-x-0 border-t border-dashed border-slate-200"
                                             :style="'top: ' + (grid * 25) + '%'"></div>
                                    </template>
                                </div>

                                <svg x-ref="studentTrendChart"
                                     class="absolute inset-0 w-full h-[320px]"
                                     viewBox="0 0 1000 320"
                                     preserveAspectRatio="none"
                                     @mousemove="handleStudentTrendHover($event)"
                                     @mouseleave="hideStudentTrendTooltip()">
                                    <polyline fill="none"
                                              stroke="#0ea5e9"
                                              stroke-width="4"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              pointer-events="none"
                                              :points="studentTrendLinePoints()"></polyline>

                                    <template x-for="point in studentTrendModal.chartPoints" :key="point.key">
                                        <g>
                                            <circle :cx="point.x"
                                                    :cy="point.y"
                                                    r="7"
                                                    fill="#ffffff"
                                                    stroke="#0ea5e9"
                                                    stroke-width="4"
                                                    pointer-events="none"></circle>
                                            <circle :cx="point.x"
                                                    :cy="point.y"
                                                    r="18"
                                                    fill="transparent"
                                                    class="cursor-pointer"
                                                    @mouseenter="showStudentTrendTooltip(point)"
                                                    @mouseleave="hideStudentTrendTooltip()"
                                                    @focus="showStudentTrendTooltip(point)"
                                                    @blur="hideStudentTrendTooltip()"></circle>
                                        </g>
                                    </template>
                                </svg>

                                <div x-show="studentTrendModal.tooltip"
                                     x-cloak
                                     class="absolute z-10 w-52 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-2xl pointer-events-none"
                                     :style="studentTrendTooltipBox()">
                                    <div class="text-[11px] font-extrabold" x-text="studentTrendTooltipLabel()"></div>
                                    <div class="mt-2 text-xs text-slate-200" x-text="'นักเรียน ' + studentTrendTooltipStudents() + ' คน'"></div>
                                    <div class="mt-1 text-xs text-slate-300" x-text="'ห้องเรียน ' + studentTrendTooltipRooms() + ' ห้อง'"></div>
                                    <div class="mt-1 text-xs text-slate-300" x-text="'โรงเรียน ' + formatNumber(studentTrendModal.tooltip ? studentTrendModal.tooltip.schools_count : 0) + ' แห่ง'"></div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div x-show="levelTrendModal.open"
             x-transition.opacity
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4"
             @click.self="closeLevelTrend()"
             @keydown.escape.window="closeLevelTrend()">
            <div class="w-full max-w-5xl rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-orange-500"></i>
                            <span x-text="'แนวโน้มนักเรียนระดับ ' + (levelTrendModal.levelLabel || '-')"></span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">เปรียบเทียบข้อมูลนักเรียนตามระดับชั้นในทุกปีการศึกษาและรอบที่นำเข้า</p>
                    </div>
                    <button type="button"
                            @click="closeLevelTrend()"
                            class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div x-show="levelTrendModal.loading" class="py-16 flex flex-col items-center justify-center gap-3" x-cloak>
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
                        <span class="text-xs font-extrabold text-slate-400">กำลังประมวลผลแนวโน้มตามระดับชั้น...</span>
                    </div>

                    <div x-show="!levelTrendModal.loading && levelTrendModal.points.length === 0"
                         class="py-12 text-center text-sm text-slate-400 font-bold"
                         x-cloak>
                        ยังไม่พบข้อมูลสำหรับสร้างกราฟแนวโน้ม
                    </div>

                    <div x-show="!levelTrendModal.loading && levelTrendModal.points.length > 0" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">จุดเริ่มต้น</div>
                                <div class="text-base font-extrabold text-slate-800 mt-1" x-text="levelTrendModal.summary.first ? levelTrendModal.summary.first.label : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="levelTrendModal.summary.first ? formatNumber(levelTrendModal.summary.first.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ข้อมูลล่าสุด</div>
                                <div class="text-base font-extrabold text-slate-800 mt-1" x-text="levelTrendModal.summary.latest ? levelTrendModal.summary.latest.label : '-'"></div>
                                <div class="text-xs text-slate-500 mt-1" x-text="levelTrendModal.summary.latest ? formatNumber(levelTrendModal.summary.latest.student_total) + ' คน' : '-'"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3">
                                <div class="text-[10px] font-extrabold text-slate-400 uppercase">ผลต่าง</div>
                                <div class="text-base font-extrabold mt-1"
                                     :class="levelTrendChangeClass()"
                                     x-text="signedNumber(levelTrendModal.summary.change) + ' คน'"></div>
                                <div class="text-xs mt-1"
                                     :class="levelTrendChangeClass()"
                                     x-text="signedNumber(levelTrendModal.summary.changePercent) + '%'"></div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="relative h-[360px]">
                                <div class="absolute inset-x-0 top-0 bottom-10 pointer-events-none">
                                    <template x-for="grid in [0, 1, 2, 3, 4]" :key="'level-grid-' + grid">
                                        <div class="absolute inset-x-0 border-t border-dashed border-slate-200"
                                             :style="'top: ' + (grid * 25) + '%'"></div>
                                    </template>
                                </div>

                                <svg x-ref="levelTrendChart"
                                     class="absolute inset-0 w-full h-[320px]"
                                     viewBox="0 0 1000 320"
                                     preserveAspectRatio="none"
                                     @mousemove="handleLevelTrendHover($event)"
                                     @mouseleave="hideLevelTrendTooltip()">
                                    <polyline fill="none"
                                              stroke="#f97316"
                                              stroke-width="4"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              pointer-events="none"
                                              :points="levelTrendLinePoints()"></polyline>

                                    <template x-for="point in levelTrendModal.chartPoints" :key="point.key">
                                        <g>
                                            <circle :cx="point.x"
                                                    :cy="point.y"
                                                    r="7"
                                                    fill="#ffffff"
                                                    stroke="#f97316"
                                                    stroke-width="4"
                                                    pointer-events="none"></circle>
                                            <circle :cx="point.x"
                                                    :cy="point.y"
                                                    r="18"
                                                    fill="transparent"
                                                    class="cursor-pointer"
                                                    @mouseenter="showLevelTrendTooltip(point)"
                                                    @mouseleave="hideLevelTrendTooltip()"
                                                    @focus="showLevelTrendTooltip(point)"
                                                    @blur="hideLevelTrendTooltip()"></circle>
                                        </g>
                                    </template>
                                </svg>

                                <div x-show="levelTrendModal.tooltip"
                                     x-cloak
                                     class="absolute z-10 w-52 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-2xl pointer-events-none"
                                     :style="levelTrendTooltipBox()">
                                    <div class="text-[11px] font-extrabold" x-text="levelTrendTooltipLabel()"></div>
                                    <div class="mt-2 text-xs text-orange-200" x-text="'นักเรียน ' + levelTrendTooltipStudents() + ' คน'"></div>
                                    <div class="mt-1 text-xs text-slate-300" x-text="'ห้องเรียน ' + levelTrendTooltipRooms() + ' ห้อง'"></div>
                                    <div class="mt-1 text-xs text-slate-300" x-text="'โรงเรียน ' + formatNumber(levelTrendModal.tooltip ? levelTrendModal.tooltip.schools_count : 0) + ' แห่ง'"></div>
                                </div>
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
            function dashboardManager() {
                return {
                    loadingStats: true,
                    stats: {
                        availableYears: [],
                        availableTerms: [],
                        selectedYear: '',
                        selectedTerm: '',
                        overview: {
                            schoolsCount: 0,
                            matchedSchoolsCount: 0,
                            studentTotal: 0,
                            roomTotal: 0,
                            maleTotal: 0,
                            femaleTotal: 0,
                            avgStudentsPerSchool: 0
                        },
                        genderSummary: [],
                        levelSummary: [],
                        networkSummary: [],
                        districtSummary: [],
                        topSchools: [],
                        latestImport: null
                    },
                    filters: {
                        academic_year: '',
                        term: ''
                    },
                    toast: {
                        show: false,
                        message: '',
                        type: 'success'
                    },
                    studentTrendModal: {
                        open: false,
                        loading: false,
                        points: [],
                        chartPoints: [],
                        summary: {
                            first: null,
                            latest: null,
                            change: 0,
                            changePercent: 0
                        },
                        tooltip: null
                    },
                    levelTrendModal: {
                        open: false,
                        loading: false,
                        level: '',
                        levelLabel: '',
                        points: [],
                        chartPoints: [],
                        summary: {
                            first: null,
                            latest: null,
                            change: 0,
                            changePercent: 0
                        },
                        tooltip: null
                    },
                    topSchoolModal: {
                        open: false,
                        activeTab: 'trend',
                        school: null,
                        trend: {
                            loaded: false,
                            loading: false,
                            points: [],
                            chartPoints: [],
                            summary: {
                                first: null,
                                latest: null,
                                change: 0,
                                changePercent: 0
                            },
                            tooltip: null
                        },
                        students: {
                            loaded: false,
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
                        schoolInfo: {
                            loaded: false,
                            loading: false,
                            school: null
                        }
                    },
                    genderTrendTooltip: null,
                    genderTrendTooltipPosition: {
                        left: 0,
                        top: 0
                    },

                    init() {
                        this.fetchStats();
                    },

                    fetchStats() {
                        this.loadingStats = true;
                        axios.get('{{ route("api.dashboard.stats") }}', {
                            params: {
                                academic_year: this.filters.academic_year || '',
                                term: this.filters.term || ''
                            }
                        }).then(response => {
                            if (response.data.status === 'success') {
                                this.stats = response.data.data;
                                this.filters.academic_year = this.stats.selectedYear ? String(this.stats.selectedYear) : '';
                                this.filters.term = this.stats.selectedTerm ? String(this.stats.selectedTerm) : '';
                            } else {
                                this.showToast('ดึงข้อมูลแดชบอร์ดไม่สำเร็จ', 'error');
                            }
                        }).catch(() => {
                            this.showToast('เชื่อมต่อข้อมูลแดชบอร์ดไม่สำเร็จ', 'error');
                        }).finally(() => {
                            this.loadingStats = false;
                        });
                    },

                    overviewCards() {
                        return [
                            {
                                label: 'โรงเรียนที่มีข้อมูล',
                                value: this.stats.overview.schoolsCount,
                                note: 'หน่วยข้อมูลรายโรงเรียน',
                                icon: 'fa-solid fa-school',
                                iconBg: 'bg-orange-50 text-orange-600',
                                href: this.allSchoolsLink(),
                                sizeBreakdown: this.stats.overview.schoolSizeSummary || null
                            },
                            {
                                label: 'โรงเรียนขยายโอกาส',
                                value: this.stats.overview.opportunitySchoolsCount,
                                note: 'มีนักเรียน ม.3 - ม.6',
                                icon: 'fa-solid fa-building-columns',
                                iconBg: 'bg-violet-50 text-violet-600',
                                href: this.opportunitySchoolsLink(),
                                sizeBreakdown: this.stats.overview.opportunitySchoolSizeSummary || null,
                                sizeLinkBuilder: 'opportunity'
                            },
                            {
                                label: 'นักเรียนรวมทั้งหมด',
                                value: this.stats.overview.studentTotal,
                                note: 'จากปีและรอบที่เลือก',
                                icon: 'fa-solid fa-users',
                                iconBg: 'bg-sky-50 text-sky-600',
                                clickable: true
                            },
                            {
                                label: 'จำนวนห้องเรียน',
                                value: this.stats.overview.roomTotal,
                                note: 'รวมทุกระดับชั้น',
                                icon: 'fa-solid fa-door-open',
                                iconBg: 'bg-indigo-50 text-indigo-600'
                            },
                            {
                                label: 'เฉลี่ยต่อโรงเรียน',
                                value: this.stats.overview.avgStudentsPerSchool,
                                note: 'นักเรียนต่อโรงเรียน',
                                icon: 'fa-solid fa-chart-line',
                                iconBg: 'bg-emerald-50 text-emerald-600'
                            }
                        ];
                    },

                    formatNumber(value) {
                        const numeric = Number(value || 0);
                        if (Number.isInteger(numeric)) {
                            return numeric.toLocaleString('th-TH');
                        }
                        return numeric.toLocaleString('th-TH', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1
                        });
                    },

                    percent(value, base) {
                        const total = Number(base || 0);
                        if (total <= 0) {
                            return '0.0%';
                        }
                        return ((Number(value || 0) * 100) / total).toFixed(1) + '%';
                    },

                    barWidth(value, base) {
                        const total = Number(base || 0);
                        if (total <= 0) {
                            return 'width: 0%';
                        }
                        const pct = Math.max(0, Math.min(100, (Number(value || 0) * 100) / total));
                        return 'width: ' + pct + '%';
                    },

                    genderTrendChartPoints() {
                        const points = Array.isArray(this.stats.genderTrend) ? this.stats.genderTrend : [];
                        if (points.length === 0) {
                            return [];
                        }

                        const values = points.flatMap(point => [
                            Number(point.male_total || 0),
                            Number(point.female_total || 0)
                        ]);
                        const min = Math.min(...values);
                        const max = Math.max(...values);
                        const range = Math.max(max - min, 1);
                        const left = 40;
                        const right = 960;
                        const top = 12;
                        const bottom = 160;

                        return points.map((point, index) => {
                            const ratioX = points.length > 1 ? index / (points.length - 1) : 0.5;
                            const mapY = (value) => bottom - (((Number(value || 0) - min) / range) * (bottom - top));

                            return {
                                ...point,
                                key: point.label,
                                x: left + ((right - left) * ratioX),
                                maleY: mapY(point.male_total),
                                femaleY: mapY(point.female_total)
                            };
                        });
                    },

                    genderTrendLinePoints(type) {
                        return this.genderTrendChartPoints()
                            .map(point => point.x + ',' + (type === 'male' ? point.maleY : point.femaleY))
                            .join(' ');
                    },

                    handleGenderTrendHover(event) {
                        const points = this.genderTrendChartPoints();
                        if (!points.length) {
                            this.genderTrendTooltip = null;
                            return;
                        }

                        const svg = this.$refs.genderTrendChart;
                        if (!svg) {
                            return;
                        }

                        const rect = svg.getBoundingClientRect();
                        if (!rect.width) {
                            return;
                        }

                        const relativeX = ((event.clientX - rect.left) / rect.width) * 1000;
                        let nearestPoint = points[0];
                        let nearestDistance = Math.abs(relativeX - points[0].x);

                        points.forEach(point => {
                            const distance = Math.abs(relativeX - point.x);
                            if (distance < nearestDistance) {
                                nearestPoint = point;
                                nearestDistance = distance;
                            }
                        });

                        this.genderTrendTooltip = nearestPoint;

                        const wrap = this.$refs.genderTrendWrap;
                        if (!wrap) {
                            return;
                        }

                        const wrapRect = wrap.getBoundingClientRect();
                        const tooltipWidth = 208;
                        const tooltipHeight = 88;
                        const offsetX = 16;
                        const offsetY = 18;
                        const left = Math.max(
                            12,
                            Math.min(
                                wrapRect.width - tooltipWidth - 12,
                                event.clientX - wrapRect.left + offsetX
                            )
                        );
                        const top = Math.max(
                            8,
                            Math.min(
                                wrapRect.height - tooltipHeight - 8,
                                event.clientY - wrapRect.top - tooltipHeight - offsetY
                            )
                        );

                        this.genderTrendTooltipPosition = { left, top };
                    },

                    hideGenderTrendTooltip() {
                        this.genderTrendTooltip = null;
                    },

                    genderTrendTooltipBox() {
                        if (!this.genderTrendTooltip) {
                            return 'display:none;';
                        }

                        return 'left:' + this.genderTrendTooltipPosition.left + 'px;top:' + this.genderTrendTooltipPosition.top + 'px;';
                    },

                    allSchoolsLink() {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ route('dashboard.schools') }}' + '?' + params.toString();
                    },

                    opportunitySchoolsLink() {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ route('dashboard.opportunity-schools') }}' + '?' + params.toString();
                    },

                    sizeLink(size) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/size') }}/' + size + '?' + params.toString();
                    },

                    opportunitySizeLink(size) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/opportunity/size') }}/' + size + '?' + params.toString();
                    },

                    networkLink(network) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/network') }}/' + encodeURIComponent(network) + '?' + params.toString();
                    },

                    networkSizeLink(network, size) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/network') }}/' + encodeURIComponent(network) + '/size/' + size + '?' + params.toString();
                    },

                    districtLink(district) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/district') }}/' + encodeURIComponent(district) + '?' + params.toString();
                    },

                    districtSizeLink(district, size) {
                        const params = new URLSearchParams();

                        if (this.stats.selectedYear) {
                            params.set('academic_year', this.stats.selectedYear);
                        }

                        if (this.stats.selectedTerm) {
                            params.set('term', this.stats.selectedTerm);
                        }

                        return '{{ url('/schools/district') }}/' + encodeURIComponent(district) + '/size/' + size + '?' + params.toString();
                    },

                    cardSizeLink(item, size) {
                        if (item.sizeLinkBuilder === 'opportunity') {
                            return this.opportunitySizeLink(size);
                        }

                        return this.sizeLink(size);
                    },

                    openTopSchoolModal(school) {
                        this.topSchoolModal.open = true;
                        this.topSchoolModal.activeTab = 'trend';
                        this.topSchoolModal.school = school;
                        this.resetTopSchoolModalSections();
                        this.loadTopSchoolTrend();
                    },

                    closeTopSchoolModal() {
                        this.topSchoolModal.open = false;
                        this.topSchoolModal.trend.tooltip = null;
                    },

                    resetTopSchoolModalSections() {
                        this.topSchoolModal.trend = {
                            loaded: false,
                            loading: false,
                            points: [],
                            chartPoints: [],
                            summary: {
                                first: null,
                                latest: null,
                                change: 0,
                                changePercent: 0
                            },
                            tooltip: null
                        };
                        this.topSchoolModal.students = {
                            loaded: false,
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
                        };
                        this.topSchoolModal.schoolInfo = {
                            loaded: false,
                            loading: false,
                            school: null
                        };
                    },

                    setTopSchoolTab(tab) {
                        this.topSchoolModal.activeTab = tab;

                        if (tab === 'trend') {
                            this.loadTopSchoolTrend();
                        } else if (tab === 'students') {
                            this.loadTopSchoolStudents();
                        } else if (tab === 'school') {
                            this.loadTopSchoolInfo();
                        }
                    },

                    loadTopSchoolTrend() {
                        if (!this.topSchoolModal.school?.school_smis || this.topSchoolModal.trend.loaded || this.topSchoolModal.trend.loading) {
                            return;
                        }

                        this.topSchoolModal.trend.loading = true;
                        this.topSchoolModal.trend.tooltip = null;

                        axios.get('{{ route("api.dashboard.school-trend") }}', {
                            params: {
                                school_smis: this.topSchoolModal.school.school_smis
                            }
                        }).then(response => {
                            const data = response.data.data || {};
                            const points = Array.isArray(data.points) ? data.points : [];

                            this.topSchoolModal.trend.points = points;
                            this.topSchoolModal.trend.summary = data.summary || this.topSchoolModal.trend.summary;
                            this.topSchoolModal.trend.chartPoints = this.buildStudentTrendChartPoints(
                                points.map(point => ({
                                    ...point,
                                    label: String(point.academic_year || '') + ' / ' + String(point.term || '')
                                }))
                            );
                            this.topSchoolModal.trend.loaded = true;
                        }).catch(() => {
                            this.showToast('เชื่อมต่อข้อมูลแนวโน้มโรงเรียนไม่สำเร็จ', 'error');
                        }).finally(() => {
                            this.topSchoolModal.trend.loading = false;
                        });
                    },

                    loadTopSchoolStudents() {
                        if (!this.topSchoolModal.school?.school_smis || this.topSchoolModal.students.loaded || this.topSchoolModal.students.loading) {
                            return;
                        }

                        this.topSchoolModal.students.loading = true;

                        axios.get('{{ route("api.dashboard.school-student-detail") }}', {
                            params: {
                                school_smis: this.topSchoolModal.school.school_smis,
                                academic_year: this.filters.academic_year || '',
                                term: this.filters.term || ''
                            }
                        }).then(response => {
                            const data = response.data.data || {};

                            this.topSchoolModal.students.school = data.school || null;
                            this.topSchoolModal.students.gradeRows = data.gradeRows || [];
                            this.topSchoolModal.students.levelRows = data.levelRows || [];
                            this.topSchoolModal.students.summary = data.summary || this.topSchoolModal.students.summary;
                            this.topSchoolModal.students.loaded = true;
                        }).catch(() => {
                            this.showToast('เชื่อมต่อข้อมูลนักเรียนโรงเรียนไม่สำเร็จ', 'error');
                        }).finally(() => {
                            this.topSchoolModal.students.loading = false;
                        });
                    },

                    loadTopSchoolInfo() {
                        if (!this.topSchoolModal.school?.school_smis || this.topSchoolModal.schoolInfo.loaded || this.topSchoolModal.schoolInfo.loading) {
                            return;
                        }

                        this.topSchoolModal.schoolInfo.loading = true;

                        axios.get('{{ route("api.dashboard.school-info") }}', {
                            params: {
                                school_smis: this.topSchoolModal.school.school_smis
                            }
                        }).then(response => {
                            this.topSchoolModal.schoolInfo.school = response.data.data || null;
                            this.topSchoolModal.schoolInfo.loaded = true;
                        }).catch(() => {
                            this.showToast('เชื่อมต่อข้อมูลโรงเรียนไม่สำเร็จ', 'error');
                        }).finally(() => {
                            this.topSchoolModal.schoolInfo.loading = false;
                        });
                    },

                    topSchoolInfoRows() {
                        const school = this.topSchoolModal.schoolInfo.school || {};

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

                    openStudentTrend() {
                        this.studentTrendModal.open = true;
                        this.studentTrendModal.loading = true;
                        this.studentTrendModal.points = [];
                        this.studentTrendModal.chartPoints = [];
                        this.studentTrendModal.tooltip = null;

                        axios.get('{{ route("api.dashboard.student-trend") }}')
                            .then(response => {
                                if (response.data.status !== 'success') {
                                    this.showToast('ดึงข้อมูลกราฟนักเรียนไม่สำเร็จ', 'error');
                                    return;
                                }

                                const points = response.data.data && Array.isArray(response.data.data.points)
                                    ? response.data.data.points
                                    : [];

                                this.studentTrendModal.points = points;
                                this.studentTrendModal.summary = response.data.data && response.data.data.summary
                                    ? response.data.data.summary
                                    : {
                                        first: null,
                                        latest: null,
                                        change: 0,
                                        changePercent: 0
                                    };
                                this.studentTrendModal.chartPoints = this.buildStudentTrendChartPoints(points);
                            })
                            .catch(() => {
                                this.showToast('เชื่อมต่อข้อมูลกราฟนักเรียนไม่สำเร็จ', 'error');
                            })
                            .finally(() => {
                                this.studentTrendModal.loading = false;
                            });
                    },

                    closeStudentTrend() {
                        this.studentTrendModal.open = false;
                        this.studentTrendModal.tooltip = null;
                    },

                    openLevelTrend(item) {
                        this.levelTrendModal.open = true;
                        this.levelTrendModal.loading = true;
                        this.levelTrendModal.level = item && item.key ? item.key : '';
                        this.levelTrendModal.levelLabel = item && item.label ? item.label : '';
                        this.levelTrendModal.points = [];
                        this.levelTrendModal.chartPoints = [];
                        this.levelTrendModal.tooltip = null;

                        axios.get('{{ route("api.dashboard.level-trend") }}', {
                            params: {
                                level: this.levelTrendModal.level
                            }
                        }).then(response => {
                            if (response.data.status !== 'success') {
                                this.showToast('ดึงข้อมูลกราฟระดับชั้นไม่สำเร็จ', 'error');
                                return;
                            }

                            const data = response.data.data || {};
                            const points = Array.isArray(data.points) ? data.points : [];

                            this.levelTrendModal.levelLabel = data.levelLabel || this.levelTrendModal.levelLabel;
                            this.levelTrendModal.points = points;
                            this.levelTrendModal.summary = data.summary || {
                                first: null,
                                latest: null,
                                change: 0,
                                changePercent: 0
                            };
                            this.levelTrendModal.chartPoints = this.buildStudentTrendChartPoints(points);
                        }).catch(() => {
                            this.showToast('เชื่อมต่อข้อมูลกราฟระดับชั้นไม่สำเร็จ', 'error');
                        }).finally(() => {
                            this.levelTrendModal.loading = false;
                        });
                    },

                    closeLevelTrend() {
                        this.levelTrendModal.open = false;
                        this.levelTrendModal.tooltip = null;
                    },

                    buildStudentTrendChartPoints(points) {
                        if (!Array.isArray(points) || points.length === 0) {
                            return [];
                        }

                        if (points.length === 1) {
                            const single = points[0];

                            return [{
                                ...single,
                                key: single.label,
                                x: 500,
                                y: 160
                            }];
                        }

                        const values = points.map(point => Number(point.student_total || 0));
                        const min = Math.min(...values);
                        const max = Math.max(...values);
                        const range = Math.max(max - min, 1);
                        const left = 56;
                        const right = 944;
                        const top = 20;
                        const bottom = 280;

                        return points.map((point, index) => {
                            const ratioX = index / (points.length - 1);
                            const ratioY = (Number(point.student_total || 0) - min) / range;

                            return {
                                ...point,
                                key: point.label,
                                x: left + ((right - left) * ratioX),
                                y: bottom - ((bottom - top) * ratioY)
                            };
                        });
                    },

                    studentTrendLinePoints() {
                        return this.studentTrendModal.chartPoints
                            .map(point => point.x + ',' + point.y)
                            .join(' ');
                    },

                    showStudentTrendTooltip(point) {
                        this.studentTrendModal.tooltip = point;
                    },

                    handleStudentTrendHover(event) {
                        const points = this.studentTrendModal.chartPoints;

                        if (!Array.isArray(points) || points.length === 0) {
                            this.studentTrendModal.tooltip = null;
                            return;
                        }

                        const svg = this.$refs.studentTrendChart;

                        if (!svg) {
                            return;
                        }

                        const rect = svg.getBoundingClientRect();

                        if (!rect.width) {
                            return;
                        }

                        const relativeX = ((event.clientX - rect.left) / rect.width) * 1000;
                        let nearestPoint = points[0];
                        let nearestDistance = Math.abs(relativeX - points[0].x);

                        points.forEach(point => {
                            const distance = Math.abs(relativeX - point.x);

                            if (distance < nearestDistance) {
                                nearestPoint = point;
                                nearestDistance = distance;
                            }
                        });

                        this.studentTrendModal.tooltip = nearestPoint;
                    },

                    hideStudentTrendTooltip() {
                        this.studentTrendModal.tooltip = null;
                    },

                    studentTrendTooltipBox() {
                        const point = this.studentTrendModal.tooltip;

                        if (!point) {
                            return 'display:none;';
                        }

                        const left = Math.max(16, Math.min(760, point.x - 104));
                        const top = Math.max(0, point.y - 14);

                        return 'left:' + left + 'px;top:' + top + 'px;transform:translateY(-100%);';
                    },

                    studentTrendTooltipLabel() {
                        if (!this.studentTrendModal.tooltip) {
                            return '-';
                        }

                        return 'ปี ' + this.studentTrendModal.tooltip.academic_year + ' / รอบ ' + this.studentTrendModal.tooltip.term;
                    },

                    studentTrendTooltipStudents() {
                        return this.formatNumber(this.studentTrendModal.tooltip ? this.studentTrendModal.tooltip.student_total : 0);
                    },

                    studentTrendTooltipRooms() {
                        return this.formatNumber(this.studentTrendModal.tooltip ? this.studentTrendModal.tooltip.room_total : 0);
                    },

                    studentTrendChangeClass() {
                        const change = Number(this.studentTrendModal.summary ? this.studentTrendModal.summary.change : 0);

                        if (change > 0) {
                            return 'text-emerald-600';
                        }

                        if (change < 0) {
                            return 'text-rose-600';
                        }

                        return 'text-slate-700';
                    },

                    levelTrendLinePoints() {
                        return this.levelTrendModal.chartPoints
                            .map(point => point.x + ',' + point.y)
                            .join(' ');
                    },

                    showLevelTrendTooltip(point) {
                        this.levelTrendModal.tooltip = point;
                    },

                    handleLevelTrendHover(event) {
                        const points = this.levelTrendModal.chartPoints;

                        if (!Array.isArray(points) || points.length === 0) {
                            this.levelTrendModal.tooltip = null;
                            return;
                        }

                        const svg = this.$refs.levelTrendChart;

                        if (!svg) {
                            return;
                        }

                        const rect = svg.getBoundingClientRect();

                        if (!rect.width) {
                            return;
                        }

                        const relativeX = ((event.clientX - rect.left) / rect.width) * 1000;
                        let nearestPoint = points[0];
                        let nearestDistance = Math.abs(relativeX - points[0].x);

                        points.forEach(point => {
                            const distance = Math.abs(relativeX - point.x);

                            if (distance < nearestDistance) {
                                nearestPoint = point;
                                nearestDistance = distance;
                            }
                        });

                        this.levelTrendModal.tooltip = nearestPoint;
                    },

                    hideLevelTrendTooltip() {
                        this.levelTrendModal.tooltip = null;
                    },

                    levelTrendTooltipBox() {
                        const point = this.levelTrendModal.tooltip;

                        if (!point) {
                            return 'display:none;';
                        }

                        const left = Math.max(16, Math.min(760, point.x - 104));
                        const top = Math.max(0, point.y - 14);

                        return 'left:' + left + 'px;top:' + top + 'px;transform:translateY(-100%);';
                    },

                    levelTrendTooltipLabel() {
                        if (!this.levelTrendModal.tooltip) {
                            return '-';
                        }

                        return 'ปี ' + this.levelTrendModal.tooltip.academic_year + ' / รอบ ' + this.levelTrendModal.tooltip.term;
                    },

                    levelTrendTooltipStudents() {
                        return this.formatNumber(this.levelTrendModal.tooltip ? this.levelTrendModal.tooltip.student_total : 0);
                    },

                    levelTrendTooltipRooms() {
                        return this.formatNumber(this.levelTrendModal.tooltip ? this.levelTrendModal.tooltip.room_total : 0);
                    },

                    levelTrendChangeClass() {
                        const change = Number(this.levelTrendModal.summary ? this.levelTrendModal.summary.change : 0);

                        if (change > 0) {
                            return 'text-emerald-600';
                        }

                        if (change < 0) {
                            return 'text-rose-600';
                        }

                        return 'text-slate-700';
                    },

                    topSchoolTrendLinePoints() {
                        return this.topSchoolModal.trend.chartPoints
                            .map(point => point.x + ',' + point.y)
                            .join(' ');
                    },

                    showTopSchoolTrendTooltip(point) {
                        this.topSchoolModal.trend.tooltip = point;
                    },

                    handleTopSchoolTrendHover(event) {
                        const points = this.topSchoolModal.trend.chartPoints;

                        if (!Array.isArray(points) || points.length === 0) {
                            this.topSchoolModal.trend.tooltip = null;
                            return;
                        }

                        const svg = this.$refs.topSchoolTrendChart;

                        if (!svg) {
                            return;
                        }

                        const rect = svg.getBoundingClientRect();

                        if (!rect.width) {
                            return;
                        }

                        const relativeX = ((event.clientX - rect.left) / rect.width) * 1000;
                        let nearestPoint = points[0];
                        let nearestDistance = Math.abs(relativeX - points[0].x);

                        points.forEach(point => {
                            const distance = Math.abs(relativeX - point.x);

                            if (distance < nearestDistance) {
                                nearestPoint = point;
                                nearestDistance = distance;
                            }
                        });

                        this.topSchoolModal.trend.tooltip = nearestPoint;
                    },

                    hideTopSchoolTrendTooltip() {
                        this.topSchoolModal.trend.tooltip = null;
                    },

                    topSchoolTrendTooltipBox() {
                        const point = this.topSchoolModal.trend.tooltip;

                        if (!point) {
                            return 'display:none;';
                        }

                        const left = Math.max(16, Math.min(760, point.x - 104));
                        const top = Math.max(0, point.y - 14);

                        return 'left:' + left + 'px;top:' + top + 'px;transform:translateY(-100%);';
                    },

                    topSchoolTrendTooltipLabel() {
                        if (!this.topSchoolModal.trend.tooltip) {
                            return '-';
                        }

                        return 'ปี ' + this.topSchoolModal.trend.tooltip.academic_year + ' / รอบ ' + this.topSchoolModal.trend.tooltip.term;
                    },

                    topSchoolTrendChangeClass() {
                        const change = Number(this.topSchoolModal.trend.summary ? this.topSchoolModal.trend.summary.change : 0);

                        if (change > 0) {
                            return 'text-emerald-600';
                        }

                        if (change < 0) {
                            return 'text-rose-600';
                        }

                        return 'text-slate-700';
                    },

                    signedNumber(value) {
                        const numeric = Number(value || 0);

                        if (numeric > 0) {
                            return '+' + this.formatNumber(numeric);
                        }

                        return this.formatNumber(numeric);
                    },

                    showToast(message, type = 'success') {
                        this.toast = { show: true, message, type };
                        setTimeout(() => { this.toast.show = false; }, 3500);
                    }
                };
            }
        </script>
    @endpush
</x-layout>

