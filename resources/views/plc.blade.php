<x-layout>
    <x-slot:title>กระบวนการ PLC 6 ขั้นตอน | EE CPN1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6 min-h-[75vh]" x-data="plcManager()" x-init="init()">
        <!-- Toast Notification (Floating Glassmorphic) -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl backdrop-blur-md border border-white/20"
             :class="toast.type === 'success' ? 'bg-emerald-500/95 text-white' : 'bg-rose-500/95 text-white'"
             x-cloak>
            <template x-if="toast.type === 'success'">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </template>
            <template x-if="toast.type === 'error'">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </template>
            <span class="text-xs font-bold" x-text="toast.message"></span>
        </div>

        <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-emerald-500"></i>
                    กระบวนการชุมชนแห่งการเรียนรู้ทางวิชาชีพ (PLC)
                </h2>
                <p class="text-slate-500 text-sm mt-1">กระบวนการขับเคลื่อนชุมชนการเรียนรู้ PLC 6 ขั้นตอน เพื่อพัฒนาการเรียนการสอนและแก้ปัญหาในชั้นเรียน</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('dashboard') }}" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> สร้างกลุ่ม PLC
                </button>
            </div>
        </header>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังดาวน์โหลดข้อมูลกระบวนการ PLC...</p>
        </div>

        <!-- Main Layout Single Column View -->
        <div x-show="!loading" class="max-w-7xl mx-auto min-h-[65vh]" x-cloak x-transition>
                <template x-if="!currentGroup">
                    <div class="space-y-6">
                        
                        <!-- Level 1: Networks Grid -->
                        <div x-show="currentLevel === 'networks'" class="space-y-6">
                            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm text-left">
                                <h3 class="font-extrabold text-slate-800 text-sm mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-network-wired text-emerald-500"></i>
                                    เลือกเครือข่ายสถานศึกษา
                                </h3>
                                <p class="text-slate-400 text-xs">เลือกเครือข่ายสถานศึกษาเพื่อดูโรงเรียนและกลุ่ม PLC ที่อยู่ภายใต้สังกัด</p>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <template x-for="net in networksWithGroups" :key="net.name">
                                    <div @click="selectNetwork(net.name)"
                                         class="bg-white border border-slate-100 hover:border-emerald-500 rounded-2xl p-6 shadow-sm hover:shadow-lg transition cursor-pointer text-left relative group overflow-hidden">
                                        <div class="absolute -right-4 -bottom-4 text-slate-50 group-hover:text-emerald-50/50 transition-colors text-7xl font-extrabold select-none pointer-events-none">
                                            <i class="fa-solid fa-school-flag"></i>
                                        </div>
                                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                                            <i class="fa-solid fa-school"></i>
                                        </div>
                                        <h4 class="font-extrabold text-slate-800 text-xs mb-1 group-hover:text-emerald-600 transition" x-text="net.name"></h4>
                                        <p class="text-slate-450 text-[10px] font-bold" x-text="net.count + ' กลุ่ม PLC'"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Level 2: Schools Grid -->
                        <div x-show="currentLevel === 'schools'" class="space-y-6">
                            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm text-left flex items-center justify-between">
                                <div>
                                    <h3 class="font-extrabold text-slate-800 text-sm mb-1 flex items-center gap-2">
                                        <i class="fa-solid fa-school text-emerald-500"></i>
                                        <span x-text="'เครือข่าย: ' + selectedNetwork"></span>
                                    </h3>
                                    <p class="text-slate-400 text-xs">เลือกโรงเรียนเพื่อดูกลุ่ม PLC ภายในโรงเรียนนั้น</p>
                                </div>
                                <button type="button" @click="goBackToNetworks()" class="bg-slate-100 hover:bg-slate-200 text-slate-650 px-4 py-2 rounded-xl font-bold text-xs transition flex items-center gap-1.5 cursor-pointer border border-slate-200">
                                    <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <template x-for="sch in schoolsInSelectedNetwork" :key="sch.name">
                                    <div @click="selectSchool(sch.name)"
                                         class="bg-white border border-slate-100 hover:border-emerald-500 rounded-2xl p-6 shadow-sm hover:shadow-lg transition cursor-pointer text-left relative group overflow-hidden">
                                        <div class="absolute -right-4 -bottom-4 text-slate-50 group-hover:text-emerald-50/50 transition-colors text-7xl font-extrabold select-none pointer-events-none">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                        </div>
                                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                                            <i class="fa-solid fa-chalkboard-user"></i>
                                        </div>
                                        <h4 class="font-extrabold text-slate-800 text-xs mb-1 group-hover:text-emerald-600 transition" x-text="sch.name"></h4>
                                        <p class="text-slate-450 text-[10px] font-bold" x-text="sch.count + ' กลุ่ม PLC'"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Level 3: Groups Grid -->
                        <div x-show="currentLevel === 'groups'" class="space-y-6">
                            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm text-left flex items-center justify-between">
                                <div>
                                    <h3 class="font-extrabold text-slate-800 text-sm mb-1 flex items-center gap-2">
                                        <i class="fa-solid fa-users text-emerald-500"></i>
                                        <span x-text="selectedSchool"></span>
                                    </h3>
                                    <p class="text-slate-400 text-xs" x-text="'ภายใต้เครือข่าย: ' + selectedNetwork"></p>
                                </div>
                                <button type="button" @click="goBackToSchools()" class="bg-slate-100 hover:bg-slate-200 text-slate-650 px-4 py-2 rounded-xl font-bold text-xs transition flex items-center gap-1.5 cursor-pointer border border-slate-200">
                                    <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <template x-for="group in groupsInSelectedSchool" :key="group.id">
                                    <div @click="selectGroup(group)"
                                         class="bg-white border border-slate-100 hover:border-emerald-500 rounded-2xl p-6 shadow-sm hover:shadow-lg transition cursor-pointer text-left relative group">
                                        
                                        <!-- Top: Profile Picture & Name -->
                                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-100">
                                            <img :src="getCreatorAvatarUrl(group)" alt="Creator profile" class="w-12 h-12 rounded-full object-cover border-2 border-slate-100 shadow-sm shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <span class="inline-block text-[8px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full uppercase tracking-wider mb-1">ครูต้นแบบ</span>
                                                <h5 class="text-xs font-extrabold text-slate-800 leading-tight" x-text="group.creator ? group.creator.name : '-'"></h5>
                                            </div>
                                        </div>

                                        <!-- Middle: PLC Topic & Details -->
                                        <div class="space-y-1.5 mb-4 text-left">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-extrabold text-slate-900 text-xs group-hover:text-emerald-600 transition line-clamp-2" x-text="group.name"></h4>
                                            </div>
                                            <div class="flex items-center justify-between gap-2 pt-1 text-[9px] font-bold text-slate-400">
                                                <span class="text-slate-500 truncate max-w-[65%]" x-text="group.department"></span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-550 font-bold rounded text-[8px] shrink-0" x-text="'ภาคเรียนที่ ' + group.semester + '/' + group.academic_year"></span>
                                            </div>
                                        </div>

                                        <!-- Progress indicators of 6 steps -->
                                        <div class="space-y-1.5 border-t border-slate-100 pt-3">
                                            <div class="flex justify-between text-[8px] text-slate-400 font-bold">
                                                <span>ความก้าวหน้า 6 ขั้นตอน</span>
                                                <span x-text="group.steps.filter(s => s.status === 2).length + '/6 ขั้นตอนผ่าน' "></span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <template x-for="step in group.steps">
                                                    <div class="h-1.5 flex-1 rounded-full" 
                                                         :class="step.status === 0 ? 'bg-slate-150' : 
                                                                 (step.status === 1 ? 'bg-amber-400' : 
                                                                 (step.status === 2 ? 'bg-orange-500' : 'bg-rose-500'))"
                                                         :title="step.step_name">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>
                                <template x-if="currentGroup">
                    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                        
                        <!-- Group Header Details -->
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded text-[8px]" x-text="'ภาคเรียนที่ ' + currentGroup.semester + '/' + currentGroup.academic_year"></span>
                                        <span class="text-xs font-bold text-slate-450" x-text="'กลุ่มสาระ/ฝ่าย: ' + currentGroup.department"></span>
                                    </div>
                                    <h3 class="text-xl font-extrabold text-slate-900" x-text="currentGroup.name"></h3>
                                    <p class="text-slate-500 text-xs mt-1" x-text="currentGroup.description || 'ไม่มีคำอธิบายกลุ่มเพิ่มเติม'"></p>
                                </div>
                                <div class="shrink-0 text-left md:text-right flex flex-col items-start md:items-end gap-2">
                                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-85 transition bg-slate-50 hover:bg-slate-100 p-1.5 rounded-2xl border border-slate-100" @click="viewTeacherDetail(currentGroup.creator_user_id)">
                                        <img :src="getCreatorAvatarUrl(currentGroup)" alt="Creator profile" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm">
                                        <div class="text-left">
                                            <span class="block text-[8px] font-extrabold text-slate-450 uppercase">ครูต้นแบบ (ผู้สร้าง)</span>
                                            <span class="block text-xs font-extrabold text-slate-800" x-text="currentGroup.creator ? currentGroup.creator.name : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                                        <!-- Back Button to Groups List -->
                                        <button type="button" 
                                                @click="currentGroup = null" 
                                                class="bg-slate-100 hover:bg-slate-200 text-slate-650 px-3 py-1.5 rounded-xl font-bold text-[10px] transition flex items-center gap-1.5 cursor-pointer border border-slate-200 shadow-sm">
                                            <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
                                        </button>
                                        <!-- Edit button: visible for creator or admin -->
                                        <button type="button" 
                                                x-show="currentUser.role === 'admin' || currentGroup.creator_user_id == currentUser.id"
                                                @click="openEditGroupModal(currentGroup)" 
                                                class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-xl font-bold text-[10px] transition flex items-center gap-1 cursor-pointer border border-amber-200 shadow-sm">
                                            <i class="fa-solid fa-pen-to-square text-amber-600"></i> แก้ไขข้อมูลกลุ่ม
                                        </button>
                                        
                                        <!-- Delete button: visible for creator or admin -->
                                        <button type="button" 
                                                x-show="currentUser.role === 'admin' || currentGroup.creator_user_id == currentUser.id"
                                                @click="confirmDeleteGroup(currentGroup.id)" 
                                                class="bg-rose-50 hover:bg-rose-100 text-rose-750 px-3 py-1.5 rounded-xl font-bold text-[10px] transition flex items-center gap-1.5 cursor-pointer border border-rose-200 shadow-sm">
                                            <i class="fa-solid fa-trash-can text-rose-500"></i> ลบกลุ่ม PLC
                                        </button>

                                        <button type="button" @click="resetDashboard()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-bold text-[10px] transition flex items-center gap-1.5 cursor-pointer border border-slate-200 shadow-sm">
                                            <i class="fa-solid fa-rectangle-xmark text-rose-500"></i> ปิดกลุ่ม / กลับหน้าหลัก
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- List Group Members -->
                            <div class="mt-4 pt-3 border-t border-slate-100/60 flex flex-wrap gap-2 items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">สมาชิกกลุ่ม:</span>
                                <template x-for="m in currentGroup.members">
                                    <span class="pl-1.5 pr-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-100 hover:border-emerald-200 rounded-lg text-[9px] font-bold text-slate-650 flex items-center gap-1.5 shadow-sm cursor-pointer transition active:scale-95" @click="viewTeacherDetail(m.user_id)">
                                        <img :src="getMemberAvatarUrl(m)" alt="Profile" class="w-5 h-5 rounded-full object-cover border border-slate-100 shadow-sm">
                                        <span x-text="m.user ? m.user.name : 'Unknown'"></span>
                                        <span class="text-slate-400" x-text="'(' + m.role + ')'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- 6 Steps Progress Tabs (Chevron Timeline Style) -->
                        <div class="border-b border-slate-100 overflow-x-auto">
                            <div class="flex min-w-[700px] p-3 gap-2.5 bg-slate-50">
                                <template x-for="step in currentGroup.steps" :key="step.id">
                                    <button type="button" 
                                            @click="changeTab(step.sequence)" 
                                            class="flex-1 py-3.5 pl-4 pr-7 rounded-l-2xl font-bold flex items-center justify-center gap-2.5 transition-all duration-300 shadow-sm relative cursor-pointer select-none border-0"
                                            style="clip-path: polygon(0% 0%, calc(100% - 14px) 0%, 100% 50%, calc(100% - 14px) 100%, 0% 100%);"
                                            :class="activeTab === step.sequence 
                                                ? (step.status === 2 ? 'bg-orange-500 text-white shadow-md shadow-orange-100' : 
                                                  (step.status === 1 ? 'bg-amber-500 text-white shadow-md shadow-amber-100' : 
                                                  'bg-slate-800 text-white shadow-md shadow-slate-200')) 
                                                : (step.status === 2 ? 'bg-orange-50 text-orange-700 hover:bg-orange-100/60' : 
                                                  (step.status === 1 ? 'bg-amber-50 text-amber-700 hover:bg-amber-100/60' : 
                                                  'bg-slate-200/50 text-slate-500 hover:bg-slate-200'))"
                                    >
                                        <!-- Step status badge circle -->
                                        <div class="w-5.5 h-5.5 rounded-full flex items-center justify-center text-[10px] font-extrabold shrink-0 shadow-sm"
                                             :class="activeTab === step.sequence
                                                 ? (step.status === 2 ? 'bg-white text-orange-600' : 
                                                   (step.status === 1 ? 'bg-white text-amber-600' : 
                                                   'bg-white text-slate-800'))
                                                 : (step.status === 2 ? 'bg-orange-500 text-white' : 
                                                   (step.status === 1 ? 'bg-amber-500 text-white' : 
                                                   'bg-slate-300 text-slate-600'))"
                                        >
                                            <template x-if="step.status === 2">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                            </template>
                                        </div>
                                        
                                        <!-- Step Text Label (increased font size) -->
                                        <span class="text-xs md:text-sm font-extrabold uppercase tracking-wide" x-text="'Step ' + step.sequence"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Step Content Panels -->
                        <div class="p-6 md:p-8 space-y-6">
                            
                            <template x-for="step in currentGroup.steps" :key="step.id">
                                <div x-show="activeTab === step.sequence" class="space-y-6">
                                    
                                    <div class="border-b border-slate-100 pb-4 space-y-3">
                                        <div class="flex items-center justify-between gap-4">
                                            <h4 class="text-base font-extrabold text-slate-800" x-text="step.step_name"></h4>
                                            <span class="px-3 py-1 font-bold rounded-full text-[9px] border"
                                                  :class="step.status === 2 ? 'bg-orange-50 text-orange-600 border-orange-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
                                                  x-text="step.status === 2 ? 'เสร็จสิ้นขั้นตอน' : 'กำลังดำเนินการ'">
                                            </span>
                                        </div>
                                        <!-- Step Description Box -->
                                        <div class="bg-emerald-50/40 rounded-xl p-4 border border-emerald-100/60 text-left text-xs text-slate-600 space-y-1.5 shadow-sm">
                                            <div class="font-extrabold text-emerald-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-info"></i> คำอธิบายขั้นตอนการดำเนินงาน:
                                            </div>
                                            <div x-show="step.sequence === 1" class="font-medium">ร่วมกันวิเคราะห์ปัญหาการเรียนรู้ของผู้เรียน ค้นหาสาเหตุที่แท้จริง และกำหนดเป้าหมายเชิงตัวชี้วัด (KPI) พร้อมทั้งวางกรอบเวลาในการดำเนินงานทั้ง 6 ขั้นตอน</div>
                                            <div x-show="step.sequence === 2" class="font-medium">ร่วมกันออกแบบนวัตกรรม แผนการจัดการเรียนรู้ และสื่อการสอนที่ตอบโจทย์การแก้ปัญหาที่ระบุไว้ในขั้นตอนที่ 1 โดยระบุวัตถุประสงค์และรายละเอียดของนวัตกรรม</div>
                                            <div x-show="step.sequence === 3" class="font-medium">สมาชิกในกลุ่ม (ครูคู่หู/พี่เลี้ยง/ผู้เชี่ยวชาญ) ร่วมกันวิพากษ์แผนการสอน เสนอข้อแนะนำปรับปรุง บันทึกประวัติการปรับปรุง และยืนยันความพร้อมก่อนนำไปสอนจริง</div>
                                            <div x-show="step.sequence === 4" class="font-medium">ครูต้นแบบทำการเปิดห้องเรียนจริงตามวันเวลาและสถานที่ที่กำหนด โดยสมาชิกในกลุ่มร่วมสังเกตการณ์การเรียนรู้ พฤติกรรมการตอบสนองของนักเรียน และบันทึกผลการสังเกตการณ์</div>
                                            <div x-show="step.sequence === 5" class="font-medium">ร่วมกันประชุมสะท้อนผลการสอนของครูต้นแบบ (AAR) โดยครูต้นแบบประเมินผลการเรียนรู้ของนักเรียนตามตัวชี้วัด และสมาชิกในกลุ่มร่วมให้ข้อเสนอแนะสำหรับการปรับปรุงรอบถัดไป</div>
                                            <div x-show="step.sequence === 6" class="font-medium">สรุปผลงานนวัตกรรม บทคัดย่อความรู้ (Best Practice) ที่ได้รับจากกระบวนการ PLC ทั้งหมด แนบเอกสารคู่มือ/แผนการจัดกิจกรรมฉบับสมบูรณ์ และเลือกสิทธิ์เผยแพร่สู่สาธารณะเพื่อเป็นแหล่งเรียนรู้</div>
                                        </div>
                                    </div>

                                    <!-- STEP 1: Plan form -->
                                    <template x-if="step.sequence === 1">
                                        <div class="space-y-5">
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. ประเด็นปัญหาที่พบในชั้นเรียน (Problem Statement) *</label>
                                                <textarea x-model="step.step1_problem_statement" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="ระบุประเด็นปัญหาที่พบในการจัดเรียนการสอนของท่าน..."></textarea>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. สาเหตุและที่มาของปัญหา (Root Cause) *</label>
                                                <textarea x-model="step.step1_root_cause" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="อธิบายสาเหตุเชิงลึกของปัญหาดังกล่าว..."></textarea>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">3. เป้าหมายและตัวชี้วัดความสำเร็จ (Goal/KPI) *</label>
                                                <textarea x-model="step.step1_goal_kpi" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เป้าหมายเชิงปริมาณและคุณภาพที่ต้องการให้เกิด..."></textarea>
                                            </div>

                                            <div class="border-t border-slate-100 pt-4 mt-6">
                                                <h5 class="text-xs font-bold text-slate-800 mb-3 uppercase tracking-wider">📅 ปฏิทินวันนัดหมายประเมินแต่ละขั้นตอน (Timeline)</h5>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-slate-450">วันนัดหมาย Step 2</label>
                                                        <input type="date" x-model="step.step1_timeline_step2" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-slate-450">วันนัดหมาย Step 3</label>
                                                        <input type="date" x-model="step.step1_timeline_step3" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-slate-450">วันนัดหมาย Step 4</label>
                                                        <input type="date" x-model="step.step1_timeline_step4" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-slate-450">วันนัดหมาย Step 5</label>
                                                        <input type="date" x-model="step.step1_timeline_step5" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-slate-450">วันนัดหมาย Step 6</label>
                                                        <input type="date" x-model="step.step1_timeline_step6" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- STEP 2: Design form & Comment sharing -->
                                    <template x-if="step.sequence === 2">
                                        <div class="space-y-5">
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. ชื่อหน่วยการเรียนรู้ / แผนการจัดการเรียนรู้ *</label>
                                                <input type="text" x-model="step.step2_unit_name" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น แผนการจัดการเรียนรู้ที่ 5 เรื่อง การใช้พู่กันสีน้ำ">
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ระดับชั้น / วิชา *</label>
                                                    <input type="text" x-model="step.step2_grade_subject" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น ศิลปะ ม.2">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">นวัตกรรม / สื่อ / เทคนิคการสอนที่ใช้ *</label>
                                                    <input type="text" x-model="step.step2_innovation" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น ชุดสื่อวิดีโอสาธิต 5 นาที">
                                                </div>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. จุดประสงค์การเรียนรู้ (KPA) *</label>
                                                <textarea x-model="step.step2_learning_objectives" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="ระบุจุดประสงค์การเรียนรู้ด้านพุทธิพิสัย (K), ทักษะพิสัย (P), และเจตคติ (A)..."></textarea>
                                            </div>

                                            <!-- Comments sharing box (Step 2) -->
                                            <div class="border-t border-slate-100 pt-6 mt-6">
                                                <h5 class="text-xs font-bold text-slate-800 mb-4 flex items-center gap-2">
                                                    <i class="fa-regular fa-comments text-emerald-500"></i>
                                                    ร่วมเสนอความคิดเห็นและระดมสมอง (Buddy Teacher & Expert)
                                                </h5>
                                                
                                                <!-- List of previous ideas -->
                                                <div class="space-y-3 mb-4 max-h-[40vh] overflow-y-auto pr-1">
                                                    <template x-if="!step.step2_idea_sharing || step.step2_idea_sharing.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2">ยังไม่มีสมาชิกมาแสดงความเห็นเพิ่มเติม</p>
                                                    </template>
                                                    <template x-for="idea in step.step2_idea_sharing" :key="idea.user_id + idea.updated_at">
                                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-left">
                                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                                <span class="font-extrabold text-slate-800 text-[10px]" x-text="idea.user_name"></span>
                                                                <span class="text-[9px] text-slate-400" x-text="idea.updated_at"></span>
                                                            </div>
                                                            <p class="text-slate-650 text-xs" x-html="idea.comment"></p>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Write comment form (Buddy / Observer) -->
                                                <div class="space-y-2" x-show="isMember()">
                                                    <textarea x-model="commentForm.comment" rows="7" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="พิมพ์แบ่งปันหรือเสนอแนะไอเดียการสอนเพิ่มที่นี่..."></textarea>
                                                    <div class="flex justify-end">
                                                        <button type="button" @click="submitComment(step, 'step2_idea_sharing')" class="bg-slate-700 hover:bg-slate-800 text-white font-bold text-[10px] px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                                                            <i class="fa-solid fa-paper-plane"></i> ส่งความเห็น
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- STEP 3: Develop form & Files upload -->
                                    <template x-if="step.sequence === 3">
                                        <div class="space-y-5">
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. รายการปรับปรุงแก้ไขแผนการจัดการเรียนรู้ (Change Log) *</label>
                                                <textarea x-model="step.step3_change_log" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="อธิบายรายละเอียดหรือหัวข้อที่มีการแก้ไขปรับปรุงหลังจากวิพากษ์แผนร่วมกัน..."></textarea>
                                            </div>

                                            <div class="flex items-center gap-3 py-2 bg-slate-50 rounded-xl px-4 border border-slate-100">
                                                <input type="checkbox" id="ready_status" x-model="step.step3_ready_status" :disabled="!isModelTeacher()" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                <label for="ready_status" class="text-xs font-bold text-slate-700 cursor-pointer">
                                                    แผนการสอนผ่านการตรวจสอบ ปรับปรุง และมีความพร้อมนำไปสอนจริง (Ready Status)
                                                </label>
                                            </div>

                                            <!-- Files plan upload -->
                                            <div class="space-y-2">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. แฟ้มไฟล์เอกสารแผนการเรียนรู้ฉบับแก้ไข (.pdf, .docx, .pptx) *</label>
                                                
                                                <!-- List plan files -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                    <template x-if="!step.step3_plan_file_paths || step.step3_plan_file_paths.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2 md:col-span-2 text-left">ยังไม่มีการแนบไฟล์แผนการสอน</p>
                                                    </template>
                                                    <template x-for="file in step.step3_plan_file_paths" :key="file.path">
                                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-left">
                                                            <a :href="file.url" target="_blank" class="text-slate-650 hover:text-emerald-600 font-bold text-xs truncate max-w-[80%] flex items-center gap-1.5">
                                                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                                                <span x-text="file.name"></span>
                                                            </a>
                                                            <button type="button" @click="deleteFile(step, file.path, 'step3_plan_file_paths')" class="text-slate-400 hover:text-rose-500 p-1 text-[10px]" title="ลบไฟล์">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Upload action -->
                                                <div class="mt-2" x-show="isModelTeacher()">
                                                    <input type="file" :id="'file_upload_step3_' + step.id" @change="uploadFiles($event, step, 'step3_plan_file_paths')" multiple class="hidden">
                                                    <label :for="'file_upload_step3_' + step.id" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-xl cursor-pointer shadow-sm transition">
                                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                                        อัปโหลดไฟล์แผนการสอน
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Supervision/Critique Comments box (Step 3) -->
                                            <div class="border-t border-slate-100 pt-6 mt-6">
                                                <h5 class="text-xs font-bold text-slate-800 mb-4 flex items-center gap-2">
                                                    <i class="fa-solid fa-chalkboard-user text-emerald-500"></i>
                                                    คำแนะนำเชิงวิพากษ์จากผู้เชี่ยวชาญ / สมาชิก (Supervision Notes)
                                                </h5>
                                                
                                                <!-- List comments -->
                                                <div class="space-y-3 mb-4 max-h-[40vh] overflow-y-auto pr-1">
                                                    <template x-if="!step.step3_supervision_notes || step.step3_supervision_notes.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2">ยังไม่มีบันทึกคำแนะนำจากสมาชิกลงในขั้นตอนนี้</p>
                                                    </template>
                                                    <template x-for="note in step.step3_supervision_notes" :key="note.user_id + note.updated_at">
                                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-left">
                                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                                <span class="font-extrabold text-slate-800 text-[10px]" x-text="note.user_name"></span>
                                                                <span class="text-[9px] text-slate-400" x-text="note.updated_at"></span>
                                                            </div>
                                                            <p class="text-slate-650 text-xs" x-html="note.comment"></p>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Write comment form -->
                                                <div class="space-y-2" x-show="isMember()">
                                                    <textarea x-model="commentForm.comment" rows="7" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="ระบุคำวิจารณ์เชิงบวกและจุดที่ควรพัฒนาเพิ่มเติมในแผนนี้..."></textarea>
                                                    <div class="flex justify-end">
                                                        <button type="button" @click="submitComment(step, 'step3_supervision_notes')" class="bg-slate-700 hover:bg-slate-800 text-white font-bold text-[10px] px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                                                            <i class="fa-solid fa-paper-plane"></i> ส่งบันทึกคำแนะนำ
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- STEP 4: Do & See form & Observations -->
                                    <template x-if="step.sequence === 4">
                                        <div class="space-y-5">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">วัน-เวลาเปิดห้องเรียนจริง *</label>
                                                    <!-- Convert dynamic format to datetime-local string if needed -->
                                                    <input type="datetime-local" 
                                                           :value="step.step4_class_date ? new Date(step.step4_class_date).toISOString().slice(0, 16) : ''"
                                                           @change="step.step4_class_date = $event.target.value"
                                                           :disabled="!isModelTeacher()" 
                                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คาบเรียนที่ *</label>
                                                    <input type="text" x-model="step.step4_period" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น คาบที่ 3 เวลา 10:00 - 11:00 น.">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ห้องเรียนที่สอน *</label>
                                                    <input type="text" x-model="step.step4_room" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น ห้องปฏิบัติการศิลปะ อาคาร 2">
                                                </div>
                                            </div>



                                            <!-- Observations by observers (Step 4) -->
                                            <div class="border-t border-slate-100 pt-6 mt-6">
                                                <h5 class="text-xs font-bold text-slate-800 mb-4 flex items-center gap-2">
                                                    <i class="fa-solid fa-magnifying-glass text-emerald-500"></i>
                                                    บันทึกการสังเกตการณ์การเรียนรู้ของนักเรียน (Observations)
                                                </h5>
                                                
                                                <!-- List observations -->
                                                <div class="space-y-4 mb-4 max-h-[45vh] overflow-y-auto pr-1">
                                                    <template x-if="!step.step4_observations || step.step4_observations.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2">ยังไม่มีบันทึกสังเกตการณ์ห้องเรียน</p>
                                                    </template>
                                                    <template x-for="obs in step.step4_observations" :key="obs.user_id + obs.updated_at">
                                                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-left space-y-2">
                                                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-1.5">
                                                                <span class="font-extrabold text-slate-850 text-[10px]" x-text="obs.user_name"></span>
                                                                <span class="text-[9px] text-slate-400" x-text="obs.updated_at"></span>
                                                            </div>
                                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                                                <div>
                                                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide">พฤติกรรมการเรียนรู้:</span>
                                                                    <p class="text-slate-650 mt-0.5" x-html="obs.learning_behavior || '-'"></p>
                                                                </div>
                                                                <div>
                                                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide">ปัญหา / อุปสรรคที่พบ:</span>
                                                                    <p class="text-slate-650 mt-0.5" x-html="obs.problems || '-'"></p>
                                                                </div>
                                                                <div>
                                                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide">การแก้ไข / ตอบสนอง:</span>
                                                                    <p class="text-slate-650 mt-0.5" x-html="obs.response || '-'"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Add observation form -->
                                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-150 text-left space-y-3" x-show="isMember()">
                                                    <h6 class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">เพิ่มแบบบันทึกสังเกตการณ์ของคุณ</h6>
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] font-bold text-slate-500">พฤติกรรมการเรียนรู้ที่น่าสนใจ</label>
                                                            <textarea x-model="commentForm.learning_behavior" rows="7" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none transition" placeholder="เช่น นักเรียนกลุ่มที่ 3 แสดงความกระตือรือร้นในการใช้สีน้ำ"></textarea>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] font-bold text-slate-500">ปัญหาและสิ่งท้าทายที่สังเกตพบ</label>
                                                            <textarea x-model="commentForm.problems" rows="7" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none transition" placeholder="เช่น นักเรียนบางส่วนเขียนพู่กันเปียกเกินไปทำให้กระดาษขาด"></textarea>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] font-bold text-slate-500">วิธีจัดการปัญหาหรือการตอบสนองครู</label>
                                                            <textarea x-model="commentForm.response" rows="7" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none transition" placeholder="เช่น ครูแนะนำให้นำทิชชู่มาซับพู่กันและแก้ไขได้รวดเร็ว"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <button type="button" @click="submitComment(step, 'step4_observations')" class="bg-slate-700 hover:bg-slate-800 text-white font-bold text-[10px] px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                                                            <i class="fa-solid fa-save"></i> บันทึกผลสังเกตการณ์
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- STEP 5: Reflect form -->
                                    <template x-if="step.sequence === 5">
                                        <div class="space-y-5">
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. ผลการสะท้อนความคิดตนเองของครูต้นแบบ (Self Reflection) *</label>
                                                <textarea x-model="step.step5_self_reflection" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="ครูสรุปความรู้สึกและสิ่งที่ตนทำได้ดี รวมถึงสิ่งที่ต้องแก้ไขรอบต่อไป..."></textarea>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border border-slate-100 p-4 rounded-xl bg-slate-50/50">
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">จำนวนนักเรียนทั้งหมด (คน) *</label>
                                                    <input type="number" min="0" x-model.number="step.step5_total_students" :disabled="!isModelTeacher()" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น 40">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">จำนวนนักเรียนที่ผ่านเกณฑ์ (คน) *</label>
                                                    <input type="number" min="0" x-model.number="step.step5_passed_students" :disabled="!isModelTeacher()" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="เช่น 35">
                                                </div>
                                                <div class="space-y-1.5 flex flex-col justify-end">
                                                    <label class="text-xs font-bold text-slate-450 mb-1.5">ร้อยละนักเรียนที่ประเมินผ่านเกณฑ์ (KPI %)</label>
                                                    <div class="bg-emerald-50 text-emerald-700 font-extrabold text-sm py-2 px-4 rounded-xl border border-emerald-100 text-center select-none" 
                                                         x-text="calculatePassPercent(step.step5_passed_students, step.step5_total_students) + ' %'">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. ผลลัพธ์เชิงคุณภาพภาพรวมชั้นเรียน *</label>
                                                <textarea x-model="step.step5_qualitative_result" :disabled="!isModelTeacher()" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="อธิบายข้อเท็จจริงเกี่ยวกับคุณภาพและระดับการยอมรับพัฒนาการด้านเจตคติและทักษะของนักเรียน..."></textarea>
                                            </div>

                                            <!-- Peer Reflection box (Step 5) -->
                                            <div class="border-t border-slate-100 pt-6 mt-6">
                                                <h5 class="text-xs font-bold text-slate-800 mb-4 flex items-center gap-2">
                                                    <i class="fa-solid fa-users-viewfinder text-emerald-500"></i>
                                                    สะท้อนผลร่วมกันเพื่อปรับวงรอบการเรียนรู้ (Peer Reflections)
                                                </h5>
                                                
                                                <!-- List reflections -->
                                                <div class="space-y-3 mb-4 max-h-[40vh] overflow-y-auto pr-1">
                                                    <template x-if="!step.step5_peer_reflections || step.step5_peer_reflections.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2">ยังไม่มีบันทึกสะท้อนผลร่วมกับสมาชิก</p>
                                                    </template>
                                                    <template x-for="ref in step.step5_peer_reflections" :key="ref.user_id + ref.updated_at">
                                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-left space-y-1">
                                                            <div class="flex items-center justify-between gap-2 border-b border-slate-100/50 pb-1">
                                                                <span class="font-extrabold text-slate-800 text-[10px]" x-text="ref.user_name"></span>
                                                                <span class="text-[9px] text-slate-400" x-text="ref.updated_at"></span>
                                                            </div>
                                                            <div class="text-xs text-slate-650 space-y-1">
                                                                <p><strong>จุดเด่น/ชื่นชม:</strong> <span x-html="ref.strengths || '-'"></span></p>
                                                                <p><strong>ข้อเสนอแนะรอบต่อไป:</strong> <span x-html="ref.suggestions || '-'"></span></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Write comment form -->
                                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-150 text-left space-y-3" x-show="isMember()">
                                                    <h6 class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">เขียนคำสะท้อนความคิดเห็นของคุณ</h6>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] font-bold text-slate-500">จุดเด่นที่ทำได้ดีชื่นชม</label>
                                                            <textarea x-model="commentForm.strengths" rows="7" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none transition" placeholder="ระบุสิ่งที่ครูต้นแบบสื่อสารและดำเนินการสอนได้ยอดเยี่ยม..."></textarea>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] font-bold text-slate-500">ข้อเสนอแนะในการปรับวงรอบ PLC ถัดไป</label>
                                                            <textarea x-model="commentForm.suggestions" rows="7" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none transition" placeholder="ระบุแนวทางที่สามารถนำไปพัฒนาร่วมกันรอบหน้า..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <button type="button" @click="submitComment(step, 'step5_peer_reflections')" class="bg-slate-700 hover:bg-slate-800 text-white font-bold text-[10px] px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                                                            <i class="fa-solid fa-paper-plane"></i> ส่งผลการสะท้อนร่วม
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- STEP 6: Publish form & KM files -->
                                    <template x-if="step.sequence === 6">
                                        <div class="space-y-5">
                                            <div class="space-y-1.5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. บทคัดย่อ / สรุปความรู้ผลงานสิ่งประดิษฐ์ (Best Practice) *</label>
                                                <textarea x-model="step.step6_best_practice" :disabled="!isModelTeacher()" rows="11" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60" placeholder="สรุปเนื้อหาสิ่งประดิษฐ์/นวัตกรรมที่ท่านได้รับจากกระบวนการ PLC 6 ขั้นตอน เพื่อเป็นกรณีศึกษาให้ครูท่านอื่น..."></textarea>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ระดับสิทธิ์การเผยแพร่ความรู้ (Visibility) *</label>
                                                    <select x-model="step.step6_visibility" :disabled="!isModelTeacher()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition disabled:opacity-60">
                                                        <option value="group">เห็นเฉพาะสมาชิกในกลุ่ม (Private)</option>
                                                        <option value="school">เผยแพร่ภายในเครือข่ายสถานศึกษา (School Network)</option>
                                                        <option value="public">เผยแพร่สู่ภายนอกเป็นสาธารณะ (Public KM)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- KM Files upload -->
                                            <div class="space-y-2 border-t border-slate-100 pt-5 mt-5">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. แฟ้มคู่มือนวัตกรรม สื่อการจัดการสอน หรือเอกสารเผยแพร่ (KM Final Files)</label>
                                                
                                                <!-- List final files -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                    <template x-if="!step.step6_final_file_paths || step.step6_final_file_paths.length === 0">
                                                        <p class="text-slate-400 text-xs italic py-2 md:col-span-2 text-left">ยังไม่มีเอกสารแนบประกอบ Best Practice</p>
                                                    </template>
                                                    <template x-for="file in step.step6_final_file_paths" :key="file.path">
                                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-left">
                                                            <a :href="file.url" target="_blank" class="text-slate-650 hover:text-emerald-600 font-bold text-xs truncate max-w-[80%] flex items-center gap-1.5">
                                                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                                                <span x-text="file.name"></span>
                                                            </a>
                                                            <button type="button" @click="deleteFile(step, file.path, 'step6_final_file_paths')" class="text-slate-400 hover:text-rose-500 p-1 text-[10px]" title="ลบไฟล์">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Upload button -->
                                                <div class="mt-2" x-show="isModelTeacher()">
                                                    <input type="file" :id="'file_upload_step6_' + step.id" @change="uploadFiles($event, step, 'step6_final_file_paths')" multiple class="hidden">
                                                    <label :for="'file_upload_step6_' + step.id" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-xl cursor-pointer shadow-sm transition">
                                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                                        อัปโหลดคู่มือนวัตกรรม/ผลงาน
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Image Attachments Section (For all 6 steps) -->
                                    <div class="border-t border-slate-100 pt-5 mt-5 space-y-3 text-left">
                                        <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="fa-solid fa-images text-emerald-500"></i>
                                            รูปภาพ / หลักฐานการดำเนินงานในขั้นตอนนี้
                                        </h5>
                                        
                                        <!-- List images in grid -->
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                            <template x-if="!step.file_path || step.file_path.length === 0">
                                                <p class="text-slate-400 text-xs italic py-2 sm:col-span-4 text-left">ยังไม่มีการอัปโหลดรูปภาพหลักฐานประกอบ</p>
                                            </template>
                                            <template x-for="img in step.file_path" :key="img.path">
                                                <div class="relative group aspect-video rounded-xl overflow-hidden border border-slate-200/60 shadow-sm bg-slate-100">
                                                    <img :src="img.url" alt="Step evidence image" class="w-full h-full object-cover">
                                                    
                                                    <!-- Overlay with actions -->
                                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                                        <a :href="img.url" target="_blank" class="bg-white/90 hover:bg-white text-slate-700 p-2 rounded-full shadow-sm text-xs" title="ขยายดูรูปภาพ">
                                                            <i class="fa-solid fa-expand"></i>
                                                        </a>
                                                        <button type="button" @click="deleteFile(step, img.path, 'file_path')" class="bg-rose-600/90 hover:bg-rose-600 text-white p-2 rounded-full shadow-sm text-xs" title="ลบภาพ" x-show="isModelTeacher()">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Upload button -->
                                        <div class="mt-2" x-show="isModelTeacher()">
                                            <input type="file" :id="'image_upload_generic_' + step.id" @change="uploadFiles($event, step, 'file_path')" accept="image/*" multiple class="hidden">
                                            <label :for="'image_upload_generic_' + step.id" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-xl cursor-pointer shadow-sm transition">
                                                <i class="fa-solid fa-image"></i>
                                                อัปโหลดรูปภาพประกอบขั้นตอน
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Model Teacher: Action draft / complete step -->
                                    <div class="border-t border-slate-100 pt-6 mt-6 flex justify-end gap-3" x-show="isModelTeacher()">
                                        <button type="button" 
                                                @click="saveStepData(step, 0)" 
                                                :disabled="saving"
                                                class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition disabled:opacity-60">
                                            บันทึกร่างข้อมูล (ยังไม่เสร็จสิ้น)
                                        </button>
                                        <button type="button" 
                                                @click="saveStepData(step, 2)" 
                                                :disabled="saving"
                                                class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 transition disabled:opacity-60 flex items-center gap-1.5 shadow-md shadow-emerald-50">
                                            <template x-if="saving">
                                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                            </template>
                                            บันทึกและเสร็จสิ้นขั้นตอนนี้
                                        </button>
                                    </div>

                                </div>
                            </template>

                        </div>

                    </div>
                </template>
        </div>

        <!-- Add/Edit PLC Group Modal -->
        <div x-show="groupModal.open" class="fixed inset-0 z-40 flex items-start justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto pt-10 md:pt-20" x-transition x-cloak>
            <form @submit.prevent="saveGroup()" class="bg-white rounded-[2rem] max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col h-[650px] max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-users text-emerald-500"></i>
                        <span x-text="groupModal.id ? 'แก้ไขกลุ่ม PLC' : 'สร้างกลุ่ม PLC ใหม่'"></span>
                    </h3>
                    <button type="button" @click="groupModal.open = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <!-- Modal Tabs -->
                <div class="px-6 bg-slate-50 border-b border-slate-100 flex gap-4 shrink-0">
                    <button type="button" 
                            @click="groupModal.activeTab = 'info'" 
                            class="py-3 text-xs font-bold transition-all relative border-b-2"
                            :class="groupModal.activeTab === 'info' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                    >
                        <i class="fa-solid fa-circle-info mr-1"></i> ข้อมูลกลุ่ม PLC
                    </button>
                    <button type="button" 
                            @click="groupModal.activeTab = 'members'" 
                            class="py-3 text-xs font-bold transition-all relative border-b-2 flex items-center gap-1.5"
                            :class="groupModal.activeTab === 'members' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                    >
                        <i class="fa-solid fa-user-plus mr-1"></i> จัดการสมาชิก
                        <span class="bg-slate-200 text-slate-700 text-[10px] px-1.5 py-0.5 rounded-full" x-text="groupModal.members.length"></span>
                    </button>
                </div>
                
                <div class="p-6 md:p-8 space-y-5 overflow-y-auto flex-1 text-left">
                    <!-- Tab 1: Group Info -->
                    <div x-show="groupModal.activeTab === 'info'" class="space-y-5">
                        <!-- Group Name Input -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อกลุ่ม PLC *</label>
                            <input type="text" x-model="groupModal.name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น นวัตกรรมภาพเขียนสีน้ำมัธยม">
                        </div>

                        <!-- Department -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">กลุ่มสาระการเรียนรู้ / ฝ่ายที่รับผิดชอบ *</label>
                            <select x-model="groupModal.department" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                <option value="">เลือกกลุ่มสาระการเรียนรู้...</option>
                                <template x-for="dept in departments" :key="dept">
                                    <option :value="dept" x-text="dept" :selected="groupModal.department === dept"></option>
                                </template>
                            </select>
                        </div>

                        <!-- School Network & School Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เครือข่ายสถานศึกษา *</label>
                                <select x-model="groupModal.school_group" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                    <option value="">เลือกเครือข่ายสถานศึกษา...</option>
                                    <template x-for="net in networks" :key="net">
                                        <option :value="net" x-text="net" :selected="groupModal.school_group === net"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">โรงเรียน / สถานศึกษา *</label>
                                <select x-model="groupModal.school_name" @change="onSchoolChange()" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                    <option value="">เลือกสถานศึกษา...</option>
                                    <template x-for="sch in filteredSchoolsForModal" :key="sch.name">
                                        <option :value="sch.name" x-text="sch.name" :selected="groupModal.school_name === sch.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Semester & Year -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ภาคเรียนที่ *</label>
                                <select x-model="groupModal.semester" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                    <option :value="1">ภาคเรียนที่ 1</option>
                                    <option :value="2">ภาคเรียนที่ 2</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ปีการศึกษา *</label>
                                <input type="text" x-model="groupModal.academic_year" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น 2569">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คำอธิบายกลุ่ม</label>
                            <textarea x-model="groupModal.description" rows="7" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="ระบุวัตถุประสงค์โดยสรุปของกลุ่ม PLC..."></textarea>
                        </div>
                    </div>

                    <!-- Tab 2: Members Management -->
                    <div x-show="groupModal.activeTab === 'members'" class="space-y-4">
                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-150">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">จัดการรายชื่อสมาชิก</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">เพิ่มคุณครูในเครือข่ายสถานศึกษาเดียวกันเข้ามาในกลุ่ม PLC</p>
                            </div>
                            <button type="button" @click="addMember()" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 px-3.5 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 transition active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-plus text-[10px]"></i> เพิ่มสมาชิก
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(member, index) in groupModal.members" :key="index">
                                <div class="flex items-center gap-2 bg-slate-50 border border-slate-150 p-2 rounded-xl">
                                    <!-- Searchable Dropdown for Teachers Selection -->
                                    <div class="flex-1 relative" x-data="{ openDropdown: false, searchQuery: '' }" @click.away="openDropdown = false">
                                        <!-- Trigger Button -->
                                        <button type="button" 
                                                @click="openDropdown = !openDropdown"
                                                class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs text-left flex justify-between items-center hover:bg-slate-50 transition cursor-pointer select-none font-bold text-slate-700"
                                        >
                                            <span x-text="member.user_id ? (teachers.find(t => t.id == member.user_id)?.name ?? 'เลือกสมาชิกครู...') : 'เลือกสมาชิกครู...'"></span>
                                            <i class="fa-solid fa-chevron-down text-slate-400 text-[10px]"></i>
                                        </button>
                                        
                                        <!-- Dropdown Panel -->
                                        <div x-show="openDropdown" 
                                             class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-50 p-2 space-y-2 origin-top"
                                             x-transition
                                             x-cloak
                                        >
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input type="text" 
                                                       x-model="searchQuery" 
                                                       placeholder="ค้นหาชื่อครู..." 
                                                       class="w-full pl-7 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                                                       @keydown.enter.prevent
                                                >
                                                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-[10px]"></i>
                                            </div>
                                            
                                            <!-- Teachers List -->
                                            <div class="max-h-48 overflow-y-auto space-y-0.5 text-left">
                                                <template x-for="t in teachers.filter(t => t.name.toLowerCase().includes(searchQuery.toLowerCase()))" :key="t.id">
                                                    <button type="button" 
                                                            @click="member.user_id = t.id; openDropdown = false; searchQuery = '';"
                                                            class="w-full text-left px-2.5 py-1.5 text-xs rounded hover:bg-emerald-50 hover:text-emerald-700 font-bold transition flex items-center justify-between"
                                                            :class="member.user_id == t.id ? 'bg-emerald-50 text-emerald-700' : 'text-slate-650'"
                                                    >
                                                        <span x-text="t.name"></span>
                                                        <i class="fa-solid fa-check text-[10px]" x-show="member.user_id == t.id"></i>
                                                    </button>
                                                </template>
                                                <template x-if="teachers.filter(t => t.name.toLowerCase().includes(searchQuery.toLowerCase())).length === 0">
                                                    <div class="px-2.5 py-4 text-center text-slate-450 text-[10px] italic">
                                                        ไม่พบข้อมูลครูที่ค้นหา
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <select x-model="member.role" required class="w-32 bg-white border border-slate-200 rounded-lg p-2 text-xs">
                                        <option value="ครูคู่หู">ครูคู่หู</option>
                                        <option value="พี่เลี้ยง">พี่เลี้ยง</option>
                                        <option value="ผู้เชี่ยวชาญ">ผู้เชี่ยวชาญ</option>
                                        <option value="ผู้สังเกตการณ์">ผู้สังเกตการณ์</option>
                                    </select>
                                    <button type="button" @click="removeMember(index)" class="text-slate-400 hover:text-rose-500 p-1.5" title="ลบออก">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </template>
                            <template x-if="groupModal.members.length === 0">
                                <div class="px-6 py-12 text-center text-slate-400 bg-slate-25/50 border border-dashed border-slate-200 rounded-2xl space-y-2">
                                    <i class="fa-solid fa-user-group text-slate-300 text-3xl"></i>
                                    <p class="text-xs font-bold">ยังไม่มีการเพิ่มสมาชิก</p>
                                    <p class="text-[10px] text-slate-400">กดปุ่ม "เพิ่มสมาชิก" ด้านบนเพื่อเริ่มเพิ่มคณะทำงาน</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="groupModal.open = false" class="px-5 py-2.5 border border-slate-200 text-slate-650 rounded-xl font-bold text-xs hover:bg-slate-100 transition">
                        ยกเลิก
                    </button>
                    <button type="submit" :disabled="saving" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <template x-if="saving">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </template>
                        บันทึกกลุ่ม PLC
                    </button>
                </div>
            </form>
        </div>

        <!-- Teacher Profile Detail Modal (Same as /reports) -->
        <div x-show="modal.open" 
             class="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto pt-10 md:pt-20"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             x-cloak>
            
            <div class="bg-white w-full max-w-5xl rounded-3xl border border-slate-100 shadow-2xl flex flex-col transform overflow-hidden h-[750px] max-h-[85vh]"
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
                        <p class="text-[10px] text-slate-400 mt-1 font-bold" x-text="modal.data.id ? `รหัสชุดข้อมูล: SURV-${modal.data.id}` : 'บัญชีรายชื่อระบบ'"></p>
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
                                    <i class="fa-solid fa-users text-slate-350 text-6xl"></i>
                                </template>
                            </div>
                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs leading-relaxed">
                                <div><span class="font-bold text-slate-400 block mb-0.5">ชื่อ-นามสกุล</span> <span class="font-extrabold text-slate-800 text-sm" x-text="modal.data.prefix ? `${modal.data.prefix} ${modal.data.first_name} ${modal.data.last_name}` : modal.data.first_name"></span></div>
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
                                        <i class="fa-solid fa-circle-minus text-[10px]"></i> ถูกบล็อก (เฉพาะเจ้าตัว/แอดมิน)
                                    </span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-450 block mb-0.5">วันเกิด / อายุ</span>
                                    <span class="font-semibold text-slate-800" x-text="`${formatThaiDate(modal.data.birth_date)} (อายุ ${modal.data.age} ปี)`" x-show="modal.data.birth_date"></span>
                                    <span class="inline-flex items-center gap-1 text-slate-400 italic text-[11px] font-semibold" x-show="!modal.data.birth_date">
                                        <i class="fa-solid fa-circle-minus text-[10px]"></i> ถูกบล็อก (เฉพาะเจ้าตัว/แอดมิน)
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
                            </div>
                        </template>
                    </div>

                    <!-- TAB 2: TEACHING & ALIGNMENT -->
                    <div x-show="modal.activeTab === 'teaching'" class="space-y-4" x-cloak>
                        <!-- Alignment block -->
                        <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-100 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-square-poll-vertical text-lg"></i>
                            </div>
                            <div class="text-xs">
                                <span class="block font-extrabold text-emerald-800" x-text="`ระดับความเข้ากันได้กับวิชาเอก: ${modal.data.alignment.label}`"></span>
                                <p class="text-emerald-700 mt-1" x-text="modal.data.alignment.desc"></p>
                            </div>
                        </div>

                        <!-- Subjects Taught List -->
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

                        <!-- Education List -->
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
                            <div class="p-4 bg-slate-50 rounded-2xl text-center border border-dashed border-slate-200 text-slate-440 text-xs font-bold flex items-center justify-center gap-2" 
                                 x-show="!modal.data.educations" x-cloak>
                                <i class="fa-solid fa-user-lock text-sm text-slate-450"></i>
                                <span>ข้อมูลประวัติการศึกษาแบบละเอียดถูกจำกัดสิทธิ์การเข้าชม</span>
                            </div>
                            <template x-if="modal.data.educations && modal.data.educations.length === 0">
                                <div class="p-4 bg-slate-50 rounded-2xl text-center border border-dashed border-slate-200 text-slate-450 text-xs font-bold">
                                    ไม่มีข้อมูลประวัติการศึกษาในระบบ
                                </div>
                            </template>
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
                                <p class="mt-0.5"><span class="font-bold text-slate-400">วันที่ทดสอบ:</span> <span class="font-semibold" x-text="modal.data.cefr ? formatThaiDate(modal.data.cefr.cert_date) : '-'"></span></p>
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
                                <p class="mt-0.5"><span class="font-bold text-slate-400">วันที่ทดสอบ:</span> <span class="font-semibold" x-text="modal.data.hsk ? formatThaiDate(modal.data.hsk.cert_date) : '-'"></span></p>
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
                                    <div class="flex justify-between items-center text-[9px] text-slate-400">
                                        <span>สถานะการเข้าเรียน: <strong class="text-slate-650" x-text="course.progress === 100 ? 'สำเร็จหลักสูตร' : 'กำลังดำเนินการเรียน'"></strong></span>
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

    <!-- Scripts section containing Alpine code -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function plcManager() {
            return {
                        groups: [],
                        teachers: [],
                        networks: [],
                        schools: [],
                        departments: [],
                        currentUser: {},
                        currentGroup: null,
                        activeTab: 1,
                        loading: true,
                        saving: false,
                        deleting: false,
                        toast: { show: false, message: '', type: 'success' },
                        
                        currentLevel: 'networks',
                        selectedNetwork: null,
                        selectedSchool: null,
                        
                        groupModal: {
                            open: false,
                            id: null,
                            name: '',
                            description: '',
                            semester: 1,
                            academic_year: '2569',
                            department: '',
                            school_group: '',
                            school_name: '',
                            members: [],
                            activeTab: 'info'
                        },

                        modal: {
                            open: false,
                            activeTab: 'general',
                            data: {
                                id: 0,
                                prefix: '',
                                first_name: '',
                                last_name: '',
                                position: '',
                                school_name: '',
                                school_network: '',
                                recruitment_subject: '',
                                other_workload: '',
                                personalid: '',
                                birth_date: '',
                                age: '',
                                school: null,
                                educations: [],
                                subjects: [],
                                awards: [],
                                lms_courses: [],
                                alignment: { label: '', desc: '' }
                            }
                        },

                        get networksWithGroups() {
                            const counts = {};
                            this.groups.forEach(g => {
                                const net = g.school_group || 'อื่นๆ / ไม่ระบุเครือข่าย';
                                counts[net] = (counts[net] || 0) + 1;
                            });
                            return Object.entries(counts).map(([name, count]) => ({ name, count }));
                        },

                        get schoolsInSelectedNetwork() {
                            if (!this.selectedNetwork) return [];
                            const counts = {};
                            this.groups.forEach(g => {
                                const net = g.school_group || 'อื่นๆ / ไม่ระบุเครือข่าย';
                                if (net === this.selectedNetwork) {
                                    const school = g.school_name || 'ไม่ระบุโรงเรียน';
                                    counts[school] = (counts[school] || 0) + 1;
                                }
                            });
                            return Object.entries(counts).map(([name, count]) => ({ name, count }));
                        },

                        get groupsInSelectedSchool() {
                            if (!this.selectedSchool || !this.selectedNetwork) return [];
                            return this.groups.filter(g => {
                                const net = g.school_group || 'อื่นๆ / ไม่ระบุเครือข่าย';
                                const school = g.school_name || 'ไม่ระบุโรงเรียน';
                                return net === this.selectedNetwork && school === this.selectedSchool;
                            });
                        },

                        get filteredSchoolsForModal() {
                            if (!this.groupModal.school_group) return this.schools;
                            return this.schools.filter(s => s.school_group === this.groupModal.school_group);
                        },

                        selectNetwork(netName) {
                            this.selectedNetwork = netName;
                            this.currentLevel = 'schools';
                        },

                        selectSchool(schName) {
                            this.selectedSchool = schName;
                            this.currentLevel = 'groups';
                        },

                        goBackToNetworks() {
                            this.selectedNetwork = null;
                            this.currentLevel = 'networks';
                        },

                        goBackToSchools() {
                            this.selectedSchool = null;
                            this.currentLevel = 'schools';
                        },

                        resetDashboard() {
                            this.currentGroup = null;
                            this.selectedSchool = null;
                            this.selectedNetwork = null;
                            this.currentLevel = 'networks';
                        },

                        onSchoolChange() {
                            const selected = this.schools.find(s => s.name === this.groupModal.school_name);
                            if (selected && selected.school_group) {
                                this.groupModal.school_group = selected.school_group;
                            }
                        },

                        commentForm: {
                            comment: '',
                            learning_behavior: '',
                            problems: '',
                            response: '',
                            strengths: '',
                            suggestions: ''
                        },

                        reviewForm: {
                            status: 0,
                            admin_comment: ''
                        },

                        init() {
                            this.fetchData(true);
                        },

                        showToast(message, type = 'success') {
                            this.toast = { show: true, message, type };
                            setTimeout(() => { this.toast.show = false; }, 3500);
                        },

                        showConfirm(opts) {
                            window.showConfirm(opts);
                        },

                        fetchData(showSpinner = false) {
                            if (showSpinner) {
                                this.loading = true;
                            }
                            axios.get('{{ route("plc.data") }}')
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.groups = res.data.data.groups;
                                        this.teachers = res.data.data.teachers;
                                        this.networks = res.data.data.networks || [];
                                        this.schools = res.data.data.schools || [];
                                        this.currentUser = res.data.data.currentUser;
                                        this.departments = res.data.data.departments || [];
                                        
                                        if (this.currentGroup) {
                                            const updated = this.groups.find(g => g.id === this.currentGroup.id);
                                            if (updated) {
                                                this.selectGroup(updated);
                                            } else {
                                                this.currentGroup = null;
                                            }
                                        }
                                    } else {
                                        this.showToast(res.data.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล', 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    this.showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                                })
                                .finally(() => {
                                    this.loading = false;
                                });
                        },

                        selectGroup(group) {
                            this.currentGroup = group;
                            this.loadReviewForm();
                        },

                        loadReviewForm() {
                            if (!this.currentGroup) return;
                            const step = this.currentGroup.steps.find(s => s.sequence == this.activeTab);
                            if (step) {
                                this.reviewForm.status = step.status;
                                this.reviewForm.admin_comment = step.admin_comment || '';
                            }
                        },

                        changeTab(tab) {
                            this.activeTab = tab;
                            this.loadReviewForm();
                        },

                        openCreateModal() {
                            this.groupModal = {
                                open: true,
                                id: null,
                                name: '',
                                description: '',
                                semester: 1,
                                academic_year: (new Date().getFullYear() + 543).toString(),
                                department: '',
                                school_group: this.currentUser.school_group || '',
                                school_name: this.currentUser.school_name || '',
                                members: [],
                                activeTab: 'info'
                            };
                        },

                        openEditGroupModal(group) {
                            this.groupModal = {
                                open: true,
                                id: group.id,
                                name: group.name,
                                description: group.description || '',
                                semester: group.semester,
                                academic_year: group.academic_year,
                                department: group.department,
                                school_group: group.school_group || '',
                                school_name: group.school_name || '',
                                members: group.members.map(m => ({
                                    user_id: m.user_id,
                                    role: m.role
                                })),
                                activeTab: 'info'
                            };
                        },

                        addMember() {
                            this.groupModal.members.push({ user_id: '', role: 'ครูคู่หู' });
                        },

                        removeMember(index) {
                            this.groupModal.members.splice(index, 1);
                        },

                        saveGroup() {
                            if (!this.groupModal.name || !this.groupModal.department || !this.groupModal.school_group || !this.groupModal.school_name) {
                                this.showToast('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน รวมถึงโรงเรียนและเครือข่ายสถานศึกษา', 'error');
                                return;
                            }

                            this.saving = true;
                            axios.post('{{ route("plc.save") }}', this.groupModal)
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast(res.data.message, 'success');
                                        this.groupModal.open = false;
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'ไม่สามารถบันทึกได้', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast(err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        confirmDeleteGroup(groupId) {
                            window.showConfirm({
                                title: 'ยืนยันการลบกลุ่ม PLC?',
                                text: 'คุณแน่ใจว่าต้องการลบกลุ่ม PLC นี้และไฟล์ทั้งหมดที่เกี่ยวข้องใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้',
                                confirmButtonText: 'ลบข้อมูล',
                                cancelButtonText: 'ยกเลิก',
                                type: 'danger',
                                onConfirm: () => {
                                    this.deleteGroup(groupId);
                                }
                            });
                        },

                        deleteGroup(groupId) {
                            this.deleting = true;
                            axios.delete(`{{ url('/plc') }}/${groupId}`, {
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                            })
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast(res.data.message, 'success');
                                        if (this.currentGroup && this.currentGroup.id === groupId) {
                                            this.currentGroup = null;
                                        }
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'ไม่สามารถลบกลุ่มได้', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast('เกิดข้อผิดพลาดในการลบกลุ่ม', 'error');
                                })
                                .finally(() => {
                                    this.deleting = false;
                                });
                        },

                        saveStepData(step, status = 0) {
                            this.saving = true;
                            
                            const payload = {
                                step_id: step.id,
                                status: status
                            };

                            if (step.sequence == 1) {
                                payload.step1_problem_statement = step.step1_problem_statement;
                                payload.step1_root_cause = step.step1_root_cause;
                                payload.step1_goal_kpi = step.step1_goal_kpi;
                                payload.step1_timeline_step2 = step.step1_timeline_step2;
                                payload.step1_timeline_step3 = step.step1_timeline_step3;
                                payload.step1_timeline_step4 = step.step1_timeline_step4;
                                payload.step1_timeline_step5 = step.step1_timeline_step5;
                                payload.step1_timeline_step6 = step.step1_timeline_step6;
                            } else if (step.sequence == 2) {
                                payload.step2_unit_name = step.step2_unit_name;
                                payload.step2_grade_subject = step.step2_grade_subject;
                                payload.step2_learning_objectives = step.step2_learning_objectives;
                                payload.step2_innovation = step.step2_innovation;
                            } else if (step.sequence == 3) {
                                payload.step3_change_log = step.step3_change_log;
                                payload.step3_ready_status = step.step3_ready_status ? 1 : 0;
                            } else if (step.sequence == 4) {
                                payload.step4_class_date = step.step4_class_date;
                                payload.step4_period = step.step4_period;
                                payload.step4_room = step.step4_room;
                            } else if (step.sequence == 5) {
                                payload.step5_self_reflection = step.step5_self_reflection;
                                payload.step5_total_students = step.step5_total_students;
                                payload.step5_passed_students = step.step5_passed_students;
                                payload.step5_qualitative_result = step.step5_qualitative_result;
                            } else if (step.sequence == 6) {
                                payload.step6_best_practice = step.step6_best_practice;
                                payload.step6_visibility = step.step6_visibility;
                            }

                            axios.post('{{ route("plc.steps.save") }}', payload)
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast(res.data.message, 'success');
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'ไม่สามารถบันทึกขั้นตอนนี้ได้', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast(err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการบันทึกข้อมูลขั้นตอน', 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        uploadFiles(e, step, fieldName) {
                            const files = e.target.files;
                            if (!files.length) return;

                            const formData = new FormData();
                            formData.append('step_id', step.id);
                            formData.append('file_field', fieldName);
                            for (let i = 0; i < files.length; i++) {
                                formData.append('files[]', files[i]);
                            }

                            this.saving = true;
                            axios.post('{{ route("plc.steps.upload") }}', formData, {
                                headers: { 'Content-Type': 'multipart/form-data' }
                            })
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast(res.data.message, 'success');
                                        e.target.value = '';
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'อัปโหลดไฟล์ไม่สำเร็จ', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast(err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์', 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        deleteFile(step, filePath, fieldName) {
                            this.showConfirm({
                                title: 'ยืนยันการลบไฟล์?',
                                text: 'คุณแน่ใจว่าต้องการลบไฟล์นี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้',
                                confirmButtonText: 'ลบไฟล์',
                                cancelButtonText: 'ยกเลิก',
                                type: 'danger',
                                onConfirm: () => {
                                    this.saving = true;
                                    axios.post('{{ route("plc.steps.delete_file") }}', {
                                        step_id: step.id,
                                        file_path: filePath,
                                        file_field: fieldName
                                    })
                                        .then(res => {
                                            if (res.data.status === 'success') {
                                                this.showToast(res.data.message, 'success');
                                                this.fetchData();
                                            } else {
                                                this.showToast(res.data.message || 'ลบไฟล์ไม่สำเร็จ', 'error');
                                            }
                                        })
                                        .catch(err => {
                                            this.showToast(err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการลบไฟล์', 'error');
                                        })
                                        .finally(() => {
                                            this.saving = false;
                                        });
                                }
                            });
                        },

                        submitComment(step, type) {
                            const commentPayload = {
                                step_id: step.id,
                                type: type
                            };

                            if (type === 'step2_idea_sharing' || type === 'step3_supervision_notes') {
                                if (!this.commentForm.comment) return;
                                commentPayload.comment = this.commentForm.comment;
                            } else if (type === 'step4_observations') {
                                if (!this.commentForm.learning_behavior && !this.commentForm.problems && !this.commentForm.response) return;
                                commentPayload.learning_behavior = this.commentForm.learning_behavior;
                                commentPayload.problems = this.commentForm.problems;
                                commentPayload.response = this.commentForm.response;
                            } else if (type === 'step5_peer_reflections') {
                                if (!this.commentForm.strengths && !this.commentForm.suggestions) return;
                                commentPayload.strengths = this.commentForm.strengths;
                                commentPayload.suggestions = this.commentForm.suggestions;
                            }

                            this.saving = true;
                            axios.post('{{ route("plc.steps.comment") }}', commentPayload)
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast(res.data.message, 'success');
                                        this.commentForm = {
                                            comment: '',
                                            learning_behavior: '',
                                            problems: '',
                                            response: '',
                                            strengths: '',
                                            suggestions: ''
                                        };
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'บันทึกความคิดเห็นไม่สำเร็จ', 'error');
                                    }
                                })
                                .catch(err => {
                                    const msg = err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการบันทึกความคิดเห็น';
                                    this.showToast(msg, 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        submitReview(step) {
                            this.saving = true;
                            axios.post('{{ route("plc.steps.save") }}', {
                                step_id: step.id,
                                status: this.reviewForm.status,
                                admin_comment: this.reviewForm.admin_comment
                            })
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.showToast('บันทึกการประเมินสำเร็จ', 'success');
                                        this.fetchData();
                                    } else {
                                        this.showToast(res.data.message || 'บันทึกการประเมินไม่สำเร็จ', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast('เกิดข้อผิดพลาดในการบันทึกการประเมิน', 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        getMemberAvatarUrl(m) {
                            if (m.user && m.user.teacher_profile) {
                                const tp = m.user.teacher_profile;
                                if (tp.profile_image_path) {
                                    return '{{ asset("storage") }}/' + tp.profile_image_path;
                                }
                                if (tp.profile_image_url) {
                                    return tp.profile_image_url;
                                }
                            }
                            const name = m.user ? m.user.name : 'U';
                            return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff&bold=true`;
                        },

                        getCreatorAvatarUrl(group) {
                            if (group.creator && group.creator.teacher_profile) {
                                const tp = group.creator.teacher_profile;
                                if (tp.profile_image_path) {
                                    return '{{ asset("storage") }}/' + tp.profile_image_path;
                                }
                                if (tp.profile_image_url) {
                                    return tp.profile_image_url;
                                }
                            }
                            const name = group.creator ? group.creator.name : 'C';
                            return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff&bold=true`;
                        },

                        viewTeacherDetail(userId) {
                            if (!userId) return;
                            this.saving = true;
                            axios.get(`{{ url('/plc/teacher') }}/${userId}`)
                                .then(res => {
                                    if (res.data.status === 'success') {
                                        this.modal.data = res.data.data;
                                        this.modal.activeTab = 'general';
                                        this.modal.open = true;
                                    } else {
                                        this.showToast(res.data.message || 'ไม่สามารถดึงข้อมูลประวัติได้', 'error');
                                    }
                                })
                                .catch(err => {
                                    this.showToast('เกิดข้อผิดพลาดในการดึงข้อมูลประวัติ', 'error');
                                })
                                .finally(() => {
                                    this.saving = false;
                                });
                        },

                        formatThaiDate(dateStr, yearBe = null) {
                            if (!dateStr) return '-';
                            try {
                                const d = new Date(dateStr);
                                if (isNaN(d.getTime())) return dateStr;
                                const months = [
                                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                                ];
                                const day = d.getDate();
                                const month = months[d.getMonth()];
                                const year = yearBe || (d.getFullYear() + 543);
                                return `${day} ${month} ${year}`;
                            } catch (e) {
                                return dateStr;
                            }
                        },

                        getCurrentUserRole() {
                            if (!this.currentGroup || !this.currentUser) return '';
                            if (this.currentUser.role === 'admin') return 'admin';
                            if (this.currentGroup.creator_user_id == this.currentUser.id) return 'ครูต้นแบบ';
                            const member = this.currentGroup.members.find(m => m.user_id == this.currentUser.id);
                            return member ? member.role : '';
                        },

                        isModelTeacher() {
                            const role = this.getCurrentUserRole();
                            return role === 'admin' || role === 'ครูต้นแบบ';
                        },

                        isMember() {
                            if (this.currentUser.role === 'admin') return true;
                            if (!this.currentGroup) return false;
                            if (this.currentGroup.creator_user_id == this.currentUser.id) return true;
                            return this.currentGroup.members.some(m => m.user_id == this.currentUser.id);
                        },

                        calculatePassPercent(passed, total) {
                            if (!total) return 0;
                            return ((passed / total) * 100).toFixed(1);
                        }
                    };
                }
    </script>
    @endpush
</x-layout>
