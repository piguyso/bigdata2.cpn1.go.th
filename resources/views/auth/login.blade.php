<x-layout>
    <x-slot:title>เข้าสู่ระบบ | EE CPN1</x-slot>

    <div class="min-h-[80vh] flex items-center justify-center px-6 relative overflow-hidden">
        <div class="absolute top-20 left-10 w-64 h-64 bg-emerald-200 rounded-full blur-[100px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-30"></div>

        <div class="w-full max-w-[450px] relative z-10">
            <div class="bg-white/70 backdrop-blur-2xl p-10 md:p-14 rounded-[3.5rem] border border-white shadow-2xl shadow-emerald-100/50">
                
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-black text-slate-800 tracking-tighter italic">Welcome <span class="text-emerald-500">Back!</span></h2>
                    <p class="text-slate-400 font-medium mt-2 text-sm italic">เข้าสู่ระบบเพื่อจัดการข้อมูลของคุณ</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required autofocus 
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 focus:border-emerald-400 outline-none transition-all font-medium"
                            placeholder="example@mail.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-bold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                        <input type="password" name="password" required 
                            class="w-full px-6 py-4 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-emerald-100 focus:border-emerald-400 outline-none transition-all font-medium"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-bold" />
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-emerald-500 focus:ring-emerald-500 border-slate-200">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">จำฉันไว้</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">ลืมรหัสผ่าน?</a>
                        @endif
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-bold text-lg hover:bg-emerald-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                        เข้าสู่ระบบ 
                    </button>

                    <p class="text-center text-sm font-medium text-slate-400 pt-4">
                        ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-emerald-600 font-bold hover:underline">ลงทะเบียนครูใหม่</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-layout>
