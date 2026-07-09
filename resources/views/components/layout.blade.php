@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'EE.CPN1';
    $webLogo = $settings['web_logo'] ?? null;
    $contactEmail = $settings['contact_email'] ?? 'info@anubanchumphon.ac.th';
    $contactPhone = $settings['contact_phone'] ?? '077-511124';
    $contactAddress = $settings['contact_address'] ?? 'โรงเรียนอนุบาลชุมพร ถ.ปรมินทรมรรคา ต.ท่าตะเภา อ.เมืองชุมพร จ.ชุมพร 86000';
@endphp
<!DOCTYPE html>
<html lang="th" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($webName === 'EE.CPN1' ? 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สำนักงานเขตพื้นที่การศึกษาประถมศึกษาชุมพร เขต 1' : $webName) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Anuphan', 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .nav-link { position: relative; font-size: 0.875rem; font-weight: 600; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -4px; left: 0; background: #10b981; transition: 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        [x-cloak] { display: none !important; }
    </style>
    <link class="icon-tag" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="antialiased selection:bg-emerald-500 selection:text-white">

    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100/80 transition-all duration-300" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                @if($webLogo)
                    <img src="{{ asset('storage/' . $webLogo) }}" alt="Logo" class="h-10 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100 group-hover:scale-105 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </div>
                @endif
                <div class="flex flex-col justify-center">
                    <span class="brand-title text-base md:text-lg font-extrabold tracking-tight text-slate-900 group-hover:text-emerald-600 transition-colors duration-300 leading-none">
                        @if($webName === 'EE.CPN1')
                            EE<span class="text-emerald-500">.</span>CPN1
                        @else
                            {{ $webName }}
                        @endif
                    </span>
                    <span class="brand-subtitle text-[9px] md:text-[10px] font-bold text-slate-500 group-hover:text-emerald-600 transition-colors duration-300 leading-none mt-1.5">
                        {{ $settings['web_subtitle'] ?? 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1' }}
                    </span>
                </div>
            </a>
            
            <div class="hidden md:flex gap-8 items-center font-semibold text-slate-500">
                <a href="/" class="nav-link hover:text-emerald-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-house text-emerald-500"></i> หน้าหลัก
                </a>
                <a href="{{ route('reports.index') }}" class="nav-link hover:text-emerald-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-emerald-500"></i> ข้อมูลบุคลากร
                </a>
                @auth
                <a href="{{ route('profile.edit') }}" class="nav-link hover:text-emerald-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-user text-emerald-500"></i> ข้อมูลของฉัน
                </a>
                @endauth
                @auth
                <a href="{{ route('plc.index') }}" class="nav-link hover:text-emerald-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-users-gear text-emerald-500"></i> PLC
                </a>
                @endauth
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <!-- User Settings Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <!-- Trigger Button (Gear Icon with tiny profile avatar badge) -->
                        <button @click="open = !open" type="button" class="w-10 h-10 bg-slate-50 border border-slate-100 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-100 rounded-xl flex items-center justify-center transition-all duration-200 cursor-pointer shadow-sm relative group">
                            <i class="fa-solid fa-gear text-lg group-hover:rotate-45 transition-transform duration-300"></i>
                            
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[8px] font-bold text-white border border-white overflow-hidden">
                                @if(Auth::user()->logo)
                                    <img src="{{ asset('storage/' . Auth::user()->logo) }}" alt="Logo" class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </span>
                        </button>

                        <!-- Dropdown Menu Items -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2.5 w-64 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 p-2 space-y-1 text-left origin-top-right"
                             x-cloak>
                             
                            <!-- User Information Submenu Item -->
                            <div class="px-4 py-3 border-b border-slate-100/60 flex items-center gap-3 select-none">
                                @if(Auth::user()->logo)
                                    <img src="{{ asset('storage/' . Auth::user()->logo) }}" alt="Logo" class="w-9 h-9 rounded-full object-cover border border-emerald-300">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">
                                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="overflow-hidden">
                                    <span class="block text-[10px] font-bold text-slate-400">เข้าสู่ระบบโดย</span>
                                    <span class="block text-xs font-extrabold text-slate-800 truncate" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</span>
                                    <span class="block text-[9px] text-emerald-600 font-extrabold uppercase tracking-wide">
                                        @if(Auth::user()->role === 'admin')
                                            ผู้ดูแลระบบ (Admin)
                                        @elseif(Auth::user()->role === 'teacher')
                                            ครูผู้สอน (Teacher)
                                        @else
                                            สมาชิกเครือข่าย
                                        @endif
                                    </span>
                                </div>
                            </div>



                            <!-- Dynamic Management Links Based on Roles -->
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-school text-slate-450 w-4 text-center text-slate-400"></i>
                                    จัดการเครือข่ายสถานศึกษา
                                </a>
                            @endif

                            @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                                <a href="{{ route('admin.lms.courses.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-graduation-cap text-slate-450 w-4 text-center text-slate-400"></i>
                                    การตั้งค่า LMS
                                </a>
                            @endif

                                <a href="{{ route('plc.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-users-gear text-slate-450 w-4 text-center text-slate-400"></i>
                                    กระบวนการ PLC
                                </a>

                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.org.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-sitemap text-slate-450 w-4 text-center text-slate-400"></i>
                                    จัดการโครงสร้างศูนย์
                                </a>
                                <a href="{{ route('admin.documents.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-folder-open text-slate-450 w-4 text-center text-slate-400"></i>
                                    จัดการคลังเอกสาร
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-users text-slate-450 w-4 text-center text-slate-400"></i>
                                    จัดการสมาชิกและสิทธิ์
                                </a>
                                <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-sliders text-slate-450 w-4 text-center text-slate-400"></i>
                                    ตั้งค่าระบบเว็บไซต์
                                </a>
                            @endif
                            
                            <!-- Profile Link Submenu -->
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                <i class="fa-regular fa-user text-slate-450 w-4 text-center text-slate-400"></i>
                                จัดการข้อมูลส่วนตัว
                            </a>
                            
                            <!-- Password Change Link -->
                            <a href="{{ route('profile.password.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                <i class="fa-solid fa-key text-slate-450 w-4 text-center text-slate-400"></i>
                                เปลี่ยนรหัสผ่าน
                            </a>

                            <hr class="border-slate-50 my-1">

                            <!-- Logout Submenu -->
                            <form method="POST" action="{{ route('logout') }}" class="block w-full">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition duration-200 text-left cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                    ออกจากระบบ
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-500 hover:text-emerald-600 transition px-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-right-to-bracket text-emerald-500"></i> ลงชื่อเข้าใช้
                    </a>
                @endauth

                <!-- Hamburger Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden w-10 h-10 bg-slate-50 border border-slate-100 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-100 rounded-xl flex items-center justify-center transition-all duration-200 cursor-pointer shadow-sm focus:outline-none shrink-0" aria-label="Toggle Menu">
                    <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark text-lg' : 'fa-bars text-lg'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden bg-white border-t border-slate-100 px-6 py-4 space-y-4 shadow-xl relative z-40"
             x-cloak>
            
            <a href="/" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 flex items-center gap-2" @click="mobileMenuOpen = false">
                <i class="fa-solid fa-house text-emerald-500 w-4 text-center"></i> หน้าหลัก
            </a>
            <a href="{{ route('reports.index') }}" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 flex items-center gap-2" @click="mobileMenuOpen = false">
                <i class="fa-solid fa-users text-emerald-500 w-4 text-center"></i> ข้อมูลบุคลากร
            </a>
            @auth
            <a href="{{ route('profile.edit') }}" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 flex items-center gap-2" @click="mobileMenuOpen = false">
                <i class="fa-solid fa-user text-emerald-500 w-4 text-center"></i> ข้อมูลของฉัน
            </a>
            @endauth
            @auth
            <a href="{{ route('plc.index') }}" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 flex items-center gap-2" @click="mobileMenuOpen = false">
                <i class="fa-solid fa-users-gear text-emerald-500 w-4 text-center"></i> PLC
            </a>
            @endauth

            @guest
                <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 py-2.5 text-sm font-bold text-slate-600 hover:text-emerald-600 hover:bg-slate-50 rounded-xl border border-slate-100 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-right-to-bracket text-emerald-500"></i> ลงชื่อเข้าใช้
                    </a>
                </div>
            @endguest

            @auth
                <div class="pt-4 border-t border-slate-100 space-y-2">
                    <div class="px-2 py-1.5 flex items-center gap-3 select-none">
                        @if(Auth::user()->logo)
                            <img src="{{ asset('storage/' . Auth::user()->logo) }}" alt="Logo" class="w-9 h-9 rounded-full object-cover border border-emerald-300">
                        @else
                            <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <span class="block text-[10px] font-bold text-slate-400">เข้าสู่ระบบโดย</span>
                            <span class="block text-xs font-extrabold text-slate-800 truncate">{{ Auth::user()->name }}</span>
                        </div>
                    </div>

                    
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-school w-4 text-center text-slate-400"></i> จัดการเครือข่ายสถานศึกษา
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                        <a href="{{ route('admin.lms.courses.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-graduation-cap w-4 text-center text-slate-400"></i> การตั้งค่า LMS
                        </a>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.org.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-sitemap w-4 text-center text-slate-400"></i> จัดการโครงสร้างศูนย์
                        </a>
                        <a href="{{ route('admin.documents.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-folder-open w-4 text-center text-slate-400"></i> จัดการคลังเอกสาร
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-users w-4 text-center text-slate-400"></i> จัดการสมาชิกและสิทธิ์
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-sliders w-4 text-center text-slate-400"></i> ตั้งค่าระบบเว็บไซต์
                        </a>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                        <i class="fa-regular fa-user w-4 text-center text-slate-400"></i> จัดการข้อมูลส่วนตัว
                    </a>
                    
                    <a href="{{ route('profile.password.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-key w-4 text-center text-slate-400"></i> เปลี่ยนรหัสผ่าน
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="block w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-rose-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition text-left cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> ออกจากระบบ
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main>{{ $slot }}</main>

    <footer class="bg-slate-900 text-slate-400 py-16 border-t border-slate-800 mt-32">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="space-y-4">
                <a href="/" class="flex items-center gap-3">
                    @if($webLogo)
                        <img src="{{ asset('storage/' . $webLogo) }}" alt="Logo" class="h-10 w-auto object-contain">
                    @else
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </div>
                    @endif
                    <span class="text-xl font-bold tracking-tight text-white">
                        @if($webName === 'EE.CPN1')
                            EE<span class="text-emerald-400">.</span>CPN1
                        @else
                            {{ $webName }}
                        @endif
                    </span>
                </a>
                <p class="text-xs text-slate-400 leading-relaxed pt-2">
                    {{ $webName === 'EE.CPN1' ? 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สำนักงานเขตพื้นที่การศึกษาประถมศึกษาชุมพร เขต 1' : $webName }} มุ่งมั่นยกระดับศักยภาพการจัดการเรียนรู้ เพื่อการพัฒนาวิชาชีพครูและบุคลากรทางการศึกษาอย่างยั่งยืน
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ลิงก์ด่วน</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="/" class="hover:text-emerald-400 transition">หน้าหลัก</a></li>
                    <li><a href="/#about" class="hover:text-emerald-400 transition">เกี่ยวกับเรา</a></li>
                    <li><a href="{{ route('org.public') }}" class="hover:text-emerald-400 transition">โครงสร้างศูนย์</a></li>
                    <li><a href="/#courses" class="hover:text-emerald-400 transition">หลักสูตรอบรม</a></li>
                    <li><a href="/#schools" class="hover:text-emerald-400 transition">เครือข่ายสถานศึกษา</a></li>
                    <li><a href="{{ route('documents.public') }}" class="hover:text-emerald-400 transition">เอกสารเผยแพร่</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ลิงก์ที่เป็นประโยชน์</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="https://cpn1.go.th" target="_blank" class="hover:text-emerald-400 transition">สพป.ชุมพร เขต 1</a></li>
                    <li><a href="https://www.moe.go.th" target="_blank" class="hover:text-emerald-400 transition">กระทรวงศึกษาธิการ</a></li>
                    <li><a href="{{ route('profile.edit') }}" class="hover:text-emerald-400 transition">จัดการข้อมูลส่วนตัว (Profile)</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ติดต่อ{{ $webName === 'EE.CPN1' ? 'ศูนย์พัฒนาครู' : $webName }}</h4>
                <ul class="space-y-2 text-xs leading-relaxed">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot mt-0.5 text-emerald-500"></i>
                        <span>{{ $contactAddress }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-emerald-500"></i>
                        <span>{{ $contactPhone }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-emerald-500"></i>
                        <span>{{ $contactEmail }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 border-t border-slate-800 mt-12 pt-6 text-center text-xs">
            <p>© 2026 {{ $webName === 'EE.CPN1' ? 'Teachers and Educational Personnel Development Center, Chumphon Primary Education Service Area Office 1' : $webName }} • All Rights Reserved</p>
        </div>
    </footer>

    <!-- Global Custom Confirm Modal -->
    <div x-data x-show="$store.confirm.open" 
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div x-show="$store.confirm.open"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-sm p-6 overflow-hidden text-center relative"
             @click.outside="$store.confirm.open = false">
            
            <!-- Danger Icon -->
            <template x-if="$store.confirm.type === 'danger'">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl mx-auto mb-4 border border-rose-100">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </template>

            <!-- Warning Icon -->
            <template x-if="$store.confirm.type === 'warning'">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl mx-auto mb-4 border border-amber-100">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </template>

            <!-- Title -->
            <h3 class="font-extrabold text-slate-800 text-sm mb-2" x-text="$store.confirm.title"></h3>
            
            <!-- Description -->
            <p class="text-slate-500 text-[10.5px] leading-relaxed mb-6 px-2" x-text="$store.confirm.text"></p>
            
            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button" @click="$store.confirm.open = false" 
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-650 font-bold py-2.5 rounded-xl text-xs transition cursor-pointer border border-slate-200 shadow-sm">
                    <span x-text="$store.confirm.cancelButtonText"></span>
                </button>
                <button type="button" 
                        @click="$store.confirm.open = false; if($store.confirm.onConfirm) $store.confirm.onConfirm()" 
                        class="flex-1 font-bold py-2.5 rounded-xl text-xs transition cursor-pointer shadow-lg"
                        :class="$store.confirm.type === 'danger' 
                            ? 'bg-rose-500 hover:bg-rose-600 text-white shadow-rose-100' 
                            : 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-100'">
                    <span x-text="$store.confirm.confirmButtonText"></span>
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>