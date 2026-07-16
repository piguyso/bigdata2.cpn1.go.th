<x-layout>
    <x-slot:title>ข้อมูล asset | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="assetDashboard()" x-init="init()">
        <header class="mb-8 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="{{ url('/') }}" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ข้อมูลด้านงบประมาณ</span>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ข้อมูล asset</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ข้อมูล asset</h2>
                <p class="text-slate-500 text-sm mt-1">แดชบอร์ดสิ่งก่อสร้างและ logo โรงเรียนจาก OBEC Asset ที่นำเข้าไว้ในฐานข้อมูล local</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-extrabold text-slate-600">
                    <i class="fa-solid fa-location-dot text-orange-500 mr-2"></i>
                    <span x-text="dashboard?.latestImport?.areaName || '{{ $webSubtitle }}'"></span>
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
                <div class="md:col-span-1 xl:col-span-2">
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">โรงเรียน</label>
                    <div class="relative" @click.away="schoolDropdownOpen = false">
                        <button type="button"
                                @click="toggleSchoolDropdown()"
                                class="form-input w-full text-left flex items-center justify-between gap-3">
                            <span class="truncate"
                                  :class="selectedSchoolOption() ? 'text-slate-700' : 'text-slate-400'"
                                  x-text="selectedSchoolOption() ? selectedSchoolOption().schoolName : 'ทุกโรงเรียน'"></span>
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
                                <template x-if="filteredSchools().length === 0">
                                    <div class="px-4 py-6 text-center text-xs font-bold text-slate-400">ไม่พบโรงเรียนที่ค้นหา</div>
                                </template>

                                <template x-for="school in filteredSchools()" :key="school.schoolSmis || 'all'">
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
                                                <div class="mt-1 text-[11px] font-bold text-slate-400" x-text="school.schoolSmis || 'ทุกโรงเรียนในเขตพื้นที่'"></div>
                                            </div>
                                        </div>
                                        <i x-show="String(filters.schoolSmis || '') === String(school.schoolSmis || '')"
                                           class="fa-solid fa-check text-orange-500 text-xs mt-1 shrink-0"
                                           x-cloak></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">ประเภทสิ่งก่อสร้าง</label>
                    <select class="form-input w-full" x-model="filters.buildingType" @change="filterChanged()">
                        <option value="">ทุกประเภท</option>
                        <template x-for="type in availableTypes" :key="type.value">
                            <option :value="type.value" x-text="type.label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase mb-2">สภาพการใช้งาน</label>
                    <select class="form-input w-full" x-model="filters.condition" @change="filterChanged()">
                        <option value="">ทุกสภาพ</option>
                        <template x-for="condition in availableConditions" :key="condition.value">
                            <option :value="condition.value" x-text="condition.label"></option>
                        </template>
                    </select>
                </div>
            </div>
        </section>

        <div x-show="loading" class="py-28 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังดึงข้อมูล asset...</span>
        </div>

        <div x-show="!loading && error" class="bg-white border border-rose-100 rounded-3xl p-10 text-center" x-cloak>
            <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700">ไม่สามารถดึงข้อมูล asset ได้</h3>
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
                                <span class="text-2xl font-extrabold text-slate-800" x-text="formatMetric(item)"></span>
                                <span class="text-[10px] font-bold text-slate-400" x-text="item.suffix"></span>
                            </div>
                            <span class="text-[9px] text-slate-400 block mt-0.5" x-text="item.note"></span>
                        </div>
                    </div>
                </template>
            </section>

            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tab in tabs" :key="tab.key">
                            <button type="button"
                                    @click="selectTab(tab.key)"
                                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-extrabold transition"
                                    :class="activeTab === tab.key ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:border-orange-200 hover:text-orange-600'">
                                <i :class="tab.icon"></i>
                                <span x-text="tab.label"></span>
                                <span class="rounded-full px-2 py-0.5 text-[10px]"
                                      :class="activeTab === tab.key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-400'"
                                      x-text="formatNumber(tabTotal(tab.key))"></span>
                            </button>
                        </template>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400" x-text="dashboard.latestImport ? ('นำเข้า #' + dashboard.latestImport.id + ' ' + formatDateTime(dashboard.latestImport.importedAt)) : '-'"></span>
                </div>

                <div x-show="activeTab === 'schools'" x-cloak>
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2"><i class="fa-solid fa-school text-orange-500"></i> โรงเรียนและจำนวนสิ่งก่อสร้าง</h3>
                        <p class="text-[11px] text-slate-400 mt-1">แสดงข้อมูลทั้งหมดแบบแบ่งหน้า ตามตัวกรองปัจจุบัน</p>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full min-w-[1100px] divide-y divide-slate-100 table-auto">
                            <thead class="bg-white">
                                <tr class="text-left text-[11px] font-extrabold text-slate-400 uppercase">
                                    <th class="px-5 py-3">โรงเรียน</th>
                                    <th class="px-5 py-3 text-right">สิ่งก่อสร้าง</th>
                                    <th class="px-5 py-3 text-right">งบประมาณ</th>
                                    <th class="px-5 py-3 text-right">อายุเฉลี่ย</th>
                                    <th class="px-5 py-3 text-right">เก่าสุด</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="school in dashboard.schoolRows.data" :key="school.school_smis">
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-5 py-3">
                                            <a :href="schoolBuildingsUrl(school)"
                                               @click.prevent="openSchoolBuildings(school)"
                                               class="flex items-center gap-3 rounded-2xl -m-2 p-2 transition hover:bg-orange-50">
                                                <img x-show="school.logo_url" :src="school.logo_url" alt="" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-100 p-1" x-cloak>
                                                <div x-show="!school.logo_url" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0" x-cloak>
                                                    <i class="fa-solid fa-school text-xs"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-extrabold text-slate-700 truncate hover:text-orange-600" x-text="school.school_name"></div>
                                                    <div class="text-[11px] text-slate-400 font-bold" x-text="school.school_smis"></div>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-right text-xs font-extrabold text-slate-700" x-text="formatNumber(school.buildings_count)"></td>
                                        <td class="px-5 py-3 text-right text-xs font-bold text-slate-500" x-text="formatMoney(school.total_budget)"></td>
                                        <td class="px-5 py-3 text-right text-xs font-bold text-slate-500" x-text="school.average_age ? school.average_age + ' ปี' : '-'"></td>
                                        <td class="px-5 py-3 text-right text-xs font-bold text-slate-500" x-text="school.oldest_year || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <span class="text-[11px] font-bold text-slate-400" x-text="paginationLabel('schools')"></span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="changePage('schools', -1)" :disabled="pageMeta('schools').current_page <= 1" class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="px-3 py-2 rounded-xl bg-slate-50 text-xs font-extrabold text-slate-600" x-text="pageMeta('schools').current_page + ' / ' + pageMeta('schools').last_page"></span>
                            <button type="button" @click="changePage('schools', 1)" :disabled="pageMeta('schools').current_page >= pageMeta('schools').last_page" class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'types'" x-cloak>
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2"><i class="fa-solid fa-chart-simple text-orange-500"></i> สรุปตามประเภทสิ่งก่อสร้าง</h3>
                        <p class="text-[11px] text-slate-400 mt-1">สรุปจำนวนและงบประมาณรวม แสดงข้อมูลทั้งหมดแบบแบ่งหน้า</p>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)] gap-0">
                        <div class="divide-y divide-slate-100">
                            <template x-for="type in dashboard.typeSummary.data" :key="type.label">
                                <div class="px-6 py-4 hover:bg-slate-50/70 transition">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div class="text-xs font-extrabold text-slate-700 truncate" x-text="type.label"></div>
                                        <div class="text-xs font-extrabold text-slate-900" x-text="formatNumber(type.count) + ' รายการ'"></div>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-[11px] text-slate-400 font-bold">
                                        <span>งบประมาณรวม</span>
                                        <span x-text="formatMoney(type.budget)"></span>
                                    </div>
                                </div>
                            </template>
                            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <span class="text-[11px] font-bold text-slate-400" x-text="paginationLabel('types')"></span>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="changePage('types', -1)" :disabled="pageMeta('types').current_page <= 1" class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                                    <span class="px-3 py-2 rounded-xl bg-slate-50 text-xs font-extrabold text-slate-600" x-text="pageMeta('types').current_page + ' / ' + pageMeta('types').last_page"></span>
                                    <button type="button" @click="changePage('types', 1)" :disabled="pageMeta('types').current_page >= pageMeta('types').last_page" class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
                                </div>
                            </div>
                        </div>
                        <aside class="border-t xl:border-t-0 xl:border-l border-slate-100 p-6 space-y-6 bg-slate-50/40">
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-screwdriver-wrench text-emerald-500"></i> สภาพการใช้งาน
                                </h4>
                                <div class="space-y-3">
                                    <template x-for="item in dashboard.conditionSummary" :key="item.label">
                                        <div>
                                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                                <span class="text-slate-600" x-text="item.label"></span>
                                                <span class="text-slate-400" x-text="formatNumber(item.count) + ' (' + item.percent + '%)'"></span>
                                            </div>
                                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full bg-emerald-500 rounded-full" :style="'width:' + Math.max(item.percent, 2) + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-circle-check text-sky-500"></i> สถานะการใช้งาน
                                </h4>
                                <div class="space-y-3">
                                    <template x-for="item in dashboard.statusSummary" :key="item.label">
                                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                            <span class="text-xs font-extrabold text-slate-700" x-text="item.label"></span>
                                            <span class="text-xs font-bold text-slate-400" x-text="formatNumber(item.count) + ' รายการ'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div x-show="activeTab === 'buildings'" x-cloak>
                    <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2"><i class="fa-solid fa-list-check text-sky-500"></i> รายการสิ่งก่อสร้าง</h3>
                            <p class="text-[11px] text-slate-400 mt-1" x-text="selectedSchoolOption() ? ('รายการสิ่งก่อสร้างและรายละเอียดของ ' + selectedSchoolOption().schoolName) : 'รายการสิ่งก่อสร้างทั้งหมดจาก OBEC Asset แบบแบ่งหน้า'"></p>
                        </div>
                        <button type="button"
                                x-show="filters.schoolSmis"
                                @click="clearSchoolFilter()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-[11px] font-extrabold text-slate-500 transition hover:border-orange-200 hover:text-orange-600"
                                x-cloak>
                            <i class="fa-solid fa-xmark"></i>
                            แสดงทุกโรงเรียน
                        </button>
                    </div>
                    <div class="p-5 bg-slate-50/50">
                        <div x-show="dashboard.buildingRows.data.length === 0" class="rounded-3xl border border-dashed border-slate-200 bg-white py-14 text-center text-sm font-bold text-slate-400">
                            ไม่พบรายการสิ่งก่อสร้างตามเงื่อนไขที่เลือก
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                            <template x-for="(item, index) in dashboard.buildingRows.data" :key="item.id || (index + item.school_smis + item.building_type)">
                                <article class="rounded-3xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                                    <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)]">
                                        <div class="bg-slate-100 min-h-[220px]">
                                            <template x-if="selectedBuildingImage(item)">
                                                <div class="block h-full">
                                                    <img :src="selectedBuildingImage(item)" :alt="item.building_type || item.building_model" class="h-full min-h-[220px] w-full object-cover">
                                                </div>
                                            </template>
                                            <div x-show="!selectedBuildingImage(item)" class="h-full min-h-[220px] flex items-center justify-center text-slate-300">
                                                <i class="fa-solid fa-building text-4xl"></i>
                                            </div>
                                        </div>

                                        <div class="p-5 min-w-0">
                                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-[11px] font-extrabold text-orange-600 uppercase" x-text="item.building_type || 'ไม่ระบุประเภท'"></div>
                                                    <h4 class="mt-1 text-base font-extrabold text-slate-800 leading-snug" x-text="item.building_model || '-'"></h4>
                                                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                                                        <span x-text="item.school_name"></span>
                                                        <span class="mx-1">/</span>
                                                        <span x-text="item.school_smis"></span>
                                                    </p>
                                                </div>
                                                <span class="inline-flex w-fit rounded-xl bg-slate-100 px-3 py-1.5 text-[11px] font-extrabold text-slate-600" x-text="item.condition || '-'"></span>
                                            </div>

                                            <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2">
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase">ปีสร้าง</div>
                                                    <div class="mt-0.5 text-xs font-extrabold text-slate-700" x-text="item.construction_year || '-'"></div>
                                                </div>
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase">อายุ</div>
                                                    <div class="mt-0.5 text-xs font-extrabold text-slate-700" x-text="item.age_years ? item.age_years + ' ปี' : '-'"></div>
                                                </div>
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase">ห้องจริง</div>
                                                    <div class="mt-0.5 text-xs font-extrabold text-slate-700" x-text="formatNumber(item.rooms_actual)"></div>
                                                </div>
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase">สถานะ</div>
                                                    <div class="mt-0.5 text-xs font-extrabold text-slate-700 truncate" x-text="item.usage_status || '-'"></div>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                                <div>
                                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">งบประมาณ</div>
                                                    <div class="mt-1 font-extrabold text-slate-800" x-text="formatMoney(item.budget)"></div>
                                                    <div class="mt-1 text-[11px] font-bold text-slate-400" x-text="item.budget_source || '-'"></div>
                                                </div>
                                                <div>
                                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase">จำนวนห้อง / ต่อเติม</div>
                                                    <div class="mt-1 font-bold text-slate-600" x-text="roomSummary(item)"></div>
                                                    <div class="mt-1 text-[11px] font-bold text-slate-400" x-text="extensionSummary(item)"></div>
                                                </div>
                                            </div>

                                            <div x-show="item.images && item.images.length > 1" class="mt-4 flex gap-2 overflow-x-auto pb-1" x-cloak>
                                                <template x-for="image in item.images" :key="image">
                                                    <button type="button"
                                                            @click="setBuildingImage(item, image)"
                                                            class="block w-16 h-14 rounded-xl overflow-hidden border bg-slate-50 shrink-0 transition"
                                                            :class="selectedBuildingImage(item) === image ? 'border-orange-500 ring-2 ring-orange-100' : 'border-slate-100 hover:border-orange-200'">
                                                        <img :src="image" alt="" class="w-full h-full object-cover">
                                                    </button>
                                                </template>
                                            </div>

                                            <details class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                                <summary class="cursor-pointer text-[11px] font-extrabold text-slate-500">รายละเอียดจาก OBEC Asset</summary>
                                                <div class="mt-3 space-y-1.5 text-[11px] font-bold text-slate-500">
                                                    <template x-for="line in item.detail_lines" :key="line">
                                                        <div x-text="line"></div>
                                                    </template>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </article>
                            </template>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <span class="text-[11px] font-bold text-slate-400" x-text="paginationLabel('buildings')"></span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="changePage('buildings', -1)" :disabled="pageMeta('buildings').current_page <= 1" class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="px-3 py-2 rounded-xl bg-slate-50 text-xs font-extrabold text-slate-600" x-text="pageMeta('buildings').current_page + ' / ' + pageMeta('buildings').last_page"></span>
                            <button type="button" @click="changePage('buildings', 1)" :disabled="pageMeta('buildings').current_page >= pageMeta('buildings').last_page" class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
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
        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-size: 0.75rem;
            transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
        }
        .pagination-btn:hover:not(:disabled) {
            border-color: #fed7aa;
            color: #ea580c;
            background: #fff7ed;
        }
        .pagination-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        [x-cloak] { display: none !important; }
    </style>

    @push('scripts')
        <script>
            function assetDashboard() {
                return {
                    apiUrl: '{{ route('api.assets.dashboard') }}',
                    dashboard: null,
                    loading: true,
                    error: '',
                    activeTab: 'schools',
                    tabs: [
                        { key: 'schools', label: 'โรงเรียนและจำนวนสิ่งก่อสร้าง', icon: 'fa-solid fa-school' },
                        { key: 'types', label: 'สรุปตามประเภทสิ่งก่อสร้าง', icon: 'fa-solid fa-chart-simple' },
                        { key: 'buildings', label: 'รายการสิ่งก่อสร้าง', icon: 'fa-solid fa-list-check' },
                    ],
                    pages: { schools: 1, types: 1, buildings: 1 },
                    perPage: 15,
                    schoolDropdownOpen: false,
                    schoolSearch: '',
                    selectedBuildingImages: {},
                    filters: { schoolSmis: '', buildingType: '', condition: '' },
                    init() {
                        const params = new URLSearchParams(window.location.search);
                        const tab = params.get('tab');
                        if (['schools', 'types', 'buildings'].includes(tab)) {
                            this.activeTab = tab;
                        }
                        this.filters.schoolSmis = params.get('school_smis') || '';
                        this.filters.buildingType = params.get('building_type') || '';
                        this.filters.condition = params.get('condition') || '';
                        this.fetchDashboard();
                    },
                    get availableSchools() { return this.dashboard?.availableSchools || []; },
                    get availableTypes() { return this.dashboard?.availableTypes || []; },
                    get availableConditions() { return this.dashboard?.availableConditions || []; },
                    filteredSchools() {
                        const keyword = this.schoolSearch.trim().toLowerCase();
                        const schools = [
                            { schoolSmis: '', schoolName: 'ทุกโรงเรียน', logoUrl: null },
                            ...this.availableSchools.map(school => ({
                                schoolSmis: school.school_smis || '',
                                schoolName: school.school_name || '',
                                logoUrl: school.logo_url || null,
                            }))
                        ];

                        if (!keyword) return schools;

                        return schools.filter(school =>
                            (school.schoolName || '').toLowerCase().includes(keyword) ||
                            (school.schoolSmis || '').toLowerCase().includes(keyword)
                        );
                    },
                    fetchDashboard(forceLoading = false) {
                        this.loading = forceLoading || !this.dashboard;
                        this.error = '';
                        axios.get(this.apiUrl, {
                            params: {
                                school_smis: this.filters.schoolSmis,
                                building_type: this.filters.buildingType,
                                condition: this.filters.condition,
                                school_page: this.pages.schools,
                                type_page: this.pages.types,
                                building_page: this.pages.buildings,
                                per_page: this.perPage,
                            }
                        }).then(response => {
                            this.dashboard = response.data || {};
                            this.filters.schoolSmis = this.dashboard.selectedSchoolSmis || '';
                            this.filters.buildingType = this.dashboard.selectedBuildingType || '';
                            this.filters.condition = this.dashboard.selectedCondition || '';
                            this.pages.schools = this.pageMeta('schools').current_page;
                            this.pages.types = this.pageMeta('types').current_page;
                            this.pages.buildings = this.pageMeta('buildings').current_page;
                            this.selectedBuildingImages = {};
                            if (this.dashboard.message) this.error = this.dashboard.message;
                        }).catch(error => {
                            this.error = error.response?.data?.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล asset';
                        }).finally(() => this.loading = false);
                    },
                    toggleSchoolDropdown() {
                        this.schoolDropdownOpen = !this.schoolDropdownOpen;
                        if (this.schoolDropdownOpen) this.$nextTick(() => this.$refs.schoolSearchInput?.focus());
                    },
                    selectSchool(school) {
                        this.filters.schoolSmis = school?.schoolSmis || '';
                        this.schoolDropdownOpen = false;
                        this.schoolSearch = '';
                        this.resetPages();
                        this.fetchDashboard();
                        this.syncUrl();
                    },
                    selectedSchoolOption() {
                        if (!this.filters.schoolSmis) return null;

                        const school = this.availableSchools.find(school => school.school_smis === this.filters.schoolSmis);
                        return school ? {
                            schoolSmis: school.school_smis,
                            schoolName: school.school_name,
                            logoUrl: school.logo_url || null,
                        } : null;
                    },
                    selectTab(tab) {
                        this.activeTab = tab;
                        this.syncUrl();
                    },
                    schoolBuildingsUrl(school) {
                        const params = new URLSearchParams();
                        params.set('tab', 'buildings');
                        params.set('school_smis', school.school_smis || school.schoolSmis || '');

                        return '{{ route('asset.dashboard') }}' + '?' + params.toString();
                    },
                    openSchoolBuildings(school) {
                        this.activeTab = 'buildings';
                        this.filters.schoolSmis = school.school_smis || school.schoolSmis || '';
                        this.pages.buildings = 1;
                        this.fetchDashboard();
                        this.syncUrl();
                    },
                    clearSchoolFilter() {
                        this.filters.schoolSmis = '';
                        this.resetPages();
                        this.fetchDashboard();
                        this.syncUrl();
                    },
                    resetPages() {
                        this.pages = { schools: 1, types: 1, buildings: 1 };
                    },
                    filterChanged() {
                        this.resetPages();
                        this.fetchDashboard();
                        this.syncUrl();
                    },
                    changePage(tab, delta) {
                        const meta = this.pageMeta(tab);
                        const nextPage = Math.min(meta.last_page, Math.max(1, meta.current_page + delta));
                        if (nextPage === meta.current_page) return;
                        this.pages[tab] = nextPage;
                        this.fetchDashboard();
                        this.syncUrl();
                    },
                    syncUrl() {
                        const params = new URLSearchParams();
                        if (this.activeTab !== 'schools') params.set('tab', this.activeTab);
                        if (this.filters.schoolSmis) params.set('school_smis', this.filters.schoolSmis);
                        if (this.filters.buildingType) params.set('building_type', this.filters.buildingType);
                        if (this.filters.condition) params.set('condition', this.filters.condition);
                        const url = params.toString()
                            ? '{{ route('asset.dashboard') }}' + '?' + params.toString()
                            : '{{ route('asset.dashboard') }}';
                        window.history.replaceState({}, '', url);
                    },
                    pageMeta(tab) {
                        const map = {
                            schools: this.dashboard?.schoolRows?.meta,
                            types: this.dashboard?.typeSummary?.meta,
                            buildings: this.dashboard?.buildingRows?.meta,
                        };
                        return map[tab] || { current_page: 1, last_page: 1, total: 0, from: 0, to: 0, per_page: this.perPage };
                    },
                    paginationLabel(tab) {
                        const meta = this.pageMeta(tab);
                        if (!meta.total) return 'ไม่มีข้อมูล';
                        return 'แสดง ' + this.formatNumber(meta.from) + '-' + this.formatNumber(meta.to) + ' จาก ' + this.formatNumber(meta.total) + ' รายการ';
                    },
                    tabTotal(tab) {
                        return this.pageMeta(tab).total || 0;
                    },
                    buildingImageKey(item) {
                        return String(item.id || [item.school_smis, item.building_type, item.building_model].join('|'));
                    },
                    selectedBuildingImage(item) {
                        const key = this.buildingImageKey(item);
                        return this.selectedBuildingImages[key] || item.main_image_url || item.images?.[0] || '';
                    },
                    setBuildingImage(item, image) {
                        this.selectedBuildingImages[this.buildingImageKey(item)] = image;
                    },
                    formatNumber(value) { return Number(value || 0).toLocaleString('th-TH'); },
                    formatMoney(value) {
                        const amount = Number(value || 0);
                        if (amount <= 0) return '-';
                        return amount.toLocaleString('th-TH', { maximumFractionDigits: 0 }) + ' บาท';
                    },
                    formatMetric(item) {
                        if (item.suffix === 'บาท') return Number(item.value || 0).toLocaleString('th-TH', { maximumFractionDigits: 0 });
                        return Number(item.value || 0).toLocaleString('th-TH');
                    },
                    nullableNumber(value) {
                        return value === null || value === undefined || value === '' ? '-' : this.formatNumber(value);
                    },
                    roomSummary(item) {
                        return [
                            'ตามแบบ ' + this.nullableNumber(item.rooms_design),
                            'จริง ' + this.nullableNumber(item.rooms_actual),
                            'พิเศษ ' + this.nullableNumber(item.rooms_special),
                        ].join(' / ');
                    },
                    extensionSummary(item) {
                        return [
                            'ต่อเติมห้องเรียน ' + this.nullableNumber(item.extension_classroom),
                            'ต่อเติมห้องพิเศษ ' + this.nullableNumber(item.extension_special),
                        ].join(' / ');
                    },
                    formatDateTime(value) {
                        if (!value) return '-';
                        const date = new Date(value);
                        return Number.isNaN(date.getTime()) ? value : date.toLocaleString('th-TH');
                    },
                };
            }
        </script>
    @endpush
</x-layout>

