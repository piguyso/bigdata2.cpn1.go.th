<x-layout>
    <x-slot:title>ตั้งค่าระบบเว็บไซต์ | EE CPN1</x-slot>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <div class="py-12 max-w-4xl mx-auto px-6" x-data="settingsForm()" x-init="init()">
        <!-- Toast Notification (Floating Glassmorphic) -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl backdrop-blur-md border"
             :class="toast.type === 'success' ? 'bg-emerald-500/95 border-emerald-400 text-white shadow-emerald-500/10' : 'bg-rose-500/95 border-rose-400 text-white shadow-rose-500/10'"
             x-cloak>
            <template x-if="toast.type === 'success'">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </template>
            <template x-if="toast.type === 'error'">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </template>
            <span class="text-xs font-bold" x-text="toast.message"></span>
        </div>

        <header class="mb-10 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">ตั้งค่าระบบเว็บไซต์</h2>
                <p class="text-slate-500 text-sm mt-1">ส่วนจัดการข้อมูลทั่วไปและตราสัญลักษณ์หลักสำหรับผู้ดูแลระบบ</p>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                ← กลับแดชบอร์ด
            </a>
        </header>

        <!-- Loading Skeleton State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-8 md:p-10 shadow-sm space-y-6" x-transition>
            <div class="h-6 bg-slate-100 rounded w-1/3 animate-pulse"></div>
            <div class="space-y-3">
                <div class="h-4 bg-slate-100 rounded w-3/4 animate-pulse"></div>
                <div class="h-4 bg-slate-100 rounded w-5/6 animate-pulse"></div>
                <div class="h-4 bg-slate-100 rounded w-1/2 animate-pulse"></div>
            </div>
            <div class="pt-6 flex justify-end">
                <div class="h-10 bg-slate-100 rounded w-24 animate-pulse"></div>
            </div>
        </div>

        <!-- Main Configuration Panel (Axios + Alpine.js) -->
        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col" x-cloak x-transition>
            <!-- Tab Controls -->
            <div class="flex border-b border-slate-100 bg-slate-50/50 p-2 gap-1">
                <button type="button" 
                        @click="activeTab = 'identity'" 
                        :class="activeTab === 'identity' ? 'bg-white text-emerald-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-id-card"></i> อัตลักษณ์เว็บไซต์
                </button>
                <button type="button" 
                        @click="activeTab = 'contact'" 
                        :class="activeTab === 'contact' ? 'bg-white text-emerald-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-address-book"></i> ข้อมูลติดต่อ
                </button>
                <button type="button" 
                        @click="activeTab = 'statistics'" 
                        :class="activeTab === 'statistics' ? 'bg-white text-emerald-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-chart-simple"></i> สถิติหน้าเว็บ
                </button>
                <button type="button" 
                        @click="activeTab = 'slides'" 
                        :class="activeTab === 'slides' ? 'bg-white text-emerald-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-images"></i> สไลด์หน้าแรก
                </button>
            </div>

            <!-- Configuration Form -->
            <form @submit.prevent="saveSettings()" class="p-8 md:p-10 space-y-8">
                <!-- Tab 1: Identity -->
                <div x-show="activeTab === 'identity'" class="space-y-8" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-50 pb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-image text-emerald-600"></i> ตราสัญลักษณ์และชื่อเว็บไซต์
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">อัปโหลดรูปภาพตราสัญลักษณ์หลักและตั้งค่าชื่อของระบบ</p>
                    </div>

                    <!-- Logo Selector/Cropper Interface -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-8">
                        <div class="relative shrink-0">
                            <div class="w-32 h-32 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center p-2 bg-slate-50 overflow-hidden relative">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Logo Preview" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="text-center text-slate-400 p-2">
                                        <i class="fa-solid fa-graduation-cap text-3xl text-emerald-500 mb-1"></i>
                                        <p class="text-[9px] font-bold uppercase tracking-wider">โลโก้เริ่มต้น</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="font-bold text-sm text-slate-700">เปลี่ยนรูปตราสัญลักษณ์</h4>
                            <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                                รูปภาพควรมีพื้นหลังโปร่งใส (PNG) หรือขนาดสัดส่วนที่เหมาะสม (รองรับการครอบตัดแบบสัดส่วนอิสระ, 1:1, หรือ 3:1)
                            </p>
                            <div class="flex items-center gap-3 pt-1">
                                <button type="button" @click="$refs.fileInput.click()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-800 transition shadow-sm">
                                    เลือกไฟล์รูปภาพ...
                                </button>
                                <template x-if="previewUrl || webLogoData">
                                    <button type="button" @click="resetPreview()" class="text-xs font-bold text-rose-500 hover:bg-rose-50 px-3 py-2 rounded-xl transition">
                                        รีเซ็ตภาพ
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input to select file -->
                    <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="fileSelected($event)">

                    <!-- Web Name Field -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อเว็บไซต์ (Website Name)</label>
                        <input type="text" 
                               x-model="settings.web_name" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                               placeholder="เช่น EE.CPN1 หรือ ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1">
                    </div>

                    <!-- Web Subtitle Field -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คำบรรยายใต้ชื่อเว็บ / ข้อมูลแถวที่ 2 (Website Subtitle)</label>
                        <input type="text" 
                               x-model="settings.web_subtitle" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                               placeholder="เช่น ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1">
                    </div>
                </div>

                <!-- Tab 2: Contact Info -->
                <div x-show="activeTab === 'contact'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-50 pb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-emerald-600"></i> ข้อมูลการติดต่อศูนย์วิชาการ
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">ตั้งค่าเบอร์โทรศัพท์ อีเมลการสื่อสาร และที่อยู่เพื่อแสดงผลที่ส่วนท้ายของเว็บเพจ</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">อีเมลติดต่อ (Contact Email)</label>
                            <input type="email" 
                                   x-model="settings.contact_email" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น info@anubanchumphon.ac.th">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เบอร์โทรศัพท์ (Contact Phone)</label>
                            <input type="text" 
                                   x-model="settings.contact_phone" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น 077-511124">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ที่ตั้งสำนักงาน (Contact Address)</label>
                        <textarea x-model="settings.contact_address" 
                                  rows="4" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                  placeholder="กรอกรายละเอียดที่ตั้งของหน่วยงาน..."></textarea>
                    </div>
                </div>

                <!-- Tab 3: Statistics -->
                <div x-show="activeTab === 'statistics'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-50 pb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-emerald-600"></i> สถิติผลงานหน้าหลักเว็บไซต์
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">ข้อมูลตัวเลขสถิติที่จะแสดงผลความสำเร็จและเครือข่ายของศูนย์ฯ</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ครูผ่านการอบรม (Trained Teachers)</label>
                            <input type="text" 
                                   x-model="settings.stat_teachers" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น 1,200+">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เครือข่ายสถานศึกษา (Network Schools)</label>
                            <input type="text" 
                                   x-model="settings.stat_schools" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น 50+">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">อำเภอครอบคลุม (Covered Districts)</label>
                            <input type="text" 
                                   x-model="settings.stat_districts" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น 8+">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">หลักสูตรจัดอบรม (Academic Courses)</label>
                            <input type="text" 
                                   x-model="settings.stat_courses" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น 15+">
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Hero Slides -->
                <div x-show="activeTab === 'slides'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-100 pb-4 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-images text-emerald-600"></i> สไลด์หน้าแรก (Hero Carousel)
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">จัดการรูปภาพสไลด์ แบนเนอร์ขนาดใหญ่ และข้อความโปรยในหน้าแรก</p>
                        </div>
                        <button type="button" @click="openSlideModal()" class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> เพิ่มสไลด์ใหม่
                        </button>
                    </div>

                    <!-- Slide Interval Setting Card -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100/60 border border-slate-200/70 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left text-emerald-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold text-slate-700">ระยะเวลาการเปลี่ยนสไลด์อัตโนมัติ</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">กำหนดว่าสไลด์จะเปลี่ยนหน้าทุกกี่วินาที (Auto-advance interval)</p>
                            </div>
                        </div>

                        <!-- Preset Buttons -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mr-1">เลือกด่วน:</span>
                            <template x-for="preset in [3, 5, 7, 10, 15]" :key="preset">
                                <button type="button"
                                        @click="settings.slide_interval = preset"
                                        :class="settings.slide_interval == preset
                                            ? 'bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-100'
                                            : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:text-emerald-600'"
                                        class="px-3.5 py-1.5 rounded-xl border font-bold text-xs transition-all duration-200 active:scale-95"
                                        x-text="preset + ' วิ'">
                                </button>
                            </template>
                        </div>

                        <!-- Custom Input + Save Row -->
                        <div class="flex items-center gap-3">
                            <div class="relative flex items-center">
                                <input type="number"
                                       x-model.number="settings.slide_interval"
                                       min="2" max="60"
                                       class="w-24 bg-white border border-slate-200 rounded-xl pl-4 pr-8 py-2.5 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition text-center">
                                <span class="absolute right-3 text-[10px] font-bold text-slate-400 pointer-events-none">วิ</span>
                            </div>
                            <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-300"
                                     :style="'width: ' + Math.min(Math.max((settings.slide_interval / 60) * 100, 3), 100) + '%'"></div>
                            </div>
                            <button type="button"
                                    @click="saveSettings()"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-xl font-bold text-xs transition shadow-sm active:scale-95">
                                <template x-if="saving">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                </template>
                                <template x-if="!saving">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                </template>
                                <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึก'"></span>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400"><i class="fa-solid fa-circle-info mr-1"></i>ค่าที่แนะนำ: 5–10 วินาที · ช่วง: 2–60 วินาที</p>
                    </div>

                    <!-- List of Slides -->
                    <div class="space-y-4">
                        <template x-for="(slide, index) in slides" :key="slide.id">
                            <div class="border border-slate-100 rounded-2xl p-4 flex flex-col md:flex-row gap-4 bg-slate-50/50 hover:bg-white transition hover:shadow-sm duration-200">
                                <!-- Slide Image Preview -->
                                <div class="w-full md:w-48 h-28 rounded-xl overflow-hidden bg-slate-200 shrink-0 relative border border-slate-100">
                                    <img :src="slide.image_url" alt="" class="w-full h-full object-cover">
                                    <div class="absolute top-2 left-2 bg-slate-900/70 text-white px-2 py-0.5 rounded text-[10px] font-bold" x-text="'ลำดับที่ ' + (index + 1)"></div>
                                </div>

                                <!-- Slide Text Details -->
                                <div class="flex-1 min-w-0 space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-[10px] font-bold" x-text="slide.badge || 'ไม่มีป้าย'"></span>
                                        <span class="text-xs text-slate-400" x-text="'Link: ' + (slide.link || '-')"></span>
                                    </div>
                                    <h4 class="font-extrabold text-sm text-slate-800 truncate" x-text="slide.title"></h4>
                                    <h5 class="text-xs font-bold text-emerald-600 truncate" x-text="slide.highlight || ''"></h5>
                                    <p class="text-slate-450 text-[11px] line-clamp-2 leading-relaxed" x-text="slide.slogan || '-'"></p>
                                </div>

                                <!-- Actions (Edit / Delete / Move Order) -->
                                <div class="flex md:flex-col items-center justify-end md:justify-center gap-2 shrink-0 border-t md:border-t-0 md:border-l border-slate-150/40 pt-3 md:pt-0 md:pl-4">
                                    <!-- Sort Order Buttons -->
                                    <div class="flex gap-1">
                                        <button type="button" 
                                                @click="moveOrder(index, 'up')" 
                                                :disabled="index === 0"
                                                class="w-8 h-8 rounded-lg bg-white border border-slate-100 text-slate-500 hover:text-emerald-600 hover:border-emerald-100 disabled:opacity-30 disabled:cursor-not-allowed transition flex items-center justify-center">
                                            <i class="fa-solid fa-arrow-up text-xs"></i>
                                        </button>
                                        <button type="button" 
                                                @click="moveOrder(index, 'down')" 
                                                :disabled="index === slides.length - 1"
                                                class="w-8 h-8 rounded-lg bg-white border border-slate-100 text-slate-500 hover:text-emerald-600 hover:border-emerald-100 disabled:opacity-30 disabled:cursor-not-allowed transition flex items-center justify-center">
                                            <i class="fa-solid fa-arrow-down text-xs"></i>
                                        </button>
                                    </div>
                                    <div class="flex gap-1 mt-0 md:mt-2">
                                        <button type="button" @click="editSlide(slide)" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-emerald-600 hover:border-emerald-100 font-bold text-xs transition">
                                            แก้ไข
                                        </button>
                                        <button type="button" @click="deleteSlide(slide.id)" class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-100 hover:text-rose-700 font-bold text-xs transition">
                                            ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <template x-if="slides.length === 0">
                            <div class="text-center py-12 bg-slate-50 rounded-2xl border border-slate-100 border-dashed">
                                <i class="fa-solid fa-images text-slate-300 text-4xl mb-3"></i>
                                <p class="text-xs text-slate-500 font-bold">ยังไม่มีข้อมูลรูปภาพสไลด์หน้าแรก</p>
                                <p class="text-[10px] text-slate-400 mt-1">คลิก "เพิ่มสไลด์ใหม่" เพื่อเพิ่มแบนเนอร์ภาพแรก</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div x-show="activeTab !== 'slides'" class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                    <button type="submit" 
                            :disabled="saving"
                            class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-md shadow-emerald-100 flex items-center gap-2">
                        <template x-if="saving">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                        </template>
                        <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกการตั้งค่าทั้งหมด'"></span>
                    </button>
                </div>
            </form>

            <!-- Cropper Modal (Glassmorphic) -->
            <div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
                <div class="bg-white rounded-[2rem] max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] md:max-h-[85vh]">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-crop text-emerald-500"></i> <span x-text="croppingTarget === 'slide' ? 'ครอบตัดรูปภาพสไลด์หน้าแรก' : 'ครอบตัดตราสัญลักษณ์หลัก'"></span>
                        </h3>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-650 transition">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="p-4 sm:p-8 bg-slate-100 flex justify-center items-center overflow-hidden h-48 sm:h-64 md:h-80 shrink-0">
                        <img id="webCropperImage" class="max-w-full max-h-full block">
                    </div>
                    
                    <!-- Cropper Controls -->
                    <div class="px-6 py-3 border-t border-slate-100 flex flex-wrap justify-between items-center gap-4 text-slate-500 bg-white shrink-0">
                        <div class="flex gap-1 text-xs">
                            <button type="button" @click="changeAspect(null)" :class="aspectRatio === null ? 'bg-emerald-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                สัดส่วนอิสระ
                            </button>
                            <button type="button" @click="changeAspect(1)" :class="aspectRatio === 1 ? 'bg-emerald-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                1:1 (จัตุรัส)
                            </button>
                            <button type="button" @click="changeAspect(3/1)" :class="aspectRatio === 3/1 ? 'bg-emerald-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                3:1 (แนวนอน)
                            </button>
                            <button type="button" x-show="croppingTarget === 'slide'" @click="changeAspect(16/7)" :class="aspectRatio === 16/7 ? 'bg-emerald-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                16:7 (สไลด์แนวกว้าง)
                            </button>
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="หมุนขวา" @click="cropper.rotate(90)">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="ซูมออก" @click="cropper.zoom(-0.1)">
                                <i class="fa-solid fa-magnifying-glass-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                            ยกเลิก
                        </button>
                        <button type="button" @click="cropImage()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">
                            ตกลงการครอบตัด
                        </button>
                    </div>
                </div>
            </div>

            <!-- Slide Editor Modal -->
            <div x-show="slideModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto" x-transition x-cloak>
                <div class="bg-white rounded-[2rem] max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col my-8 max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-images text-emerald-500"></i>
                            <span x-text="slideModal.form.id ? 'แก้ไขสไลด์หน้าแรก' : 'เพิ่มสไลด์หน้าแรกใหม่'"></span>
                        </h3>
                        <button type="button" @click="slideModal.open = false" class="text-slate-400 hover:text-slate-650 transition">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable Form) -->
                    <form @submit.prevent="saveSlide()" class="overflow-y-auto flex-1 p-6 md:p-8 space-y-6">
                        <!-- Image Selector & Preview -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">รูปภาพแบนเนอร์ (สัดส่วน 16:7 แนะนำ)</label>
                            
                            <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                                <div class="w-full sm:w-56 h-32 bg-slate-50 border border-slate-200 rounded-xl overflow-hidden relative shrink-0 flex items-center justify-center border-dashed">
                                    <template x-if="slideModal.preview">
                                        <img :src="slideModal.preview" alt="" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!slideModal.preview">
                                        <div class="text-center text-slate-400 p-2">
                                            <i class="fa-solid fa-image text-2xl mb-1 text-slate-300"></i>
                                            <p class="text-[9px] font-bold uppercase tracking-wider">ยังไม่ได้เลือกรูปภาพ</p>
                                        </div>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <button type="button" @click="$refs.slideFileInput.click()" class="bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-800 transition shadow-sm">
                                        เลือกรูปภาพสไลด์...
                                    </button>
                                    <p class="text-[10px] text-slate-400 leading-relaxed max-w-sm">
                                        แนะนำขนาด 1920x840 พิกเซล หรือสัดส่วนประมาณ 16:7 ระบบจะเปิดหน้าต่างสำหรับครอบตัดภาพให้เหมาะสม
                                    </p>
                                </div>
                            </div>
                            <!-- Hidden input -->
                            <input type="file" x-ref="slideFileInput" class="hidden" accept="image/*" @change="slideFileSelected($event)">
                        </div>

                        <!-- Badge & Link Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ข้อความกำกับ/ป้าย (Badge Tag)</label>
                                <input type="text" x-model="slideModal.form.badge" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น ศูนย์พัฒนาครู หรือ ข่าวสาร">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ลิงก์ของปุ่มแรก (Button 1 Link)</label>
                                <input type="text" x-model="slideModal.form.link" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น #courses หรือ /documents">
                            </div>
                        </div>

                        <!-- Title and Highlight Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">หัวข้อหลักสไลด์ (Slide Title) *</label>
                                <input type="text" x-model="slideModal.form.title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น พัฒนาห้องเรียนแห่งอนาคต">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ข้อความเน้นย้ำ (Highlight Text)</label>
                                <input type="text" x-model="slideModal.form.highlight" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น ด้วยทักษะดิจิทัลและ AI">
                            </div>
                        </div>

                        <!-- Slogan Textarea -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คำโปรยอธิบายเพิ่มเติม (Slogan/Intro)</label>
                            <textarea x-model="slideModal.form.slogan" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition resize-none leading-relaxed" placeholder="พิมพ์ข้อความคำอธิบายโปรยสไลด์ตรงนี้..."></textarea>
                        </div>

                        <!-- Button 1 Text, Button 2 Text, Button 2 Link -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อปุ่มแรก (Btn 1 Text)</label>
                                <input type="text" x-model="slideModal.form.btn_text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น สำรวจคอร์สฝึกอบรม">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อปุ่มที่สอง (Btn 2 Text)</label>
                                <input type="text" x-model="slideModal.form.btn2_text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น โรงเรียนในเครือข่าย">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ลิงก์ของปุ่มที่สอง (Btn 2 Link)</label>
                                <input type="text" x-model="slideModal.form.btn2_link" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="เช่น #schools หรือ /org">
                            </div>
                        </div>

                        <!-- Modal Footer Buttons -->
                        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" @click="slideModal.open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                                ยกเลิก
                            </button>
                            <button type="submit" :disabled="slideModal.saving" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100">
                                <span x-text="slideModal.saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูลสไลด์'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function settingsForm() {
            return {
                activeTab: 'identity',
                loading: true,
                saving: false,
                settings: {
                    web_name: '',
                    web_subtitle: '',
                    contact_email: '',
                    contact_phone: '',
                    contact_address: '',
                    stat_teachers: '',
                    stat_schools: '',
                    stat_districts: '',
                    stat_courses: '',
                    slide_interval: 7,
                },
                previewUrl: null,
                webLogoData: '',
                showModal: false,
                cropper: null,
                aspectRatio: null,
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                // Slides management state variables
                slides: [],
                croppingTarget: 'logo',
                slideModal: {
                    open: false,
                    saving: false,
                    preview: null,
                    form: {
                        id: null,
                        title: '',
                        highlight: '',
                        slogan: '',
                        badge: '',
                        link: '',
                        btn_text: '',
                        btn2_text: '',
                        btn2_link: '',
                        image_data: ''
                    }
                },

                init() {
                    this.fetchSettings();
                    this.fetchSlides();
                },

                fetchSettings() {
                    this.loading = true;
                    axios.get('{{ route('admin.settings.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                const data = response.data.data;
                                this.settings.web_name = data.web_name || 'EE.CPN1';
                                this.settings.web_subtitle = data.web_subtitle || 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1';
                                this.settings.contact_email = data.contact_email || 'info@anubanchumphon.ac.th';
                                this.settings.contact_phone = data.contact_phone || '077-511124';
                                this.settings.contact_address = data.contact_address || 'โรงเรียนอนุบาลชุมพร ถนนปรมินทรมรรคา ตำบลท่าตะเภา อำเภอเมืองชุมพร จังหวัดชุมพร 86000';
                                this.settings.stat_teachers = data.stat_teachers || '1,200+';
                                this.settings.stat_schools = data.stat_schools || '50+';
                                this.settings.stat_districts = data.stat_districts || '8+';
                                this.settings.stat_courses = data.stat_courses || '15+';
                                this.settings.slide_interval = parseInt(data.slide_interval) || 7;
                                
                                if (data.web_logo) {
                                    this.previewUrl = '/storage/' + data.web_logo;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Settings Error:', error);
                            this.showToast('ไม่สามารถโหลดข้อมูลตั้งค่าได้', 'error');
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                saveSettings() {
                    this.saving = true;
                    const payload = {
                        ...this.settings,
                        web_logo_data: this.webLogoData
                    };

                    axios.post('{{ route('admin.settings.save') }}', payload)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                if (response.data.web_logo) {
                                    this.previewUrl = response.data.web_logo;
                                    // Update layout logo in real-time
                                    const navLogo = document.querySelector('nav img[alt="Logo"]');
                                    if (navLogo) navLogo.src = response.data.web_logo;
                                    const footerLogo = document.querySelector('footer img[alt="Logo"]');
                                    if (footerLogo) footerLogo.src = response.data.web_logo;
                                }
                                // Update layout web_name
                                const navTitle = document.querySelector('nav .brand-title');
                                if (navTitle && response.data.web_name) {
                                    if (response.data.web_name === 'EE.CPN1') {
                                        navTitle.innerHTML = 'EE<span class="text-emerald-500">.</span>CPN1';
                                    } else {
                                        navTitle.textContent = response.data.web_name;
                                    }
                                }
                                // Update layout web_subtitle
                                const navSubtitle = document.querySelector('nav .brand-subtitle');
                                if (navSubtitle && response.data.web_subtitle) {
                                    navSubtitle.textContent = response.data.web_subtitle;
                                }
                                const footerTitle = document.querySelector('footer span.tracking-tight');
                                if (footerTitle && response.data.web_name) {
                                    if (response.data.web_name === 'EE.CPN1') {
                                        footerTitle.innerHTML = 'EE<span class="text-emerald-400">.</span>CPN1';
                                    } else {
                                        footerTitle.textContent = response.data.web_name;
                                    }
                                }
                                this.webLogoData = ''; // Reset crop buffer
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Save Settings Error:', error);
                            const msg = error.response && error.response.data && error.response.data.message 
                                ? error.response.data.message 
                                : 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                            this.showToast(msg, 'error');
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                },

                showToast(message, type = 'success') {
                    this.toast.show = true;
                    this.toast.message = message;
                    this.toast.type = type;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                // Slides Management Methods
                fetchSlides() {
                    axios.get('{{ route('admin.slides.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.slides = response.data.data;
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Slides Error:', error);
                        });
                },

                openSlideModal() {
                    this.slideModal.form = {
                        id: null,
                        title: '',
                        highlight: '',
                        slogan: '',
                        badge: '',
                        link: '',
                        btn_text: '',
                        btn2_text: '',
                        btn2_link: '',
                        image_data: ''
                    };
                    this.slideModal.preview = null;
                    this.slideModal.open = true;
                },

                slideFileSelected(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.croppingTarget = 'slide';
                        this.aspectRatio = 16/7; // wide aspect ratio for slide image
                        this.showModal = true;
                        
                        const imageEl = document.getElementById('webCropperImage');
                        imageEl.src = e.target.result;
                        
                        setTimeout(() => {
                            this.initCropper(imageEl);
                        }, 100);
                    };
                    reader.readAsDataURL(file);
                },

                saveSlide() {
                    if (!this.slideModal.form.id && !this.slideModal.form.image_data) {
                        this.showToast('กรุณาอัปโหลดรูปภาพสำหรับสไลด์ใหม่', 'error');
                        return;
                    }

                    this.slideModal.saving = true;
                    
                    let url = '{{ route('admin.slides.save') }}';
                    if (this.slideModal.form.id) {
                        url = `/admin/slides/${this.slideModal.form.id}/save`;
                    }

                    axios.post(url, this.slideModal.form)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.slideModal.open = false;
                                this.fetchSlides();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูลสไลด์', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Save Slide Error:', error);
                            const msg = error.response && error.response.data && error.response.data.message 
                                ? error.response.data.message 
                                : 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                            this.showToast(msg, 'error');
                        })
                        .finally(() => {
                            this.slideModal.saving = false;
                        });
                },

                editSlide(slide) {
                    this.slideModal.form = {
                        id: slide.id,
                        title: slide.title,
                        highlight: slide.highlight || '',
                        slogan: slide.slogan || '',
                        badge: slide.badge || '',
                        link: slide.link || '',
                        btn_text: slide.btn_text || '',
                        btn2_text: slide.btn2_text || '',
                        btn2_link: slide.btn2_link || '',
                        image_data: ''
                    };
                    this.slideModal.preview = slide.image_url;
                    this.slideModal.open = true;
                },

                deleteSlide(id) {
                    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสไลด์นี้?')) return;
                    
                    axios.delete(`/admin/slides/${id}`)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchSlides();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาดในการลบสไลด์', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Delete Slide Error:', error);
                            this.showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                        });
                },

                moveOrder(index, direction) {
                    const targetIndex = direction === 'up' ? index - 1 : index + 1;
                    if (targetIndex < 0 || targetIndex >= this.slides.length) return;

                    // Swap items in memory
                    const temp = this.slides[index];
                    this.slides[index] = this.slides[targetIndex];
                    this.slides[targetIndex] = temp;

                    // Prepare order updates payload
                    const orders = this.slides.map((slide, idx) => {
                        return {
                            id: slide.id,
                            sort_order: idx + 1
                        };
                    });

                    axios.post('{{ route('admin.slides.order') }}', { orders })
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchSlides();
                            }
                        })
                        .catch(error => {
                            console.error('Move Order Error:', error);
                            this.showToast('ไม่สามารถบันทึกลำดับสไลด์ได้', 'error');
                        });
                },

                fileSelected(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.croppingTarget = 'logo';
                        this.aspectRatio = null;
                        this.showModal = true;
                        const imageEl = document.getElementById('webCropperImage');
                        imageEl.src = e.target.result;
                        
                        setTimeout(() => {
                            this.initCropper(imageEl);
                        }, 100);
                    };
                    reader.readAsDataURL(file);
                },

                initCropper(imageEl) {
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    this.cropper = new Cropper(imageEl, {
                        aspectRatio: this.aspectRatio,
                        viewMode: 1,
                        background: false,
                        responsive: true,
                        checkOrientation: false
                    });
                },

                changeAspect(ratio) {
                    this.aspectRatio = ratio;
                    if (this.cropper) {
                        this.cropper.setAspectRatio(ratio);
                    }
                },
                
                cropImage() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas();
                    const croppedBase64 = canvas.toDataURL('image/png');
                    
                    if (this.croppingTarget === 'slide') {
                        this.slideModal.form.image_data = croppedBase64;
                        this.slideModal.preview = croppedBase64;
                    } else {
                        this.webLogoData = croppedBase64;
                        this.previewUrl = croppedBase64;
                    }
                    this.closeModal();
                },

                resetPreview() {
                    this.previewUrl = null;
                    this.webLogoData = '';
                    this.$refs.fileInput.value = '';
                },
                
                closeModal() {
                    this.showModal = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    if (this.$refs.slideFileInput) this.$refs.slideFileInput.value = '';
                }
            };
        }
    </script>
    @endpush
</x-layout>

