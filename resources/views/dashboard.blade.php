<x-layout>
    <x-slot:title>แดชบอร์ดสถิติบุคลากร | EE CPN1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="dashboardManager()" x-init="init()">
        
        <!-- Toast Notification System -->
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
             x-cloak>
            <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
            <span x-text="toast.message"></span>
        </div>

        <!-- Header Title Section -->
        <header class="mb-8">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                <a href="{{ route('reports.index') }}" class="hover:text-emerald-600 transition">หน้าหลัก</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-slate-600">แดชบอร์ดสรุปสถิติ</span>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5 font-bold">แดชบอร์ดสรุปข้อมูลสถิติ</h2>
            <p class="text-slate-500 text-sm mt-1">
                รายงานสถิติอัตราส่วนบุคลากรทางการศึกษา วุฒิการศึกษา ผลประเมินสมรรถนะ และแผนที่ข้อมูลเชิงลึก
            </p>
        </header>

        <!-- Loading State for aggregate -->
        <div x-show="loadingStats" class="py-32 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังประมวลผลข้อมูลสถิติ...</span>
        </div>

        <div x-show="!loadingStats" class="space-y-6" x-cloak>
            
            <!-- 1. Stats Counter Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Records -->
                <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-extrabold text-slate-400 uppercase">บุคลากรในฐานข้อมูล</span>
                        <span class="text-2xl font-extrabold text-slate-800" x-text="stats.totalRecords">0</span>
                        <span class="text-[9px] text-slate-400 block mt-0.5">ท่าน</span>
                    </div>
                </div>

                <!-- Today Register -->
                <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-extrabold text-slate-400 uppercase">ลงทะเบียนใหม่วันนี้</span>
                        <span class="text-2xl font-extrabold text-slate-800" x-text="stats.todayRecords">0</span>
                        <span class="text-[9px] text-slate-400 block mt-0.5" x-text="`คิดเป็น ${pct(stats.todayRecords, stats.totalRecords)}`"></span>
                    </div>
                </div>

                <!-- Month Register -->
                <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-extrabold text-slate-400 uppercase">บันทึกเพิ่มเดือนนี้</span>
                        <span class="text-2xl font-extrabold text-slate-800" x-text="stats.thisMonthRecords">0</span>
                        <span class="text-[9px] text-slate-400 block mt-0.5" x-text="`คิดเป็น ${pct(stats.thisMonthRecords, stats.totalRecords)}`"></span>
                    </div>
                </div>

                <!-- Avg Age -->
                <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-cake-candles"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-extrabold text-slate-400 uppercase">อายุเฉลี่ยบุคลากร</span>
                        <span class="text-2xl font-extrabold text-slate-800" x-text="stats.avgAge || '-'">0</span>
                        <span class="text-[9px] text-slate-400 block mt-0.5">ปี</span>
                    </div>
                </div>
            </div>

            <!-- Notice Info -->
            <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl text-xs text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-emerald-600 text-sm shrink-0"></i>
                <span><strong>แนะนำการใช้งาน:</strong> ท่านสามารถกดคลิกที่แถบข้อมูลสถิติต่างๆ ในแดชบอร์ดด้านล่าง เพื่อเปิดรายชื่อครูเจาะลึก (Drilldown) แยกรายกลุ่มได้ทันที</span>
            </div>

            <!-- 2. Charts / Data Summary Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Position ratio -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie text-emerald-500"></i> สัดส่วนตำแหน่งบุคลากร
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in stats.positionSummary">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-1.5 rounded-xl transition duration-150" 
                                 @click="drilldown('position', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน (${pct(item.total, stats.totalRecords)})`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Education levels -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-graduation-cap text-sky-500"></i> สถิติวุฒิการศึกษาสูงสุด
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in stats.educationLevelSummary">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-1.5 rounded-xl transition duration-150" 
                                 @click="drilldown('education', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน (${pct(item.total, stats.totalRecords)})`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-sky-500 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Academic Rank summary -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-award text-amber-500"></i> สถิติตามวิทยฐานะบุคลากร
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in stats.academicRankSummary">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-1.5 rounded-xl transition duration-150" 
                                 @click="drilldown('academic_rank', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน (${pct(item.total, stats.totalRecords)})`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- CEFR Exams Summary -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-language text-indigo-500"></i> สถิติผลประเมิน CEFR ภาษาอังกฤษ
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- OBEC CEFR -->
                        <div class="border border-slate-100 p-4 rounded-2xl space-y-3 bg-indigo-50/30">
                            <span class="block text-xs font-extrabold text-indigo-800 border-b pb-1">ผลสอบจาก สพฐ. (OBEC)</span>
                            <div class="space-y-2">
                                <template x-for="item in filterLang(stats.cefrSummary, 'obec')">
                                    <div class="flex justify-between items-center text-[11px] font-bold text-slate-700 cursor-pointer hover:underline"
                                         @click="drilldown('cefr', item.cefr_level)">
                                        <span x-text="`ระดับ ${item.cefr_level}`"></span>
                                        <span class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full text-[9px]" x-text="`${item.total} ท่าน`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- OTHER CEFR -->
                        <div class="border border-slate-100 p-4 rounded-2xl space-y-3 bg-slate-50/50">
                            <span class="block text-xs font-extrabold text-slate-650 border-b pb-1">ผลสอบสถาบันอื่น</span>
                            <div class="space-y-2">
                                <template x-for="item in filterLang(stats.cefrSummary, 'other')">
                                    <div class="flex justify-between items-center text-[11px] font-bold text-slate-700 cursor-pointer hover:underline"
                                         @click="drilldown('cefr', item.cefr_level)">
                                        <span x-text="`ระดับ ${item.cefr_level}`"></span>
                                        <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full text-[9px]" x-text="`${item.total} ท่าน`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HSK Exams Summary -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-flag-usa text-rose-500" style="display:none"></i>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-earth-asia text-rose-500"></i> สถิติผลประเมิน HSK ภาษาจีน</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- OBEC HSK -->
                        <div class="border border-slate-100 p-4 rounded-2xl space-y-3 bg-rose-50/30">
                            <span class="block text-xs font-extrabold text-rose-800 border-b pb-1">สพฐ. (OBEC)</span>
                            <div class="space-y-2">
                                <template x-for="item in filterLang(stats.hskSummary, 'obec')">
                                    <div class="flex justify-between items-center text-[11px] font-bold text-slate-700 cursor-pointer hover:underline"
                                         @click="drilldown('hsk', item.hsk_level)">
                                        <span class="truncate pr-1" x-text="item.hsk_level"></span>
                                        <span class="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[9px] shrink-0" x-text="`${item.total} ท่าน`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- OTHER HSK -->
                        <div class="border border-slate-100 p-4 rounded-2xl space-y-3 bg-slate-50/50">
                            <span class="block text-xs font-extrabold text-slate-650 border-b pb-1">สถาบันอื่น</span>
                            <div class="space-y-2">
                                <template x-for="item in filterLang(stats.hskSummary, 'other')">
                                    <div class="flex justify-between items-center text-[11px] font-bold text-slate-700 cursor-pointer hover:underline"
                                         @click="drilldown('hsk', item.hsk_level)">
                                        <span class="truncate pr-1" x-text="item.hsk_level"></span>
                                        <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full text-[9px] shrink-0" x-text="`${item.total} ท่าน`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Network school count -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4 lg:col-span-2">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-school text-emerald-500"></i> สถิติกลุ่มเครือข่ายโรงเรียน (10 อันดับแรก)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="item in stats.networkSummary">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-2 rounded-2xl border border-slate-100 transition duration-150" 
                                 @click="drilldown('network', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-400 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Gender Summary & Accumulated Personal stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:col-span-2">
                    <!-- 1. Gender Ratio Card -->
                    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-venus-mars text-indigo-500"></i> สัดส่วนบุคลากรแยกตามเพศ
                        </h3>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <template x-for="item in stats.genderSummary">
                                <div class="p-4 rounded-2xl border border-slate-100/70 hover:border-indigo-300 hover:shadow-md transition cursor-pointer flex items-center gap-3"
                                     @click="drilldown('gender', item.label)">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                         :class="item.label === 'ชาย' ? 'bg-sky-50 text-sky-650' : 'bg-rose-50 text-rose-650'">
                                        <i :class="item.label === 'ชาย' ? 'fa-solid fa-mars text-lg' : 'fa-solid fa-venus text-lg'"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-extrabold text-slate-400 uppercase" x-text="`เพศ${item.label}`"></span>
                                        <span class="text-lg font-extrabold text-slate-800" x-text="`${item.total} ท่าน`"></span>
                                        <span class="text-[9px] text-slate-400 block" x-text="pct(item.total, stats.totalRecords)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 2. Academic & Personal Growth counts -->
                    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-emerald-500"></i> สถิติการพัฒนาตนเองสะสมทั้งหมด
                        </h3>
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-2xl flex items-center gap-2.5">
                                <i class="fa-solid fa-trophy text-amber-500 text-sm shrink-0"></i>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400">รางวัลเชิดชูเกียรติ</span>
                                    <span class="text-xs font-extrabold text-slate-700" x-text="`${stats.counts?.awards || 0} รายการ`"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-2xl flex items-center gap-2.5">
                                <i class="fa-solid fa-language text-indigo-500 text-sm shrink-0"></i>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400">ใบรับรอง CEFR</span>
                                    <span class="text-xs font-extrabold text-slate-700" x-text="`${stats.counts?.cefr || 0} ใบ`"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-2xl flex items-center gap-2.5">
                                <i class="fa-solid fa-book text-sky-500 text-sm shrink-0"></i>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400">วิชาสอนทั้งหมด</span>
                                    <span class="text-xs font-extrabold text-slate-700" x-text="`${stats.counts?.subjects || 0} รายวิชา`"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-2xl flex items-center gap-2.5">
                                <i class="fa-solid fa-earth-asia text-rose-500 text-sm shrink-0"></i>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400">ใบรับรอง HSK</span>
                                    <span class="text-xs font-extrabold text-slate-700" x-text="`${stats.counts?.hsk || 0} ใบ`"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject taught summary (Top 10) -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-book-bookmark text-violet-500"></i> สถิติรายวิชาที่สอนสูงสุด (10 อันดับแรก)
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in stats.subjectTop">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-1.5 rounded-xl transition duration-150" 
                                 @click="drilldown('subject', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-violet-500 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Recruitment Subject summary (Top 10) -->
                <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-user-check text-rose-500"></i> สถิติตามวิชาที่สอบบรรจุ (10 อันดับแรก)
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in stats.recruitmentSubjectSummary">
                            <div class="space-y-1.5 cursor-pointer hover:bg-slate-50/50 p-1.5 rounded-xl transition duration-150" 
                                 @click="drilldown('recruitment_subject', item.label)">
                                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                                    <span x-text="item.label"></span>
                                    <span x-text="`${item.total} ท่าน`"></span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-rose-50 rounded-full" :style="`width: ${pct(item.total, stats.totalRecords)}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- 3. Drilldown Target Content (SPA-like list dynamically rendered) -->
            <div id="drilldown-section" x-show="drilldownData.active" class="bg-white border border-slate-100 p-6 rounded-3xl shadow-md space-y-6 scroll-mt-20" x-cloak>
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <div class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-widest">ผลการดึงข้อมูลเจาะลึก (Drilldown)</div>
                        <h3 class="text-lg font-extrabold text-slate-800 mt-1" 
                            x-text="`กลุ่ม: ${drilldownData.title} (${drilldownData.records.length} ท่าน)`"></h3>
                    </div>
                    <button type="button" @click="closeDrilldown()" 
                            class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Loading Drilldown -->
                <div x-show="loadingDrilldown" class="py-10 flex flex-col items-center justify-center gap-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-emerald-500"></i>
                    <span class="text-xs font-bold text-slate-400">กำลังดึงข้อมูลกลุ่มเป้าหมาย...</span>
                </div>

                <!-- Drilldown Cards Grid -->
                <div x-show="!loadingDrilldown && drilldownData.records.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="record in drilldownData.records" :key="record.id">
                        <div @click="openModal(record)" 
                             class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 hover:bg-white hover:border-emerald-300 hover:shadow-md transition-all duration-300 cursor-pointer flex gap-4">
                            
                            <div class="w-12 h-12 rounded-full overflow-hidden border shrink-0 bg-white flex items-center justify-center">
                                <template x-if="record.profile_image_url || record.profile_image_path">
                                    <img :src="record.profile_image_url || record.profile_image_path" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!(record.profile_image_url || record.profile_image_path)">
                                    <i class="fa-solid fa-users text-slate-400 text-base"></i>
                                </template>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <h4 class="font-extrabold text-slate-800 text-xs truncate" x-text="`${record.prefix} ${record.first_name} ${record.last_name}`"></h4>
                                <p class="text-[10px] text-slate-500 mt-0.5" x-text="record.position || 'ไม่ระบุตำแหน่ง'"></p>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold truncate" x-text="record.school_name"></p>
                                <p class="text-[10px] text-emerald-600 font-extrabold mt-0.5" x-text="`ความสอดคล้อง: ${record.alignment.label}`"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- DETAIL MODAL (Tabbed and Scrollbar-free) -->
        <div x-show="modal.open" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             x-cloak>
            
            <div class="bg-white w-full max-w-5xl rounded-3xl border border-slate-100 shadow-2xl flex flex-col transform overflow-hidden max-h-[90vh]"
                 @click.away="modal.open = false"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95">
                
                <!-- Sticky Modal Header -->
                <header class="bg-white border-b border-slate-100 px-6 py-5 flex items-center justify-between z-10 shrink-0">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800">ข้อมูลรายละเอียดบุคลากร</h3>
                        <p class="text-[10px] text-slate-400 mt-1 font-bold" x-text="`รหัสชุดข้อมูล: SURV-${modal.data.id}`"></p>
                    </div>
                    <button type="button" @click="modal.open = false" 
                            class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </header>

                <!-- TAB HEADERS -->
                <div class="flex border-b border-slate-100 bg-slate-50/50 shrink-0 overflow-x-auto no-scrollbar">
                    <button type="button" @click="modal.activeTab = 'general'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'general' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-user mr-1.5 text-[11px]"></i>ข้อมูลทั่วไป
                    </button>
                    <button type="button" @click="modal.activeTab = 'teaching'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'teaching' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-chalkboard-user mr-1.5 text-[11px]"></i>งานสอน & วิชาเอก
                    </button>
                    <button type="button" @click="modal.activeTab = 'language'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'language' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-language mr-1.5 text-[11px]"></i>ทักษะภาษา (CEFR/HSK)
                    </button>
                    <button type="button" @click="modal.activeTab = 'awards'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'awards' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-trophy mr-1.5 text-[11px]"></i>รางวัล & ผลงาน
                    </button>
                    <button type="button" @click="modal.activeTab = 'lms'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'lms' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-graduation-cap mr-1.5 text-[11px]"></i>คอร์สเรียน LMS
                    </button>
                </div>

                <!-- TAB CONTENTS -->
                <div class="p-6 h-[520px] overflow-y-auto bg-white">
                    <!-- TAB 1: GENERAL INFO -->
                    <div x-show="modal.activeTab === 'general'" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                            <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-slate-100 shadow-md mx-auto md:col-span-4 bg-slate-50 flex items-center justify-center shrink-0">
                                <template x-if="modal.data.profile_image_url || modal.data.profile_image_path">
                                    <img :src="modal.data.profile_image_url || modal.data.profile_image_path" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!(modal.data.profile_image_url || modal.data.profile_image_path)">
                                    <i class="fa-solid fa-users text-slate-355 text-6xl"></i>
                                </template>
                            </div>
                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs leading-relaxed">
                                <div><span class="font-bold text-slate-400 block mb-0.5">ชื่อ-นามสกุล</span> <span class="font-extrabold text-slate-800 text-sm" x-text="`${modal.data.prefix} ${modal.data.first_name} ${modal.data.last_name}`"></span></div>
                                <div><span class="font-bold text-slate-400 block mb-0.5">ตำแหน่ง</span> <span class="font-semibold text-slate-700" x-text="modal.data.position || 'ไม่ระบุตำแหน่ง'"></span></div>
                                <div><span class="font-bold text-slate-400 block mb-0.5">โรงเรียนสังกัด</span> <span class="font-semibold text-slate-700" x-text="modal.data.school_name"></span></div>
                                <div><span class="font-bold text-slate-400 block mb-0.5">กลุ่มเครือข่าย</span> <span class="font-semibold text-slate-700" x-text="modal.data.school_network"></span></div>
                                <div><span class="font-bold text-slate-400 block mb-0.5">วิชาที่สอบบรรจุ</span> <span class="font-semibold text-slate-750" x-text="modal.data.recruitment_subject || 'ไม่ระบุ'"></span></div>
                                <div><span class="font-bold text-slate-400 block mb-0.5">ภาระงานอื่น/งานพิเศษ</span> <span class="font-semibold text-slate-700" x-text="modal.data.other_workload || '-'"></span></div>
                            </div>
                        </div>

                        <!-- Private Data Simulation -->
                        <div class="border-t border-slate-100 pt-4 bg-slate-50 p-4 rounded-2xl">
                            <span class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-indigo-500"></i> ข้อมูลส่วนบุคคลละเอียดอ่อน (Sensitive Data)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div>
                                    <span class="font-bold text-slate-450 block mb-0.5">เลขประจำตัวประชาชน</span>
                                    <span class="font-mono font-bold text-slate-800" x-text="modal.data.personalid" x-show="modal.data.personalid"></span>
                                    <span class="inline-flex items-center gap-1 text-slate-400 italic text-[11px] font-semibold" x-show="!modal.data.personalid">
                                        <i class="fa-solid fa-circle-minus text-[10px]"></i> ถูกบล็อก (เฉพาะแอดมิน)
                                    </span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-450 block mb-0.5">วันเกิด / อายุ / วันบรรจุ</span>
                                    <span class="font-semibold text-slate-800" x-text="`${formatThaiDate(modal.data.birth_date, modal.data.birth_year_be)} (อายุ ${modal.data.age} ปี)`" x-show="modal.data.birth_date"></span>
                                    <span class="inline-flex items-center gap-1 text-slate-400 italic text-[11px] font-semibold" x-show="!modal.data.birth_date">
                                        <i class="fa-solid fa-circle-minus text-[10px]"></i> ถูกบล็อก (เฉพาะแอดมิน)
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- School Contact Card -->
                        <template x-if="modal.data.school">
                            <div class="border border-slate-100 p-4 rounded-2xl space-y-2 bg-gradient-to-br from-slate-50 to-slate-100/50">
                                <span class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-school-flag text-emerald-500"></i> ข้อมูลการติดต่อโรงเรียน
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div><span class="font-bold text-slate-400">ที่ตั้งสังกัด:</span> <span x-text="`ต.${modal.data.school.tambon} อ.${modal.data.school.amper}`"></span></div>
                                    <div><span class="font-bold text-slate-400">เบอร์โทรศัพท์:</span> <span x-text="modal.data.school.tel || '-'"></span></div>
                                    <div><span class="font-bold text-slate-400">อีเมลโรงเรียน:</span> <span x-text="modal.data.school.email || '-'"></span></div>
                                    <div><span class="font-bold text-slate-400">เว็บไซต์:</span> <span x-text="modal.data.school.website || '-'"></span></div>
                                </div>
                                <template x-if="modal.data.school.maplink">
                                    <div class="pt-2">
                                        <a :href="modal.data.school.maplink" target="_blank" 
                                           class="inline-flex items-center gap-1 bg-white border border-slate-200 px-3 py-1.5 rounded-xl text-[10px] font-bold text-emerald-600 hover:bg-slate-50 shadow-sm transition">
                                            <i class="fa-solid fa-map-location-dot"></i> ดูแผนที่ตั้งบน Google Maps
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- TAB 2: TEACHING & ALIGNMENT -->
                    <div x-show="modal.activeTab === 'teaching'" class="space-y-4" x-cloak>
                        <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-100 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-square-poll-vertical text-lg"></i>
                            </div>
                            <div class="text-xs">
                                <span class="block font-extrabold text-emerald-800" x-text="`ระดับความเข้ากันได้กับวิชาเอก: ${modal.data.alignment.label}`"></span>
                                <p class="text-emerald-700 mt-1" x-text="modal.data.alignment.desc"></p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <span class="block text-xs font-bold text-slate-400">รายวิชาที่รับผิดชอบการสอน</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="sub in modal.data.subjects">
                                    <div class="flex items-center justify-between text-xs p-3.5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-emerald-300 transition duration-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                                                <i class="fa-solid fa-book"></i>
                                            </div>
                                            <span class="font-bold text-slate-700" x-text="sub.subject_name"></span>
                                        </div>
                                        <span class="text-slate-500 font-extrabold" x-text="`ชั้น ${sub.subject_grade} (${sub.subject_hours} ชม./สัปดาห์)`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-4">
                            <span class="block text-xs font-bold text-slate-400 mb-2">ประวัติวุฒิการศึกษาตัวเต็ม</span>
                            <div class="overflow-x-auto border border-slate-100 rounded-2xl" x-show="modal.data.educations && modal.data.educations.length > 0" x-cloak>
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-slate-500 font-bold">
                                        <tr>
                                            <th class="p-3">ระดับ</th>
                                            <th class="p-3">สาขาวิชา</th>
                                            <th class="p-3">วิชาเอก</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="edu in modal.data.educations">
                                            <tr>
                                                <td class="p-3 font-semibold text-slate-700" x-text="edu.edu_level"></td>
                                                <td class="p-3 text-slate-600" x-text="edu.field_of_study"></td>
                                                <td class="p-3 text-slate-600" x-text="edu.major"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl text-center border border-dashed border-slate-200 text-slate-400 text-xs font-bold flex items-center justify-center gap-2" 
                                 x-show="!modal.data.educations">
                                <i class="fa-solid fa-user-lock text-sm text-slate-450"></i>
                                <span>ข้อมูลประวัติการศึกษาแบบละเอียดถูกจำกัดสิทธิ์การเข้าชม</span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: LANGUAGE SKILLS -->
                    <div x-show="modal.activeTab === 'language'" class="space-y-4" x-cloak>
                        <span class="block text-xs font-bold text-slate-400">ผลทดสอบสมรรถนะทักษะทางภาษา</span>
                        
                        <div class="p-4 rounded-2xl border border-slate-100/55 bg-gradient-to-br from-indigo-50/50 to-indigo-100/20 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center text-lg font-extrabold shrink-0 shadow-md shadow-indigo-100" 
                                     x-text="modal.data.cefr ? modal.data.cefr.cefr_level : 'N/A'"></div>
                                <div>
                                    <span class="block font-bold text-xs text-slate-800">ผลการอบรม/การทดสอบ CEFR</span>
                                    <p class="text-[10px] text-slate-500 mt-0.5" x-text="`หน่วยงานออกใบรับรอง: ${modal.data.cefr ? (modal.data.cefr.issuer || 'ไม่ระบุ') : 'ไม่มีข้อมูล'}`"></p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right text-[11px] text-slate-600" x-show="modal.data.cefr">
                                <p><span class="font-bold text-slate-400">เลขที่ใบรับรอง:</span> <span class="font-mono font-semibold" x-text="modal.data.cefr ? modal.data.cefr.cert_no : '-'"></span></p>
                                <p class="mt-0.5"><span class="font-bold text-slate-400">วันที่ทดสอบ:</span> <span class="font-semibold" x-text="modal.data.cefr ? formatThaiDate(modal.data.cefr.cert_date, modal.data.cefr.cert_date_be) : '-'"></span></p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-100/55 bg-gradient-to-br from-rose-50/50 to-rose-100/20 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-xs font-extrabold shrink-0 shadow-md shadow-rose-100 text-center px-1" 
                                     x-text="modal.data.hsk ? 'HSK' : 'N/A'"></div>
                                <div>
                                    <span class="block font-bold text-xs text-slate-800" x-text="modal.data.hsk ? `ระดับภาษาจีน HSK: ${modal.data.hsk.hsk_level}` : 'ไม่มีข้อมูลการประเมินภาษาจีน HSK 3.0'"></span>
                                    <p class="text-[10px] text-slate-500 mt-0.5" x-text="`หน่วยงานออกใบรับรอง: ${modal.data.hsk ? (modal.data.hsk.issuer || 'ไม่ระบุ') : 'ไม่มีข้อมูล'}`"></p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right text-[11px] text-slate-600" x-show="modal.data.hsk">
                                <p><span class="font-bold text-slate-400">เลขที่ใบรับรอง:</span> <span class="font-mono font-semibold" x-text="modal.data.hsk ? modal.data.hsk.cert_no : '-'"></span></p>
                                <p class="mt-0.5"><span class="font-bold text-slate-400">วันที่ทดสอบ:</span> <span class="font-semibold" x-text="modal.data.hsk ? formatThaiDate(modal.data.hsk.cert_date, modal.data.hsk.cert_date_be) : '-'"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: AWARDS & ACHIEVEMENTS -->
                    <div x-show="modal.activeTab === 'awards'" class="space-y-4" x-cloak>
                        <span class="block text-xs font-bold text-slate-400">รายการรางวัลและเกียรติยศที่ภาคภูมิใจ</span>
                        <div class="space-y-3">
                            <template x-for="award in modal.data.awards">
                                <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60 flex items-start gap-3.5 hover:border-amber-400/50 transition duration-200">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg shrink-0">
                                        <i class="fa-solid fa-trophy"></i>
                                    </div>
                                    <div class="text-xs space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-slate-800" x-text="award.award_name"></span>
                                            <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-2 py-0.5 rounded-full" 
                                                  x-text="award.award_date_be ? `พ.ศ. ${award.award_date_be}` : formatThaiDate(award.award_date)"></span>
                                        </div>
                                        <p class="text-slate-550 font-medium" x-text="`ชื่อผลงานนวัตกรรม: ${award.work_name || 'ไม่ระบุ'}`"></p>
                                        <p class="text-[10px] text-slate-400 font-bold" x-text="`ผู้ออกรางวัล: ${award.issuer || 'ไม่ระบุ'}`"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!modal.data.awards || modal.data.awards.length === 0">
                                <div class="p-8 text-center text-slate-400 text-xs font-bold border border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                                    ไม่มีข้อมูลประวัติรางวัลเชิดชูเกียรติ
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- TAB 5: LMS PROGRESS -->
                    <div x-show="modal.activeTab === 'lms'" class="space-y-4" x-cloak>
                        <span class="block text-xs font-bold text-slate-400">ความคืบหน้าการศึกษาอบรมพัฒนาในระบบ LMS</span>
                        <div class="space-y-4">
                            <template x-for="course in modal.data.lms_courses">
                                <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-2.5">
                                    <div class="flex items-center justify-between gap-4 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-[10.5px] shrink-0">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                            </div>
                                            <span class="font-extrabold text-slate-700 truncate" x-text="course.title"></span>
                                        </div>
                                        <span class="text-indigo-600 font-extrabold shrink-0" x-text="`${course.progress}%`"></span>
                                    </div>
                                    <div class="h-2 w-full bg-slate-200/50 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" :style="`width: ${course.progress}%`"></div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!modal.data.lms_courses || modal.data.lms_courses.length === 0">
                                <div class="p-8 text-center text-slate-400 text-xs font-bold border border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                                    ไม่พบประวัติการลงทะเบียนวิชาเรียนในระบบ LMS
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-between shrink-0">
                    <span class="text-[10px] text-slate-400 font-bold">ระบบตรวจสอบและคุ้มครองความปลอดภัยข้อมูลส่วนบุคคล</span>
                    <button type="button" @click="modal.open = false" 
                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                        ปิดหน้าต่าง
                    </button>
                </footer>
            </div>
        </div>

    </div>

    <!-- Script Section (Axios, Alpine.js with Safe load listener rule) -->
    @push('scripts')
    <script>
        function dashboardManager() {
            return {
                loadingStats: true,
                loadingDrilldown: false,
                stats: {
                    totalRecords: 0,
                    todayRecords: 0,
                    thisMonthRecords: 0,
                    avgAge: 0,
                    positionSummary: [],
                    schoolSummary: [],
                    networkSummary: [],
                    educationLevelSummary: [],
                    genderSummary: [],
                    cefrSummary: [],
                    hskSummary: [],
                    subjectTop: []
                },
                drilldownData: {
                    active: false,
                    title: '',
                    records: []
                },
                modal: {
                    open: false,
                    activeTab: 'general',
                    data: {
                        alignment: {}
                    }
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                init() {
                    this.fetchStats();
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3500);
                },

                fetchStats() {
                    this.loadingStats = true;
                    axios.get('{{ route("api.dashboard.stats") }}')
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.stats = response.data.data;
                        } else {
                            this.showToast('ดึงข้อมูลสถิติไม่สำเร็จ', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Stats error:', error);
                        this.showToast('เชื่อมต่อเซิร์ฟเวอร์หลักไม่สำเร็จ', 'error');
                    })
                    .finally(() => {
                        this.loadingStats = false;
                    });
                },

                drilldown(type, value) {
                    this.loadingDrilldown = true;
                    this.drilldownData.active = true;
                    this.drilldownData.records = [];
                    
                    let typeLabel = '';
                    if (type === 'school') typeLabel = 'โรงเรียน ' + value;
                    else if (type === 'network') typeLabel = 'เครือข่าย ' + value;
                    else if (type === 'position') typeLabel = 'ตำแหน่ง ' + value;
                    else if (type === 'cefr') typeLabel = 'CEFR ระดับ ' + value;
                    else if (type === 'hsk') typeLabel = 'HSK ' + value;
                    else if (type === 'education') typeLabel = 'วุฒิสูงสุด ' + value;

                    this.drilldownData.title = typeLabel;

                    // Scroll to target drilldown section smoothly
                    setTimeout(() => {
                        const el = document.getElementById('drilldown-section');
                        if (el) el.scrollIntoView({ behavior: 'smooth' });
                    }, 100);

                    axios.get('{{ route("api.dashboard.drilldown") }}', {
                        params: { type, value }
                    })
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.drilldownData.records = response.data.data;
                        } else {
                            this.showToast('ดึงข้อมูลรายละเอียดไม่สำเร็จ', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Drilldown error:', error);
                        this.showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    })
                    .finally(() => {
                        this.loadingDrilldown = false;
                    });
                },

                closeDrilldown() {
                    this.drilldownData.active = false;
                },

                openModal(record) {
                    this.modal.data = JSON.parse(JSON.stringify(record));
                    this.modal.activeTab = 'general';
                    this.modal.open = true;
                },

                pct(val, base) {
                    if (base <= 0) return '0%';
                    return ((val * 100) / base).toFixed(1) + '%';
                },

                filterLang(array, source) {
                    if (!array) return [];
                    return array.filter(item => item.source === source);
                },

                formatThaiDate(dateStr, yearBe = null) {
                    if (!dateStr) return '-';
                    const date = new Date(dateStr);
                    if (isNaN(date.getTime())) return '-';
                    
                    const d = date.getDate();
                    const m = date.getMonth();
                    const y = date.getFullYear();
                    
                    const months = [
                        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                    ];
                    
                    const thYear = yearBe ? yearBe : (y + 543);
                    return `${d} ${months[m]} ${thYear}`;
                }
            };
        }
    </script>
    @endpush
</x-layout>
