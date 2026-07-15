<x-layout>
    <x-slot:title>ตั้งค่าระบบเว็บไซต์ | BigData สพป.ชพ.1</x-slot>

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
                        :class="activeTab === 'identity' ? 'bg-white text-orange-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-id-card"></i> อัตลักษณ์เว็บไซต์
                </button>
                <button type="button" 
                        @click="activeTab = 'contact'" 
                        :class="activeTab === 'contact' ? 'bg-white text-orange-600 border border-slate-200/60 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-address-book"></i> ข้อมูลติดต่อ
                </button>

            </div>

            <!-- Configuration Form -->
            <form @submit.prevent="saveSettings()" class="p-8 md:p-10 space-y-8">
                <!-- Tab 1: Identity -->
                <div x-show="activeTab === 'identity'" class="space-y-8" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-50 pb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-image text-orange-600"></i> ตราสัญลักษณ์และชื่อเว็บไซต์
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
                                        <i class="fa-solid fa-graduation-cap text-3xl text-orange-500 mb-1"></i>
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
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" 
                               placeholder="เช่น BigData สพป.ชพ.1 หรือ ฐานข้อมูล BigData สพป.ชพ. 1">
                    </div>

                    <!-- Web Subtitle Field -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คำบรรยายใต้ชื่อเว็บ / ข้อมูลแถวที่ 2 (Website Subtitle)</label>
                        <input type="text" 
                               x-model="settings.web_subtitle" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" 
                               placeholder="เช่น ฐานข้อมูล BigData สพป.ชพ. 1">
                    </div>

                    <!-- Area Code Field -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เลขเขตพื้นที่ (Area Code)</label>
                        <input type="text"
                               x-model="settings.area_code"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="20"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition"
                               placeholder="เช่น {{ config('services.obec_safety.area_code', '1086010000') }}">
                        <p class="text-[11px] text-slate-400">ใช้สำหรับอ้างอิงข้อมูลเขตพื้นที่จากระบบภายนอก เช่น HRMS/OBEC</p>
                    </div>
                </div>

                <!-- Tab 2: Contact Info -->
                <div x-show="activeTab === 'contact'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-b border-slate-50 pb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-orange-600"></i> ข้อมูลการติดต่อศูนย์วิชาการ
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">ตั้งค่าเบอร์โทรศัพท์ อีเมลการสื่อสาร และที่อยู่เพื่อแสดงผลที่ส่วนท้ายของเว็บเพจ</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">อีเมลติดต่อ (Contact Email)</label>
                            <input type="email" 
                                   x-model="settings.contact_email" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" 
                                   placeholder="เช่น info@anubanchumphon.ac.th">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เบอร์โทรศัพท์ (Contact Phone)</label>
                            <input type="text" 
                                   x-model="settings.contact_phone" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" 
                                   placeholder="เช่น 077-511124">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ที่ตั้งสำนักงาน (Contact Address)</label>
                        <textarea x-model="settings.contact_address" 
                                  rows="4" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" 
                                  placeholder="กรอกรายละเอียดที่ตั้งของหน่วยงาน..."></textarea>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div x-show="activeTab !== 'slides'" class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                    <button type="submit" 
                            :disabled="saving"
                            class="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-md shadow-orange-100 flex items-center gap-2">
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
                            <i class="fa-solid fa-crop text-orange-500"></i> <span x-text="croppingTarget === 'slide' ? 'ครอบตัดรูปภาพสไลด์หน้าแรก' : 'ครอบตัดตราสัญลักษณ์หลัก'"></span>
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
                            <button type="button" @click="changeAspect(null)" :class="aspectRatio === null ? 'bg-orange-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                สัดส่วนอิสระ
                            </button>
                            <button type="button" @click="changeAspect(1)" :class="aspectRatio === 1 ? 'bg-orange-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                1:1 (จัตุรัส)
                            </button>
                            <button type="button" @click="changeAspect(3/1)" :class="aspectRatio === 3/1 ? 'bg-orange-500 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'" class="px-3 py-1.5 rounded-lg font-bold transition">
                                3:1 (แนวนอน)
                            </button>

                        </div>
                        
                        <div class="flex gap-2">
                            <button type="button" class="p-2 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition" title="หมุนขวา" @click="cropper.rotate(90)">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </button>
                            <button type="button" class="p-2 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition" title="ซูมออก" @click="cropper.zoom(-0.1)">
                                <i class="fa-solid fa-magnifying-glass-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                            ยกเลิก
                        </button>
                        <button type="button" @click="cropImage()" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl font-bold text-xs hover:bg-orange-700 transition shadow-lg shadow-orange-100">
                            ตกลงการครอบตัด
                        </button>
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
                    area_code: '',
                    contact_email: '',
                    contact_phone: '',
                    contact_address: '',

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



                init() {
                    this.fetchSettings();

                },

                fetchSettings() {
                    this.loading = true;
                    axios.get('{{ route('admin.settings.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                const data = response.data.data;
                                this.settings.web_name = data.web_name || 'BigData สพป.ชพ.1';
                                this.settings.web_subtitle = data.web_subtitle || 'ฐานข้อมูล BigData สพป.ชพ. 1';
                                this.settings.area_code = data.area_code || @js(config('services.obec_safety.area_code', '1086010000'));
                                this.settings.contact_email = data.contact_email || 'info@anubanchumphon.ac.th';
                                this.settings.contact_phone = data.contact_phone || '077-511124';
                                this.settings.contact_address = data.contact_address || 'โรงเรียนอนุบาลชุมพร ถนนปรมินทรมรรคา ตำบลท่าตะเภา อำเภอเมืองชุมพร จังหวัดชุมพร 86000';

                                
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
                                    if (response.data.web_name === 'BigData สพป.ชพ.1') {
                                        navTitle.innerHTML = 'EE<span class="text-orange-500">.</span>CPN1';
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
                                    if (response.data.web_name === 'BigData สพป.ชพ.1') {
                                        footerTitle.innerHTML = 'EE<span class="text-orange-400">.</span>CPN1';
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
                    
                    this.webLogoData = croppedBase64;
                    this.previewUrl = croppedBase64;
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
