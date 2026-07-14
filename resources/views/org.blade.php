@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'BigData สพป.ชพ.1';
@endphp
<x-layout>
    <x-slot:title>โครงสร้างหน่วยงาน | {{ $webName }}</x-slot>

    <!-- Custom Style Definitions -->
    <style>
        /* Smooth Scroll Reveal Animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform, opacity;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Connector Line Animations */
        @keyframes scale-y {
            from { transform: scaleY(0); opacity: 0; }
            to { transform: scaleY(1); opacity: 1; }
        }
        @keyframes scale-x {
            from { transform: scaleX(0); opacity: 0; }
            to { transform: scaleX(1); opacity: 1; }
        }
        @keyframes fade-in-dot {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .line-grow-y {
            transform-origin: top;
            transform: scaleY(0);
            animation: scale-y 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
        }
        .line-grow-y-delayed {
            transform-origin: top;
            transform: scaleY(0);
            animation: scale-y 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.9s forwards;
        }
        .line-grow-x {
            transform-origin: center;
            transform: scaleX(0);
            animation: scale-x 1s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
        }
        .dot-appear {
            transform: scale(0);
            animation: fade-in-dot 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 1.2s forwards;
        }
    </style>

    @php
        if (!function_exists('renderMemberPhoto')) {
            function renderMemberPhoto($member, $sizeClass = 'w-28 h-36') {
                if ($member->photo_url) {
                    return '<div class="' . $sizeClass . ' bg-slate-50 border border-slate-100 rounded-2xl mx-auto overflow-hidden shadow-inner flex items-center justify-center relative group">
                        <img src="' . e($member->photo_url) . '" alt="' . e($member->name) . '" class="w-full h-full object-cover">
                    </div>';
                }
                
                // Determine fallback style based on role_title and committee
                $roleTitle = $member->role_title ?: '';
                $pos = $member->position ?: '';
                $committee = $member->committee ?: 'operations';
                
                $bgClass = 'bg-gradient-to-br from-slate-500/10 to-slate-650/15 border-slate-200';
                $iconClass = 'fa-solid fa-user text-slate-500';
                
                if (mb_strpos($roleTitle, 'ประธาน') !== false && mb_strpos($roleTitle, 'รอง') === false) {
                    $bgClass = 'bg-gradient-to-br from-orange-500/10 to-teal-500/15 border-orange-500/10';
                    $iconClass = 'fa-solid fa-user-tie text-orange-600';
                } elseif (mb_strpos($roleTitle, 'รอง') !== false || mb_strpos($roleTitle, 'รอง ผอ.') !== false || mb_strpos($roleTitle, 'รองผู้อำนวยการ') !== false) {
                    $bgClass = 'bg-gradient-to-br from-sky-500/10 to-blue-500/15 border-sky-500/10';
                    $iconClass = 'fa-solid fa-user-tie text-sky-600';
                } elseif (mb_strpos($roleTitle, 'เลขานุการ') !== false || mb_strpos($roleTitle, 'ประสานงาน') !== false) {
                    $bgClass = 'bg-gradient-to-br from-amber-500/10 to-orange-500/15 border-amber-500/10';
                    $iconClass = 'fa-solid fa-user-graduate text-amber-600';
                } elseif ($member->role === 'advisor') {
                    $bgClass = 'bg-gradient-to-br from-purple-500/10 to-indigo-500/15 border-purple-500/10';
                    $iconClass = 'fa-solid fa-user-shield text-purple-600';
                } elseif ($committee === 'academic') {
                    if (mb_strpos($roleTitle, 'วิชาการ') !== false || mb_strpos($pos, 'วิชาการ') !== false) {
                        $iconClass = 'fa-solid fa-graduation-cap text-slate-500';
                    } else {
                        $iconClass = 'fa-solid fa-user-gear text-slate-500';
                    }
                } elseif ($committee === 'finance') {
                    if (mb_strpos($roleTitle, 'สถานที่') !== false || mb_strpos($pos, 'สถานที่') !== false) {
                        $iconClass = 'fa-solid fa-map-location-dot text-slate-500';
                    } elseif (mb_strpos($roleTitle, 'การเงิน') !== false || mb_strpos($pos, 'การเงิน') !== false || mb_strpos($pos, 'บัญชี') !== false) {
                        $iconClass = 'fa-solid fa-wallet text-slate-500';
                    }
                }
                
                $iSizeClass = ($sizeClass === 'w-32 h-40') ? 'text-4xl' : 'text-3xl';
                
                return '
                <div class="' . $sizeClass . ' ' . $bgClass . ' border rounded-2xl mx-auto flex items-center justify-center shadow-inner relative group">
                    <i class="' . $iconClass . ' ' . $iSizeClass . ' opacity-80 group-hover:scale-110 transition duration-300"></i>
                </div>';
            }
        }

        if (!function_exists('renderBadge')) {
            function renderBadge($member) {
                $roleTitle = $member->role_title ?: ($member->role === 'advisor' ? 'ที่ปรึกษาศูนย์' : 'คณะทำงานศูนย์');
                
                $badgeClass = 'bg-slate-100 text-slate-700';
                if (mb_strpos($roleTitle, 'ประธาน') !== false && mb_strpos($roleTitle, 'รอง') === false) {
                    $badgeClass = 'bg-orange-50 text-orange-700';
                } elseif (mb_strpos($roleTitle, 'รอง') !== false) {
                    $badgeClass = 'bg-sky-50 text-sky-700';
                } elseif (mb_strpos($roleTitle, 'เลขานุการ') !== false || mb_strpos($roleTitle, 'ประสานงาน') !== false) {
                    $badgeClass = 'bg-amber-50 text-amber-700';
                } elseif ($member->role === 'advisor') {
                    $badgeClass = 'bg-purple-50 text-purple-700';
                }
                
                return '<span class="inline-block px-2.5 py-0.5 ' . $badgeClass . ' rounded-md text-[9px] font-bold text-center leading-normal">' . e($roleTitle) . '</span>';
            }
        }

        // Filter members by committee
        $operations = $members->where('committee', 'operations');
        $executive = $members->where('committee', 'executive');
        $academic = $members->where('committee', 'academic');
        $finance = $members->where('committee', 'finance');

        $tabs = [
            [
                'id' => 'operations',
                'title' => 'คณะที่ปรึกษา',
                'icon' => 'fa-solid fa-sitemap',
                'members' => $operations,
                'empty_icon' => 'fa-solid fa-sitemap',
            ],
            [
                'id' => 'executive',
                'title' => '1. คณะกรรมการอำนวยการ',
                'icon' => 'fa-solid fa-shield-halved',
                'members' => $executive,
                'empty_icon' => 'fa-solid fa-shield-halved',
            ],
            [
                'id' => 'academic',
                'title' => '2. คณะกรรมการวิชาการ',
                'icon' => 'fa-solid fa-book-open',
                'members' => $academic,
                'empty_icon' => 'fa-solid fa-book-open',
            ],
            [
                'id' => 'finance',
                'title' => '3. คณะกรรมการการเงิน & สถานที่',
                'icon' => 'fa-solid fa-wallet',
                'members' => $finance,
                'empty_icon' => 'fa-solid fa-wallet',
            ],
        ];
    @endphp

    <main class="py-16 md:py-24 space-y-12" x-data="{ activeTab: 'operations' }">
        <!-- Header Section -->
        <section class="max-w-7xl mx-auto px-6 text-center space-y-4">
            <div class="inline-block px-3 py-1 bg-orange-50 text-orange-700 rounded-full text-xs font-bold uppercase tracking-wider">
                โครงสร้างหน่วยงาน
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                โครงสร้างบุคลากรดำเนินงานประจำศูนย์
            </h2>
            <p class="text-slate-500 text-sm max-w-2xl mx-auto leading-relaxed">
                คณะกรรมการและที่ปรึกษาผู้ร่วมจัดตั้งและขับเคลื่อนทางวิชาการ เพื่อส่งเสริมกระบวนการเรียนรู้อย่างยั่งยืน ประจำ{{ $webName === 'BigData สพป.ชพ.1' ? 'ฐานข้อมูล BigData สพป.ชพ. 1' : $webName }}
            </p>
        </section>

        <!-- Tabs Menu Bar -->
        <section class="max-w-7xl mx-auto px-6 flex justify-center z-20">
            <div class="flex p-1 bg-slate-100 rounded-2xl overflow-x-auto no-scrollbar scroll-smooth shadow-inner border border-slate-200/50">
                @foreach($tabs as $tab)
                    <button @click="activeTab = '{{ $tab['id'] }}'" 
                            class="px-5 py-3 rounded-xl font-bold text-xs md:text-sm transition duration-200 shrink-0 flex items-center gap-2"
                            :class="activeTab === '{{ $tab['id'] }}' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        <i class="{{ $tab['icon'] }}"></i> {{ $tab['title'] }}
                    </button>
                @endforeach
            </div>
        </section>

        <!-- Chart Tree Section -->
        <section class="max-w-7xl mx-auto px-6 reveal active">
            @foreach($tabs as $tab)
                <div x-show="activeTab === '{{ $tab['id'] }}'" x-transition class="w-full">
                    @if($tab['members']->count() === 0)
                        <div class="text-center py-20 bg-white border border-slate-100 rounded-3xl max-w-md mx-auto shadow-sm p-8">
                            <div class="w-16 h-16 bg-slate-50 text-slate-350 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-slate-100">
                                <i class="{{ $tab['empty_icon'] }} text-slate-400"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 text-sm">อยู่ระหว่างการจัดทำข้อมูล</h4>
                            <p class="text-slate-400 text-xs mt-1">เจ้าหน้าที่กำลังดำเนินการปรับปรุงข้อมูลแผนผังโครงสร้างของศูนย์ฯ</p>
                            <a href="/" class="mt-6 inline-block bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm">กลับหน้าหลัก</a>
                        </div>
                    @else
                        @php
                            // Group members by level and sort keys (level numbers) ascending
                            $groupedByLevel = $tab['members']->groupBy('level')->sortKeys();
                            $levelKeys = $groupedByLevel->keys()->toArray();
                        @endphp

                        <div class="flex flex-col items-center">
                            @foreach($groupedByLevel as $levelNum => $levelMembers)
                                @php
                                    $levelMembers = $levelMembers->sortBy('sort_order');
                                    $count = $levelMembers->count();
                                    $levelIndex = array_search($levelNum, $levelKeys);
                                    $hasNext = isset($levelKeys[$levelIndex + 1]);
                                    
                                    // Determine column widths and styles
                                    $gridColsClass = 'grid-cols-1';
                                    if ($count === 2) {
                                        $gridColsClass = 'sm:grid-cols-2 max-w-2xl';
                                    } elseif ($count === 3) {
                                        $gridColsClass = 'sm:grid-cols-3 max-w-4xl';
                                    } elseif ($count >= 4) {
                                        $gridColsClass = 'sm:grid-cols-2 lg:grid-cols-4 max-w-6xl';
                                    }
                                @endphp

                                <div class="relative w-full pt-8 flex flex-col items-center">
                                    <!-- Horizontal bridge for this Level (if count > 1 and it's not the top level) -->
                                    @if($count > 1 && $levelIndex > 0)
                                        @php
                                            $bridgeOffset = 50 / $count;
                                        @endphp
                                        <div class="absolute top-0 h-0.5 bg-gradient-to-r from-orange-500 via-teal-500 to-orange-500 hidden sm:block line-grow-x" 
                                             style="left: {{ $bridgeOffset }}%; right: {{ $bridgeOffset }}%;"></div>
                                             
                                        <!-- Joint dot where parent line meets this level's bridge -->
                                        <div class="absolute top-0 w-3.5 h-3.5 rounded-full bg-orange-500 border-2 border-white shadow-[0_0_8px_rgba(16,185,129,0.6)] z-20 hidden sm:block dot-appear"
                                             style="left: calc(50% - 7px); top: calc(0% - 7px);"></div>
                                    @endif

                                    <!-- Grid Container -->
                                    <div class="grid {{ $gridColsClass }} gap-8 w-full justify-center">
                                        @foreach($levelMembers as $mIndex => $member)
                                            @php
                                                $isFirstRowLg = $mIndex < 4;
                                                $isFirstRowSm = $mIndex < 2;
                                            @endphp
                                            <div class="relative flex flex-col items-center">
                                                <!-- MOBILE CONNECTOR LINE -->
                                                @if($levelIndex > 0)
                                                    <div class="absolute left-1/2 w-0.5 h-8 bg-orange-500 sm:hidden"
                                                         style="left: calc(50% - 1px); top: -32px; height: 32px;"></div>
                                                @endif

                                                <!-- DESKTOP CONNECTOR LINES & JOINTS -->
                                                @if($levelIndex > 0)
                                                    <div class="absolute w-0.5 bg-teal-500 hidden sm:block line-grow-y-delayed" 
                                                         style="left: calc(50% - 1px); top: -32px; height: 32px;"></div>
                                                    @if($count > 1)
                                                        <!-- Desktop Joint Dot (for first row of cards) -->
                                                        @if($isFirstRowLg)
                                                            <div class="absolute w-2.5 h-2.5 rounded-full bg-teal-400 border-2 border-white shadow-sm z-20 hidden lg:block dot-appear" 
                                                                 style="left: calc(50% - 5px); top: calc(-32px - 5px);"></div>
                                                        @endif
                                                        
                                                        <!-- Tablet Joint Dot (for first row of cards on tablet) -->
                                                        @if($isFirstRowSm)
                                                            <div class="absolute w-2.5 h-2.5 rounded-full bg-teal-400 border-2 border-white shadow-sm z-20 hidden sm:block lg:hidden dot-appear" 
                                                                 style="left: calc(50% - 5px); top: calc(-32px - 5px);"></div>
                                                        @endif
                                                    @endif
                                                @endif

                                                <!-- Card Container -->
                                                @if($levelIndex === 0 && $count === 1)
                                                    <!-- Leader Card (Centered & Larger) -->
                                                    <div class="bg-white border-2 {{ $member->role === 'advisor' ? 'border-purple-500/20 hover:border-purple-500' : 'border-orange-500/20 hover:border-orange-500' }} rounded-[2rem] p-6 shadow-md hover:shadow-xl transition-all duration-300 w-64 text-center space-y-4 transform hover:-translate-y-1 relative z-10">
                                                        {!! renderMemberPhoto($member, 'w-32 h-40') !!}
                                                        <div class="space-y-1">
                                                            <h4 class="font-extrabold text-slate-800 text-sm">{{ $member->name }}</h4>
                                                            {!! renderBadge($member) !!}
                                                            <p class="text-slate-500 text-[10px] pt-1 leading-normal block min-h-[30px]">{{ $member->position }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Regular Card -->
                                                    <div class="bg-white border {{ $member->role === 'advisor' ? 'border-purple-500/20 hover:border-purple-500' : 'border-slate-100 hover:border-orange-500/30' }} rounded-3xl p-5 shadow-sm hover:shadow-xl transition-all duration-300 text-center space-y-4 flex flex-col justify-between w-full max-w-[280px] h-full relative z-10 transform hover:-translate-y-1">
                                                        <div class="space-y-4">
                                                            {!! renderMemberPhoto($member, 'w-28 h-36') !!}
                                                            <div class="space-y-1">
                                                                <h4 class="font-extrabold text-slate-800 text-xs md:text-sm">{{ $member->name }}</h4>
                                                                <span class="text-slate-500 text-[10px] leading-normal block min-h-[30px]">{{ $member->position }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="pt-2 border-t border-slate-50">
                                                            {!! renderBadge($member) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Connecting vertical line down to next level -->
                                @if($hasNext)
                                    <div class="w-0.5 h-12 bg-gradient-to-b from-orange-500 to-teal-500 relative z-0 line-grow-y"></div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </main>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Scroll Reveal intersection observer
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        obs.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>
    @endpush
</x-layout>

