<x-layout>
    <x-slot:title>ข้อมูลบุคลากรทั้งหมด | BigData สพป.ชพ.1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6" x-data="reportManager()" x-init="init()">
        
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
        <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <a href="/" class="hover:text-orange-600 transition">หน้าหลัก</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-600">ข้อมูลบุคลากรทั้งหมด</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">ข้อมูลบุคลากรทั้งหมด</h2>
                <p class="text-slate-500 text-sm mt-1">
                    ระบบสืบค้นและทำเนียบข้อมูลการพัฒนาตนเอง ความสอดคล้องวิชาเอก และประวัติการฝึกอบรม
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                @auth
                <a href="{{ route('dashboard') }}" 
                   class="bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-550 text-white text-xs font-bold px-5 py-3.5 rounded-2xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-indigo-100/55 active:scale-95">
                    <i class="fa-solid fa-chart-column text-sm"></i> แดชบอร์ดสถิติ
                </a>
                @endauth

                @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('admin.reports.export') }}" 
                   class="bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-5 py-3.5 rounded-2xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-orange-100/55 active:scale-95">
                    <i class="fa-solid fa-file-excel text-sm"></i> ส่งออก Excel (แอดมิน)
                </a>
                @endif
            </div>
        </header>

        <!-- Search and Filter Toolbar (SPA-like) -->
        <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-sm mb-6 flex flex-wrap gap-4 items-center">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" x-model="filters.q_name" @input="debounceSearch()" placeholder="ค้นหา ชื่อ-นามสกุล..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-10 pr-4 py-3 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>
            
            <div class="relative w-56">
                <input type="text" x-model="filters.q_school" @input="debounceSearch()" placeholder="ค้นหาชื่อโรงเรียน..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-10 pr-4 py-3 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                <i class="fa-solid fa-school absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>

            <div class="relative w-48">
                <input type="text" x-model="filters.q_network" @input="debounceSearch()" placeholder="ค้นหาเครือข่าย..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-10 pr-4 py-3 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                <i class="fa-solid fa-network-wired absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>

            <button type="button" @click="resetFilters()" 
                    class="px-5 py-3 border border-slate-200 hover:bg-slate-50 active:scale-95 rounded-2xl text-xs font-bold transition cursor-pointer">
                รีเซ็ต
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="py-20 flex flex-col items-center justify-center gap-3" x-cloak>
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-orange-500"></i>
            <span class="text-xs font-extrabold text-slate-400">กำลังประมวลผลข้อมูล...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && records.length === 0" class="py-20 text-center border border-dashed border-slate-200 rounded-3xl bg-white" x-cloak>
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 text-lg">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h4 class="font-extrabold text-slate-700 text-sm">ไม่พบข้อมูลบุคลากร</h4>
            <p class="text-xs text-slate-400 mt-1">ลองเปลี่ยนคำค้นหาหรือตัวกรองใหม่อีกครั้ง</p>
        </div>

        <!-- Cards Grid -->
        <div x-show="!loading && records.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-cloak>
            <template x-for="record in records" :key="record.id">
                <div @click="openModal(record)" 
                     class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-orange-300 transition-all duration-300 cursor-pointer flex flex-col justify-between relative group">
                    
                    <div class="flex items-start gap-4">
                        <!-- Profile Image -->
                        <div class="w-20 h-20 rounded-full overflow-hidden border border-slate-100 shadow-sm shrink-0 bg-slate-100 flex items-center justify-center">
                            <template x-if="record.profile_image_url || record.profile_image_path">
                                <img :src="record.profile_image_url || record.profile_image_path" alt="รูปโปรไฟล์" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!(record.profile_image_url || record.profile_image_path)">
                                <i class="fa-solid fa-users text-slate-400 text-2xl"></i>
                            </template>
                        </div>

                        <!-- Main Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-800 text-sm truncate" x-text="`${record.prefix} ${record.first_name} ${record.last_name}`"></h3>

                            </div>
                            <p class="text-xs text-slate-500 mt-1 font-semibold" x-text="record.position || 'ไม่ระบุตำแหน่ง'"></p>
                            <p class="text-[11px] text-orange-600 font-extrabold mt-0.5" x-text="`วิทยฐานะ: ${record.academic_rank || 'ไม่มีวิทยฐานะ'}`"></p>
                            
                            <div class="mt-2 text-[11px] text-slate-600 space-y-1">
                                <p><span class="font-bold text-slate-400">โรงเรียน:</span> <span x-text="record.school_name"></span></p>
                                <p><span class="font-bold text-slate-400">กลุ่มเครือข่าย:</span> <span x-text="record.school_network"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Alignment & Language Stats Overview -->
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100/50 flex flex-col justify-center">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">ความสอดคล้องวิชาเอก:</span>
                            <span class="mt-1 text-[11px] font-extrabold"
                                  :class="{
                                      'text-orange-600': record.alignment.status === 'good',
                                      'text-amber-600': record.alignment.status === 'partial',
                                      'text-rose-600': record.alignment.status === 'low',
                                      'text-slate-500': record.alignment.status === 'insufficient'
                                  }" x-text="record.alignment.label"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100/50 flex flex-col justify-center">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">ผลประเมิน CEFR:</span>
                            <span class="mt-1 text-[11px] font-extrabold text-slate-700" 
                                  x-text="record.cefr ? `ระดับ ${record.cefr.cefr_level}` : 'ไม่มีข้อมูล'"></span>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-5 border-t border-slate-50 pt-4 flex items-center justify-between">
                        <span class="text-[9px] text-slate-400 font-bold" x-text="`รหัสข้อมูล: SURV-${record.id}`"></span>
                        
                        @if(auth()->user() && auth()->user()->role === 'admin')
                        <button type="button" @click.stop="confirmDelete(record.id)" 
                                :disabled="deleting === record.id"
                                class="text-rose-500 hover:text-white hover:bg-rose-500 border border-rose-100 hover:border-rose-500 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-1.5 active:scale-95 disabled:opacity-50">
                            <template x-if="deleting === record.id">
                                <i class="fa-solid fa-circle-notch fa-spin text-[10px]"></i>
                            </template>
                            <template x-if="deleting !== record.id">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                            </template>
                            <span x-text="deleting === record.id ? 'กำลังลบ...' : 'ลบข้อมูล'"></span>
                        </button>
                        @endif
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination Controls -->
        <div x-show="!loading && pagination.last_page > 1" class="mt-10 flex flex-wrap justify-center items-center gap-1.5" x-cloak>

            <!-- First + Prev -->
            <button type="button" @click="fetchData(1)"
                    :disabled="pagination.current_page === 1"
                    title="หน้าแรก"
                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-500 text-xs font-bold flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition active:scale-95">
                <i class="fa-solid fa-angles-left text-[10px]"></i>
            </button>
            <button type="button" @click="fetchData(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    title="หน้าก่อนหน้า"
                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-500 text-xs font-bold flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition active:scale-95">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>

            <!-- Sliding page window with ellipsis -->
            <template x-for="(item, idx) in pagesArray()" :key="idx">
                <span x-show="item === null"
                      class="h-9 w-9 flex items-center justify-center text-slate-400 text-xs font-extrabold select-none pointer-events-none">...</span>
                <button x-show="item !== null"
                        type="button" @click="fetchData(item)"
                        class="h-9 min-w-[36px] px-2.5 rounded-xl text-xs font-extrabold transition active:scale-95"
                        :class="pagination.current_page === item
                            ? 'bg-orange-600 text-white shadow-md shadow-orange-100 ring-2 ring-orange-300'
                            : 'border border-slate-100 bg-white text-slate-600 hover:bg-orange-50 hover:border-orange-300 hover:text-orange-700'"
                        x-text="item">
                </button>
            </template>

            <!-- Next + Last -->
            <button type="button" @click="fetchData(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    title="หน้าถัดไป"
                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-500 text-xs font-bold flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition active:scale-95">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
            <button type="button" @click="fetchData(pagination.last_page)"
                    :disabled="pagination.current_page === pagination.last_page"
                    title="หน้าสุดท้าย"
                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-500 text-xs font-bold flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition active:scale-95">
                <i class="fa-solid fa-angles-right text-[10px]"></i>
            </button>

            <!-- Page info -->
            <span class="w-full text-center text-[11px] text-slate-400 font-bold mt-1"
                  x-text="`หน้า ${pagination.current_page} จาก ${pagination.last_page} หน้า`"></span>
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

                <!-- TAB HEADERS (No Scrollbar Style) -->
                <div class="flex border-b border-slate-100 bg-slate-50/50 shrink-0 overflow-x-auto no-scrollbar">
                    <button type="button" @click="modal.activeTab = 'general'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'general' ? 'border-orange-500 text-orange-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-user mr-1.5 text-[11px]"></i>ข้อมูลทั่วไป
                    </button>
                    <button type="button" @click="modal.activeTab = 'teaching'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'teaching' ? 'border-orange-500 text-orange-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-chalkboard-user mr-1.5 text-[11px]"></i>งานสอน & วิชาเอก
                    </button>
                    <button type="button" @click="modal.activeTab = 'language'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'language' ? 'border-orange-500 text-orange-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-language mr-1.5 text-[11px]"></i>ทักษะภาษา (CEFR/HSK)
                    </button>
                    <button type="button" @click="modal.activeTab = 'awards'" 
                            class="px-5 py-3.5 text-xs font-bold transition-all border-b-2 outline-none shrink-0"
                            :class="modal.activeTab === 'awards' ? 'border-orange-500 text-orange-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-trophy mr-1.5 text-[11px]"></i>รางวัล & ผลงาน
                    </button>

                </div>

                <!-- TAB CONTENTS (Fixed Height, No Page Scrollbar) -->
                <div class="p-6 h-[520px] overflow-y-auto bg-white">
                    
                    <!-- TAB 1: GENERAL INFO -->
                    <div x-show="modal.activeTab === 'general'" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                            <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-slate-100 shadow-md mx-auto md:col-span-4 bg-slate-50 flex items-center justify-center shrink-0">
                                <template x-if="modal.data.profile_image_url || modal.data.profile_image_path">
                                    <img :src="modal.data.profile_image_url || modal.data.profile_image_path" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!(modal.data.profile_image_url || modal.data.profile_image_path)">
                                    <i class="fa-solid fa-users text-slate-350 text-6xl"></i>
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
                        
                        <!-- School Contact Card (7.2 Recommendation) -->
                        <template x-if="modal.data.school">
                            <div class="border border-slate-100 p-4 rounded-2xl space-y-2 bg-gradient-to-br from-slate-50 to-slate-100/50">
                                <span class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-school-flag text-orange-500"></i> ข้อมูลการติดต่อโรงเรียน
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
                                           class="inline-flex items-center gap-1 bg-white border border-slate-200 px-3 py-1.5 rounded-xl text-[10px] font-bold text-orange-600 hover:bg-slate-50 shadow-sm transition">
                                            <i class="fa-solid fa-map-location-dot"></i> ดูแผนที่ตั้งบน Google Maps
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- TAB 2: TEACHING & ALIGNMENT -->
                    <div x-show="modal.activeTab === 'teaching'" class="space-y-4" x-cloak>
                        <!-- Alignment block -->
                        <div class="p-4 rounded-xl bg-orange-50/60 border border-orange-100 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/10 text-orange-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-square-poll-vertical text-lg"></i>
                            </div>
                            <div class="text-xs">
                                <span class="block font-extrabold text-orange-800" x-text="`ระดับความเข้ากันได้กับวิชาเอก: ${modal.data.alignment.label}`"></span>
                                <p class="text-orange-700 mt-1" x-text="modal.data.alignment.desc"></p>
                            </div>
                        </div>

                        <!-- Subjects Taught List -->
                        <div class="space-y-2">
                            <span class="block text-xs font-bold text-slate-400">รายวิชาที่รับผิดชอบการสอน</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="sub in modal.data.subjects">
                                    <div class="flex items-center justify-between text-xs p-3.5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-orange-300 transition duration-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center text-[10px]">
                                                <i class="fa-solid fa-book"></i>
                                            </div>
                                            <span class="font-bold text-slate-700" x-text="sub.subject_name"></span>
                                        </div>
                                        <span class="text-slate-500 font-extrabold" x-text="`ชั้น ${sub.subject_grade} (${sub.subject_hours} ชม./สัปดาห์)`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Education List (Visually hidden for non-admin) -->
                        <div class="border-t border-slate-100 pt-4">
                            <span class="block text-xs font-bold text-slate-400 mb-2">ประวัติวุฒิการศึกษาตัวเต็ม</span>
                            <!-- Admin View -->
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
                            <!-- User View -->
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
                        
                        <!-- CEFR Score card -->
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

                        <!-- HSK Score card -->
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



                </div>

                <!-- Fixed Footer -->
                <footer class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-between shrink-0">
                    <span class="text-[10px] text-slate-400 font-bold">ระบบตรวจสอบและคุ้มครองความปลอดภัยข้อมูลส่วนบุคคล</span>
                    <button type="button" @click="modal.open = false" 
                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        ปิดหน้าต่าง
                    </button>
                </footer>
            </div>
        </div>

    </div>

    <!-- Script Section (Axios, Alpine.js with Safe load listener rule) -->
    @push('scripts')
    <script>
        window.addEventListener('load', function() {
            // Global Alpine initialization container to prevent race condition
        });

        function reportManager() {
            return {
                loading: false,
                deleting: null,
                records: [],
                pagination: {
                    current_page: 1,
                    last_page: 1
                },
                filters: {
                    q_name: '',
                    q_school: '',
                    q_network: ''
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
                searchTimeout: null,

                init() {
                    this.fetchData(1);
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3500);
                },

                debounceSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.fetchData(1);
                    }, 500); // 500ms debounce
                },

                resetFilters() {
                    this.filters = { q_name: '', q_school: '', q_network: '' };
                    this.fetchData(1);
                },

                fetchData(page = 1) {
                    this.loading = true;
                    axios.get('{{ route("api.reports.data") }}', {
                        params: {
                            page: page,
                            q_name: this.filters.q_name,
                            q_school: this.filters.q_school,
                            q_network: this.filters.q_network
                        }
                    })
                    .then(response => {
                        if (response.data.status === 'success') {
                            const paginator = response.data.data;
                            this.records = paginator.data;
                            this.pagination = {
                                current_page: paginator.current_page,
                                last_page: paginator.last_page
                            };
                        } else {
                            this.showToast('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        this.showToast('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ', 'error');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                },

                openModal(record) {
                    // Deep copy to prevent modifying client state
                    this.modal.data = JSON.parse(JSON.stringify(record));
                    this.modal.activeTab = 'general';
                    this.modal.open = true;
                },

                confirmDelete(id) {
                    if (confirm('คุณต้องการลบข้อมูลบุคลากรท่านนี้ใช่หรือไม่? \n(การกระทำนี้จะลบประวัติการศึกษา วิชาสอน และรางวัลทั้งหมดด้วยและไม่สามารถกู้คืนได้)')) {
                        this.deleting = id;
                        
                        axios.delete(`{{ url('/admin/reports') }}/${id}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchData(this.pagination.current_page);
                            } else {
                                this.showToast(response.data.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            this.showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                        })
                        .finally(() => {
                            this.deleting = null;
                        });
                    }
                },

                pagesArray() {
                    const current = this.pagination.current_page;
                    const last    = this.pagination.last_page;
                    const delta   = 2; // pages shown on each side of current
                    const range   = [];
                    const result  = [];

                    // Build window of pages around current
                    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                        range.push(i);
                    }

                    // Always include page 1
                    result.push(1);

                    // Left ellipsis if gap between 1 and first range page
                    if (range.length > 0 && range[0] > 2) result.push(null);

                    // Middle window pages
                    range.forEach(p => result.push(p));

                    // Right ellipsis if gap between last range page and last page
                    if (range.length > 0 && range[range.length - 1] < last - 1) result.push(null);

                    // Always include last page (if more than 1)
                    if (last > 1) result.push(last);

                    return result;
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
