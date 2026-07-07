@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'IPST.CHUMPHON';
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
    <title>{{ $title ?? ($webName === 'IPST.CHUMPHON' ? 'ศูนย์พัฒนาครู สสวท. จังหวัดชุมพร' : $webName) }}</title>
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
    <link rel="icon" type="image/svg+xml" 
          href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path fill='%2310b981' d='M440.8 318.1c-16.1 44.5-54.3 78.4-98.8 88.3 11 16 17.5 35.3 17.5 56.1 0 53-43 96-96 96s-96-43-96-96c0-20.8 6.5-40.1 17.5-56.1-44.5-9.9-82.7-43.8-98.8-88.3-17.7-48.9-10.4-100.2 18.6-139.6-18.1-15.6-29.5-38.6-29.5-64.4 0-46.4 37.6-84 84-84s84 37.6 84 84c0 25.8-11.4 48.8-29.5 64.4 29 39.4 36.3 90.7 18.6 139.6zm-177.3 33.1c11.5 3.1 23.5 4.8 35.9 4.8s24.4-1.7 35.9-4.8c-23.2-36.2-56.2-64.8-95-81.9 23.1 25.4 39.5 54.7 43.1 81.9zm-38.9-198c-28.7 0-52 23.3-52 52s23.3 52 52 52 52-23.3 52-52-23.3-52-52-52zm112.5 125.1c-38.8 17.1-71.8 45.7-95 81.9 11.5 3.1 23.5 4.8 35.9 4.8s24.4-1.7 35.9-4.8c3.6-27.2 20-56.5 43.1-81.9z'/></svg>">
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
                        @if($webName === 'IPST.CHUMPHON')
                            IPST<span class="text-emerald-500">.</span>CHUMPHON
                        @else
                            {{ $webName }}
                        @endif
                    </span>
                    <span class="text-[9px] md:text-[10px] font-bold text-slate-500 group-hover:text-emerald-600 transition-colors duration-300 leading-none mt-1.5">
                        ศูนย์พัฒนาครู สสวท. จังหวัดชุมพร
                    </span>
                </div>
            </a>
            
            <div class="hidden md:flex gap-8 items-center font-semibold text-slate-500">
                <a href="/" class="nav-link hover:text-emerald-600 transition">หน้าหลัก</a>

                <!-- Dropdown: ข้อมูลศูนย์พัฒนาครู -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button" class="nav-link hover:text-emerald-600 transition flex items-center gap-1 font-semibold text-slate-500 py-2 cursor-pointer outline-none">
                        ข้อมูลศูนย์ฯ <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 mt-1 w-56 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 p-2 space-y-0.5 text-left"
                         x-cloak>
                         <a href="/#about" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                             <i class="fa-solid fa-circle-info w-4 text-center text-slate-400 text-[13px]"></i> เกี่ยวกับศูนย์ฯ
                         </a>
                         <a href="/org" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                             <i class="fa-solid fa-sitemap w-4 text-center text-slate-400 text-[13px]"></i> โครงสร้างบุคลากร
                         </a>
                         <a href="/#schools" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                             <i class="fa-solid fa-school w-4 text-center text-slate-400 text-[13px]"></i> โรงเรียนในเครือข่าย
                         </a>
                    </div>
                </div>

                <!-- Dropdown: บริการวิชาการ -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button" class="nav-link hover:text-emerald-600 transition flex items-center gap-1 font-semibold text-slate-500 py-2 cursor-pointer outline-none">
                        หลักสูตรและคลังสื่อ <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 mt-1 w-56 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 p-2 space-y-0.5 text-left"
                         x-cloak>
                         <a href="/#courses" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                             <i class="fa-solid fa-graduation-cap w-4 text-center text-slate-400 text-[13px]"></i> หลักสูตรอบรมครู
                         </a>
                         <a href="/documents" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                             <i class="fa-solid fa-folder-open w-4 text-center text-slate-400 text-[13px]"></i> คลังเอกสารเผยแพร่
                         </a>
                    </div>
                </div>

                <a href="/#contact" class="nav-link hover:text-emerald-600 transition">ติดต่อเรา</a>
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

                            <!-- Dashboard Link Submenu -->
                            <a href="/dashboard" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                <i class="fa-solid fa-table-columns text-slate-450 w-4 text-center text-slate-400"></i>
                                Dashboard (แผงควบคุม)
                            </a>

                            <!-- Dynamic Management Links Based on Roles -->
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-school text-slate-450 w-4 text-center text-slate-400"></i>
                                    จัดการโรงเรียนเครือข่าย
                                </a>
                            @endif

                            @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                                <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                    <i class="fa-solid fa-graduation-cap text-slate-450 w-4 text-center text-slate-400"></i>
                                    อัปเดตหลักสูตรอบรม
                                </a>
                            @endif

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
                            <a href="/profile" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-200">
                                <i class="fa-regular fa-user text-slate-450 w-4 text-center text-slate-400"></i>
                                จัดการข้อมูลส่วนตัว
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
                    <a href="/login" class="text-sm font-bold text-slate-400 hover:text-slate-600 transition px-3">ลงชื่อเข้าใช้</a>
                    <a href="/register" class="hidden sm:inline-block bg-emerald-500 text-white px-7 py-3 rounded-2xl font-bold text-sm hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100 hover:-translate-y-0.5 transition-transform duration-200">
                        เข้าร่วมโครงการ
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
            
            <a href="/" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2" @click="mobileMenuOpen = false">หน้าหลัก</a>

            <!-- Mobile Dropdown: ข้อมูลศูนย์พัฒนาครู -->
            <div x-data="{ open: false }" class="space-y-1">
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 text-left cursor-pointer outline-none">
                    <span>ข้อมูลศูนย์ฯ</span>
                    <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pl-4 space-y-2 py-1.5 border-l border-slate-100">
                    <a href="/#about" class="flex items-center gap-2.5 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-circle-info w-4 text-center text-slate-400"></i> เกี่ยวกับศูนย์ฯ
                    </a>
                    <a href="/org" class="flex items-center gap-2.5 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-sitemap w-4 text-center text-slate-400"></i> โครงสร้างบุคลากร
                    </a>
                    <a href="/#schools" class="flex items-center gap-2.5 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-school w-4 text-center text-slate-400"></i> โรงเรียนในเครือข่าย
                    </a>
                </div>
            </div>

            <!-- Mobile Dropdown: บริการวิชาการ -->
            <div x-data="{ open: false }" class="space-y-1">
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2 text-left cursor-pointer outline-none">
                    <span>หลักสูตรและคลังสื่อ</span>
                    <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pl-4 space-y-2 py-1.5 border-l border-slate-100">
                    <a href="/#courses" class="flex items-center gap-2.5 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-graduation-cap w-4 text-center text-slate-400"></i> หลักสูตรอบรมครู
                    </a>
                    <a href="/documents" class="flex items-center gap-2.5 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-folder-open w-4 text-center text-slate-400"></i> คลังเอกสารเผยแพร่
                    </a>
                </div>
            </div>

            <a href="/#contact" class="block text-sm font-bold text-slate-600 hover:text-emerald-600 transition py-2" @click="mobileMenuOpen = false">ติดต่อเรา</a>

            @guest
                <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                    <a href="/login" class="w-full flex items-center justify-center py-2.5 text-sm font-bold text-slate-600 hover:text-emerald-600 hover:bg-slate-50 rounded-xl border border-slate-100 transition" @click="mobileMenuOpen = false">ลงชื่อเข้าใช้</a>
                    <a href="/register" class="w-full flex items-center justify-center py-2.5 text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl transition shadow-lg shadow-emerald-100" @click="mobileMenuOpen = false">เข้าร่วมโครงการ</a>
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
                    <a href="/dashboard" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                        <i class="fa-solid fa-table-columns w-4 text-center text-slate-400"></i> Dashboard (แผงควบคุม)
                    </a>
                    
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-school w-4 text-center text-slate-400"></i> จัดการโรงเรียนเครือข่าย
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                        <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                            <i class="fa-solid fa-graduation-cap w-4 text-center text-slate-400"></i> อัปเดตหลักสูตรอบรม
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

                    <a href="/profile" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" @click="mobileMenuOpen = false">
                        <i class="fa-regular fa-user w-4 text-center text-slate-400"></i> จัดการข้อมูลส่วนตัว
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
                        @if($webName === 'IPST.CHUMPHON')
                            IPST<span class="text-emerald-400">.</span>CHUMPHON
                        @else
                            {{ $webName }}
                        @endif
                    </span>
                </a>
                <p class="text-xs text-slate-400 leading-relaxed pt-2">
                    ศูนย์พัฒนาครู สสวท. ระดับประถมศึกษา จังหวัดชุมพร (โรงเรียนอนุบาลชุมพร) มุ่งมั่นยกระดับศักยภาพการจัดการเรียนรู้ด้านวิทยาศาสตร์ คณิตศาสตร์ และเทคโนโลยี เพื่อการพัฒนาครูอย่างยั่งยืน
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ลิงก์ด่วน</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="/" class="hover:text-emerald-400 transition">หน้าหลัก</a></li>
                    <li><a href="/#about" class="hover:text-emerald-400 transition">เกี่ยวกับเรา</a></li>
                    <li><a href="/org" class="hover:text-emerald-400 transition">โครงสร้างศูนย์</a></li>
                    <li><a href="/#courses" class="hover:text-emerald-400 transition">หลักสูตรอบรม</a></li>
                    <li><a href="/#schools" class="hover:text-emerald-400 transition">โรงเรียนเครือข่าย</a></li>
                    <li><a href="/documents" class="hover:text-emerald-400 transition">เอกสารเผยแพร่</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ลิงก์ที่เป็นประโยชน์</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="https://www.ipst.ac.th" target="_blank" class="hover:text-emerald-400 transition">สสวท. ส่วนกลาง</a></li>
                    <li><a href="https://www.moe.go.th" target="_blank" class="hover:text-emerald-400 transition">กระทรวงศึกษาธิการ</a></li>
                    <li><a href="/dashboard" class="hover:text-emerald-400 transition">แผงควบคุมระบบ (Dashboard)</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">ติดต่อศูนย์พัฒนาครู</h4>
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
            <p>© 2026 {{ $webName === 'IPST.CHUMPHON' ? 'IPST Chumphon Center (Anuban Chumphon School)' : $webName }} • All Rights Reserved</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>