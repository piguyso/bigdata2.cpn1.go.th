<x-layout>
    <x-slot:title>ลงทะเบียนครูใหม่ | EE CPN1</x-slot>

    <div class="min-h-[90vh] flex items-center justify-center px-6 py-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-100 rounded-full blur-[100px] opacity-40"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-blue-100 rounded-full blur-[100px] opacity-40"></div>

        <div class="w-full max-w-[500px] relative z-10">
            <div class="bg-white/80 backdrop-blur-2xl p-10 md:p-14 rounded-[4rem] border border-white shadow-2xl shadow-emerald-100/30">
                
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-emerald-500 text-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-100 rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    </div>
                    <h2 class="text-4xl font-black text-slate-800 tracking-tighter italic">Join <span class="text-emerald-500">Us!</span></h2>
                    <p class="text-slate-400 font-medium mt-2 text-sm italic">สร้างบัญชีเพื่อรับสิทธิ์การอบรมปี 2569</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                        <input type="text" name="name" required autofocus 
                            class="w-full px-6 py-4 bg-white/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 outline-none transition-all font-medium"
                            placeholder="ชื่อ - นามสกุล">
                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-rose-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required 
                            class="w-full px-6 py-4 bg-white/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 outline-none transition-all font-medium"
                            placeholder="example@mail.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                            <input type="password" name="password" required 
                                class="w-full px-6 py-4 bg-white/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 outline-none transition-all font-medium"
                                placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Confirm</label>
                            <input type="password" name="password_confirmation" required 
                                class="w-full px-6 py-4 bg-white/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 outline-none transition-all font-medium"
                                placeholder="••••••••">
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-500 font-bold" />

                    <button type="submit" class="w-full bg-emerald-500 text-white py-5 rounded-2xl font-bold text-lg hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-200 mt-6 active:scale-95">
                        ลงทะเบียนเลย! 
                    </button>

                    <p class="text-center text-sm font-medium text-slate-400 pt-4 uppercase tracking-tighter">
                        มีบัญชีอยู่แล้ว? <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline">เข้าสู่ระบบตรงนี้</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-layout>
