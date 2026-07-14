<section class="space-y-6" x-data="passwordForm()">
    <header class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-lock text-orange-500"></i> เปลี่ยนรหัสผ่าน
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                เปลี่ยนรหัสผ่านใหม่เพื่อความปลอดภัยในการเข้าใช้งานระบบ
            </p>
        </div>

        <!-- Toggle Button -->
        <button type="button"
                @click="formOpen = !formOpen"
                :class="formOpen
                    ? 'bg-slate-100 hover:bg-slate-200 text-slate-700'
                    : 'bg-orange-500 hover:bg-orange-600 text-white shadow-lg shadow-orange-100'"
                class="inline-flex items-center gap-2 font-bold text-xs py-3 px-6 rounded-2xl transition-all duration-200 active:scale-95 cursor-pointer">
            <template x-if="!formOpen">
                <i class="fa-solid fa-key"></i>
            </template>
            <template x-if="formOpen">
                <i class="fa-solid fa-xmark"></i>
            </template>
            <span x-text="formOpen ? 'ยกเลิก' : 'เปลี่ยนรหัสผ่าน'"></span>
        </button>
    </header>

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

    <!-- Collapsible Form -->
    <div
        x-show="formOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        x-cloak
        class="space-y-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="update_password_current_password" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">รหัสผ่านปัจจุบัน</label>
                <div class="relative">
                    <input id="update_password_current_password"
                           :type="showCurrent ? 'text' : 'password'"
                           class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 pr-10 text-xs font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                           x-model="form.current_password"
                           autocomplete="current-password" />
                    <button type="button" @click="showCurrent = !showCurrent"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <i :class="showCurrent ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                    </button>
                </div>
                <p class="mt-1.5 text-rose-500 text-[10px] font-bold" x-show="errors.current_password" x-text="errors.current_password" x-cloak></p>
            </div>

            <div>
                <label for="update_password_password" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">รหัสผ่านใหม่</label>
                <div class="relative">
                    <input id="update_password_password"
                           :type="showNew ? 'text' : 'password'"
                           class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 pr-10 text-xs font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                           x-model="form.password"
                           autocomplete="new-password" />
                    <button type="button" @click="showNew = !showNew"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <i :class="showNew ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                    </button>
                </div>
                <!-- Password strength bar -->
                <div class="mt-2 h-1 w-full bg-slate-100 rounded-full overflow-hidden" x-show="form.password">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="`width: ${passwordStrength}%; background: ${passwordStrengthColor}`"></div>
                </div>
                <p class="mt-1.5 text-rose-500 text-[10px] font-bold" x-show="errors.password" x-text="errors.password" x-cloak></p>
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">ยืนยันรหัสผ่านใหม่</label>
                <div class="relative">
                    <input id="update_password_password_confirmation"
                           :type="showConfirm ? 'text' : 'password'"
                           class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 pr-10 text-xs font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                           x-model="form.password_confirmation"
                           autocomplete="new-password" />
                    <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <i :class="showConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                    </button>
                </div>
                <!-- Match indicator -->
                <p class="mt-1.5 text-[10px] font-bold"
                   x-show="form.password_confirmation"
                   :class="form.password === form.password_confirmation ? 'text-orange-500' : 'text-rose-500'"
                   x-text="form.password === form.password_confirmation ? '✓ รหัสผ่านตรงกัน' : '✗ รหัสผ่านไม่ตรงกัน'"
                   x-cloak>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="button"
                    @click="save()"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 text-white font-bold text-xs py-3.5 px-7 rounded-2xl shadow-lg shadow-orange-100 transition duration-250 cursor-pointer">
                <template x-if="saving">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </template>
                <template x-if="!saving">
                    <i class="fa-solid fa-floppy-disk"></i>
                </template>
                <span x-text="saving ? 'กำลังอัปเดต...' : 'บันทึกรหัสผ่านใหม่'"></span>
            </button>
        </div>
    </div>

    <script>
    function passwordForm() {
        return {
            formOpen: false,
            form: {
                current_password:      '',
                password:              '',
                password_confirmation: '',
            },
            saving:      false,
            showCurrent: false,
            showNew:     false,
            showConfirm: false,
            errors:      {},
            toast:       { show: false, message: '', type: 'success' },

            get passwordStrength() {
                const p = this.form.password;
                if (!p) return 0;
                let score = 0;
                if (p.length >= 8)  score += 25;
                if (p.length >= 12) score += 15;
                if (/[A-Z]/.test(p)) score += 20;
                if (/[0-9]/.test(p)) score += 20;
                if (/[^A-Za-z0-9]/.test(p)) score += 20;
                return Math.min(score, 100);
            },

            get passwordStrengthColor() {
                const s = this.passwordStrength;
                if (s < 40)  return '#f43f5e'; // rose
                if (s < 70)  return '#f59e0b'; // amber
                return '#f97316';              // orange
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            save() {
                if (!this.form.current_password || !this.form.password || !this.form.password_confirmation) {
                    this.showToast('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
                    return;
                }
                if (this.form.password !== this.form.password_confirmation) {
                    this.showToast('รหัสผ่านใหม่ไม่ตรงกัน', 'error');
                    return;
                }

                this.saving = true;
                this.errors = {};

                window.addEventListener('load', function() {}, { once: true });

                axios.post('{{ route("api.profile.password") }}', this.form)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showToast(response.data.message, 'success');
                            this.form.current_password      = '';
                            this.form.password              = '';
                            this.form.password_confirmation = '';
                            this.formOpen = false;
                        } else {
                            this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                        }
                    })
                    .catch(error => {
                        if (error.response?.data?.errors) {
                            const errs = error.response.data.errors;
                            this.errors = {
                                current_password: errs.current_password?.[0] || '',
                                password:         errs.password?.[0] || '',
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
