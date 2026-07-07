<x-layout>
    <x-slot:title>แดชบอร์ด | EE CPN1</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-6">
        
        <header class="mb-12">
            <h2 class="text-3xl font-bold">แผงควบคุมหลัก</h2>
            <p class="text-slate-500 italic">ยินดีต้อนรับคุณ {{ Auth::user()->name }} สิทธิ์การใช้งาน: {{ Auth::user()->role }}</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col justify-between">
                <div>
                    <h4 class="font-bold mb-6">ข้อมูลส่วนตัว</h4>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-emerald-100 shadow-inner shrink-0 flex items-center justify-center bg-slate-50">
                            @if(Auth::user()->logo)
                                <img src="{{ asset('storage/' . Auth::user()->logo) }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <div class="text-2xl font-bold text-emerald-600 uppercase">
                                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 text-base leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-400 mt-1">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="text-emerald-500 text-sm font-bold mt-4 inline-block hover:text-emerald-600 transition">แก้ไขโปรไฟล์ →</a>
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                <div class="bg-white p-8 rounded-[2.5rem] border border-emerald-100 shadow-sm bg-emerald-50/10">
                    <h4 class="font-bold text-emerald-600 mb-4">เมนูจัดการระบบ</h4>
                    <ul class="space-y-3 text-sm">
                        @if(Auth::user()->role === 'admin')
                            <li><a href="{{ route('admin.schools.index') }}" class="hover:underline">🏫 จัดการเครือข่ายสถานศึกษา</a></li>
                        @else
                            <li><span class="text-slate-300 cursor-not-allowed" title="เฉพาะแอดมิน">🏫 จัดการเครือข่ายสถานศึกษา</span></li>
                        @endif
                        <li><a href="{{ route('admin.courses.index') }}" class="hover:underline">📚 อัปเดตหลักสูตรอบรม</a></li>
                    </ul>
                </div>
            @endif

            @if(Auth::user()->role === 'admin')
                <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-xl">
                    <h4 class="font-bold text-amber-400 mb-4">ส่วนเฉพาะแอดมิน</h4>
                    <ul class="space-y-3 text-sm opacity-90">
                        <li><a href="{{ route('admin.users.index') }}" class="hover:text-amber-300">👥 จัดการสมาชิกและกำหนดสิทธิ์</a></li>
                        <li><a href="{{ route('admin.org.index') }}" class="hover:text-amber-300">🌿 จัดการโครงสร้างศูนย์ (Org Chart)</a></li>
                        <li><a href="{{ route('admin.documents.index') }}" class="hover:text-amber-300">📁 จัดการคลังเอกสาร</a></li>
                        <li><a href="{{ route('admin.settings.edit') }}" class="hover:text-amber-300">⚙️ ตั้งค่าระบบเว็บไซต์</a></li>
                    </ul>
                </div>
            @endif

        </div>

        @if(Auth::user()->role === 'user')
            <div class="mt-8 bg-blue-600 p-10 rounded-[3rem] text-white">
                <h4 class="text-2xl font-bold mb-2 italic text-blue-200">สถานะของคุณครู</h4>
                <p>คุณยังไม่ได้ลงทะเบียนเข้าอบรมหลักสูตรใดๆ ในปี 2569</p>
                <a href="/#courses" class="mt-6 inline-block bg-white text-blue-600 px-8 py-3 rounded-2xl font-bold">ไปดูหลักสูตรกันเลย!</a>
            </div>
        @endif

    </div>
</x-layout>
