<x-layout>
    <x-slot:title>เข้าสู่ระบบ | BigData สพป.ชพ.1</x-slot>

    <div class="min-h-[80vh] flex items-center justify-center px-6 relative overflow-hidden">
        <div class="absolute top-20 left-10 w-64 h-64 bg-orange-200 rounded-full blur-[100px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-30"></div>

        <div class="w-full max-w-[450px] relative z-10">
            <div x-data="loginForm()" class="bg-white/70 backdrop-blur-2xl p-10 md:p-14 rounded-[3.5rem] border border-white shadow-2xl shadow-orange-100/50">
                
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-black text-slate-800 tracking-tighter italic">Welcome <span class="text-orange-500">Back!</span></h2>
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
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition-all font-medium"
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
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="form.password" required 
                                class="w-full pl-6 pr-12 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition-all font-medium"
                                placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <template x-if="showPassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </template>
                                <template x-if="!showPassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </template>
                            </button>
                        </div>
                        <template x-if="errors.password">
                            <p class="mt-2 text-xs text-rose-500 font-bold" x-text="errors.password"></p>
                        </template>
                        @if($errors->has('password'))
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-bold" />
                        @endif
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="form.remember" class="rounded text-orange-500 focus:ring-orange-500 border-slate-200">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">จำฉันไว้</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">ลืมรหัสผ่าน?</a>
                        @endif
                    </div>

                    <button type="submit" :disabled="loading" 
                        class="w-full bg-slate-900 text-white py-5 rounded-2xl font-bold text-lg hover:bg-orange-600 transition-all shadow-xl shadow-slate-200 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
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
                showPassword: false,

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
