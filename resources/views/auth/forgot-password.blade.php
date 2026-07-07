<x-layout>
    <x-slot:title>ลืมรหัสผ่าน | EE CPN1</x-slot>

    <div class="min-h-[80vh] flex items-center justify-center px-6 relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-50 rounded-full blur-[120px] opacity-60"></div>

        <div class="w-full max-w-[480px] relative z-10">
            <div class="bg-white/80 backdrop-blur-2xl p-10 md:p-14 rounded-[4rem] border border-white shadow-2xl shadow-emerald-100/20">
                
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-slate-50 text-emerald-500 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-inner transition-transform hover:scale-110 duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tighter italic">Forgot <span class="text-emerald-500">Password?</span></h2>
                    <p class="text-slate-400 font-medium mt-4 text-sm leading-relaxed italic">
                        ไม่เป็นไรครับ! เพียงระบุอีเมลที่ใช้ลงทะเบียน <br>
                        เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปให้ทันที
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl text-xs font-bold text-center animate-fade-in">
                        ✨ {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Your Registered Email</label>
                        <input type="email" name="email" :value="old('email')" required autofocus 
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 focus:border-emerald-400 outline-none transition-all font-medium"
                            placeholder="example@mail.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-bold" />
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-bold text-lg hover:bg-emerald-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                            ส่งลิงก์ตั้งรหัสใหม่ 📧
                        </button>
                    </div>

                    <div class="text-center pt-6">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-400 hover:text-emerald-600 transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            กลับไปหน้าเข้าสู่ระบบ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
