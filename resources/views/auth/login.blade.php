<x-layout>
    <x-slot:title>เข้าสู่ระบบ | EE CPN1</x-slot>

    <div class="min-h-[80vh] flex items-center justify-center px-6 relative overflow-hidden">
        <div class="absolute top-20 left-10 w-64 h-64 bg-emerald-200 rounded-full blur-[100px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-30"></div>

        <div class="w-full max-w-[450px] relative z-10">
            <div x-data="loginForm()" class="bg-white/70 backdrop-blur-2xl p-10 md:p-14 rounded-[3.5rem] border border-white shadow-2xl shadow-emerald-100/50">
                
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-black text-slate-800 tracking-tighter italic">Welcome <span class="text-emerald-500">Back!</span></h2>
                    <p class="text-slate-400 font-medium mt-2 text-sm italic">เข้าสู่ระบบเพื่อจัดการข้อมูลของคุณ</p>
                </div>

                <!-- Error Alert Box -->
                <div x-show="errorMessage" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold rounded-2xl flex items-center gap-2" x-cloak>
                    <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                    <span x-text="errorMessage"></span>
                </div>

                <form @submit.prevent="submitLogin" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Username / Email</label>
                        <input type="text" x-model="form.email" required autofocus 
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 focus:border-emerald-400 outline-none transition-all font-medium"
                            placeholder="Username or email">
                        <template x-if="errors.email">
                            <p class="mt-2 text-xs text-rose-500 font-bold" x-text="errors.email"></p>
                        </template>
                        @if($errors->has('email'))
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-bold" />
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                        <input type="password" x-model="form.password" required 
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 focus:border-emerald-400 outline-none transition-all font-medium"
                            placeholder="••••••••">
                        <template x-if="errors.password">
                            <p class="mt-2 text-xs text-rose-500 font-bold" x-text="errors.password"></p>
                        </template>
                        @if($errors->has('password'))
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-bold" />
                        @endif
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="form.remember" class="rounded text-emerald-500 focus:ring-emerald-500 border-slate-200">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">จำฉันไว้</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">ลืมรหัสผ่าน?</a>
                        @endif
                    </div>

                    <button type="submit" :disabled="loading" 
                        class="w-full bg-slate-900 text-white py-5 rounded-2xl font-bold text-lg hover:bg-emerald-600 transition-all shadow-xl shadow-slate-200 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="loading">
                            <i class="fa-solid fa-circle-notch fa-spin text-white"></i>
                        </template>
                        <span x-text="loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function loginForm() {
            return {
                form: {
                    email: '',
                    password: '',
                    remember: false
                },
                loading: false,
                errors: {},
                errorMessage: '',

                submitLogin() {
                    this.loading = true;
                    this.errors = {};
                    this.errorMessage = '';

                    axios.post('{{ route("login") }}', this.form)
                        .then(response => {
                            if (response.data.status === 'success') {
                                window.location.href = response.data.redirect;
                            } else {
                                window.location.href = '{{ route("dashboard") }}';
                            }
                        })
                        .catch(error => {
                            this.loading = false; // Reset button spinner!
                            
                            if (error.response?.data?.errors) {
                                const errs = error.response.data.errors;
                                this.errors = {
                                    email: errs.email?.[0] || '',
                                    password: errs.password?.[0] || ''
                                };
                            } else if (error.response?.data?.message) {
                                this.errorMessage = error.response.data.message;
                            } else {
                                this.errorMessage = 'ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
                            }
                        });
                }
            };
        }
    </script>
    @endpush
</x-layout>
