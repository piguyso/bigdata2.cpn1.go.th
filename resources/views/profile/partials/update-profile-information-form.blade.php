<section class="space-y-6" x-data="profileForm()">
    <header class="border-b border-slate-100 pb-4 mb-6">
        <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-user-gear text-emerald-500"></i> ข้อมูลส่วนตัว
        </h3>
        <p class="text-xs text-slate-500 mt-1">
            แก้ไขข้อมูลส่วนตัว อีเมล และสัญลักษณ์ประจำตัวของคุณ
        </p>
    </header>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <!-- Toast notification -->
    <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
        class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
        x-cloak
    >
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- Logo Upload Section with Crop -->
    <div class="flex items-center gap-6 mb-8" x-data="logoUploader()" x-on:logo-cropped.window="$dispatch('set-logo', $event.detail)">
        <div class="relative group cursor-pointer shrink-0" @click="$refs.fileInput.click()">
            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-emerald-500/20 group-hover:border-emerald-500 transition-all duration-300 shadow-md flex items-center justify-center bg-slate-100 relative">
                <!-- Current Logo Image -->
                <template x-if="previewUrl">
                    <img :src="previewUrl" alt="Logo Preview" class="w-full h-full object-cover">
                </template>
                <template x-if="!previewUrl">
                    @if($user->logo)
                        <img src="{{ asset('storage/' . $user->logo) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <div class="text-3xl font-bold text-emerald-600 bg-emerald-50 w-full h-full flex items-center justify-center uppercase">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </template>

                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-slate-900/60 flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <i class="fa-solid fa-camera text-lg mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider">เปลี่ยนรูป</span>
                </div>
            </div>
        </div>

        <div>
            <h4 class="font-bold text-slate-800 text-sm">รูปสัญลักษณ์ประจำตัว</h4>
            <p class="text-[11px] text-slate-500 mt-1">แนะนำรูปภาพสี่เหลี่ยมจัตุรัส ขนาดขั้นต่ำ 200x200 พิกเซล (ระบบจะครอบตัดเป็น 1:1)</p>
            <button type="button" class="mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1" @click="$refs.fileInput.click()">
                <i class="fa-solid fa-image"></i> เลือกรูปภาพใหม่...
            </button>
        </div>

        <!-- Hidden Inputs -->
        <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="fileSelected($event)">

        <!-- Cropper Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-[2rem] max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-crop text-emerald-500"></i> ครอบตัดรูปภาพ
                    </h3>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 bg-slate-50 flex justify-center items-center overflow-hidden h-72">
                    <img id="cropperImage" class="max-w-full max-h-full block">
                </div>

                <!-- Cropper Controls -->
                <div class="px-6 py-3 border-t border-slate-100 flex justify-center gap-4 text-slate-500 bg-white">
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

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition">
                        ยกเลิก
                    </button>
                    <button type="button" @click="cropImage()" class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl font-bold text-sm hover:bg-emerald-600 transition shadow-lg shadow-emerald-100">
                        ตกลง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">ชื่อแสดงผล</label>
            <input id="name" name="name" type="text"
                   class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                   x-model="form.name"
                   required autofocus autocomplete="name" />
            <p class="mt-1.5 text-rose-500 text-[10px] font-bold" x-show="errors.name" x-text="errors.name" x-cloak></p>
        </div>

        <div>
            <label for="email" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">ที่อยู่อีเมล (Email)</label>
            <input id="email" name="email" type="email"
                   class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                   x-model="form.email"
                   required autocomplete="username" />
            <p class="mt-1.5 text-rose-500 text-[10px] font-bold" x-show="errors.email" x-text="errors.email" x-cloak></p>
        </div>
    </div>

    <div class="flex items-center gap-4 pt-4">
        <button type="button"
                @click="save()"
                :disabled="saving"
                class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 text-white font-bold text-xs py-3.5 px-7 rounded-2xl shadow-lg shadow-emerald-100 transition duration-250 cursor-pointer">
            <template x-if="saving">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
            </template>
            <template x-if="!saving">
                <i class="fa-solid fa-floppy-disk"></i>
            </template>
            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกการเปลี่ยนแปลง'"></span>
        </button>
    </div>

    <script>
    function logoUploader() {
        return {
            showModal: false,
            previewUrl: null,
            cropper: null,

            fileSelected(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.showModal = true;
                    const imageEl = document.getElementById('cropperImage');
                    imageEl.src = e.target.result;
                    setTimeout(() => {
                        if (this.cropper) this.cropper.destroy();
                        this.cropper = new Cropper(imageEl, {
                            aspectRatio: 1,
                            viewMode: 1,
                            background: false,
                            responsive: true,
                            checkOrientation: false
                        });
                    }, 100);
                };
                reader.readAsDataURL(file);
            },

            cropImage() {
                if (!this.cropper) return;
                const canvas = this.cropper.getCroppedCanvas({ width: 300, height: 300 });
                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                this.previewUrl = dataUrl;
                // Dispatch event so parent Alpine component can grab the logo_data
                this.$dispatch('logo-cropped', { logo_data: dataUrl });
                this.closeModal();
            },

            closeModal() {
                this.showModal = false;
                if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                this.$refs.fileInput.value = '';
            }
        };
    }

    function profileForm() {
        return {
            form: {
                name:      '{{ addslashes($user->name) }}',
                email:     '{{ addslashes($user->email) }}',
                logo_data: '',
            },
            saving: false,
            errors: {},
            toast: { show: false, message: '', type: 'success' },

            init() {
                // Listen for cropped logo from child component
                window.addEventListener('logo-cropped', (e) => {
                    this.form.logo_data = e.detail.logo_data;
                });
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            save() {
                this.saving = true;
                this.errors = {};

                axios.post('{{ route("api.profile.update") }}', this.form)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showToast(response.data.message, 'success');
                            this.form.logo_data = ''; // clear logo after save
                            // Update navbar avatar if logo changed
                            if (response.data.logo_url) {
                                document.querySelectorAll('[data-profile-avatar]').forEach(el => {
                                    el.src = response.data.logo_url;
                                });
                            }
                        } else {
                            this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                        }
                    })
                    .catch(error => {
                        if (error.response?.data?.errors) {
                            const errs = error.response.data.errors;
                            this.errors = {
                                name:  errs.name?.[0] || '',
                                email: errs.email?.[0] || '',
                            };
                        }
                        const msg = error.response?.data?.message ?? 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        this.showToast(msg, 'error');
                    })
                    .finally(() => { this.saving = false; });
            }
        };
    }
    </script>
</section>
