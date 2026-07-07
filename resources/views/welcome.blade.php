@php
    $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
        ? \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all() 
        : [];
    $webName = $settings['web_name'] ?? 'IPST.CHUMPHON';
    $contactEmail = $settings['contact_email'] ?? 'info@anubanchumphon.ac.th';
    $contactPhone = $settings['contact_phone'] ?? '077-511124';
    $contactAddress = $settings['contact_address'] ?? 'โรงเรียนอนุบาลชุมพร ถนนปรมินทรมรรคา ตำบลท่าตะเภา อำเภอเมืองชุมพร จังหวัดชุมพร 86000';
    $statTeachers = $settings['stat_teachers'] ?? '1,200+';
    $statSchools = $settings['stat_schools'] ?? '50+';
    $statDistricts = $settings['stat_districts'] ?? '8+';
    $statCourses = $settings['stat_courses'] ?? '15+';
@endphp

<x-layout>
    <x-slot:title>
        {{ $webName === 'IPST.CHUMPHON' ? 'ศูนย์พัฒนาครู สสวท. จังหวัดชุมพร | โรงเรียนอนุบาลชุมพร' : $webName }}
    </x-slot>

    <!-- Custom Modern Style Definitions -->
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
        .reveal-delay-100 { transition-delay: 100ms; }
        .reveal-delay-200 { transition-delay: 200ms; }
        .reveal-delay-300 { transition-delay: 300ms; }

        /* Custom Scrollbar for horizontal scrolling elements */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        /* Shimmer Loading Animation */
        .shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>

    <main class="space-y-24 md:space-y-32" x-data="courseViewer()" x-init="initCourse()">
        <!-- 1. Hero Image Slider (Alpine.js Powered Carousel) -->
        <section x-data="{ 
            activeSlide: 0, 
            autoplayInterval: null,
            slides: {{ json_encode($slides) }},
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.slides.length;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
            },
            startAutoplay() {
                this.autoplayInterval = setInterval(() => {
                    this.next();
                }, 7000);
            },
            stopAutoplay() {
                clearInterval(this.autoplayInterval);
            }
        }" 
        x-init="startAutoplay()"
        @mouseenter="stopAutoplay()"
        @mouseleave="startAutoplay()"
        class="relative h-[550px] md:h-[600px] lg:h-[650px] w-full overflow-hidden bg-slate-900 z-10">
            
            <!-- Slide Views -->
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="activeSlide === index" 
                     class="absolute inset-0 w-full h-full"
                     x-transition:enter="transition ease-out duration-1000"
                     x-transition:enter-start="opacity-0 scale-105"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-800"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                >
                    <!-- Immersive image with gradient overlays -->
                    <img :src="slide.image" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/60 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                    
                    <!-- Content Container -->
                    <div class="absolute inset-0 flex items-center">
                        <div class="max-w-7xl mx-auto px-6 w-full text-left space-y-6 md:space-y-8">
                            <!-- Badge -->
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-500/20 text-emerald-300 rounded-full text-xs font-bold tracking-wide border border-emerald-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span x-text="slide.badge"></span>
                            </div>
                            
                            <!-- Headings -->
                            <h1 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight max-w-4xl tracking-tight">
                                <span x-text="slide.title"></span> <br>
                                <span class="bg-gradient-to-r from-emerald-400 to-sky-400 text-transparent bg-clip-text" x-text="slide.highlight"></span>
                            </h1>
                            
                            <!-- Slogan/Intro (คำโปรย) -->
                            <p class="text-slate-200 text-sm md:text-lg max-w-2xl leading-relaxed font-medium" x-text="slide.slogan"></p>
                            
                            <!-- Actions -->
                            <div class="flex flex-wrap gap-4 pt-2">
                                <a :href="slide.link" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold text-xs md:text-sm hover:-translate-y-0.5 transition duration-200 shadow-lg shadow-emerald-950/20">
                                    <span x-text="slide.btnText"></span>
                                </a>
                                <a :href="slide.btn2Link" class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-4 rounded-xl font-bold text-xs md:text-sm hover:bg-white/20 hover:-translate-y-0.5 transition duration-200">
                                    <span x-text="slide.btn2Text"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Slide Navigation Buttons -->
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-black/20 hover:bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-full flex items-center justify-center transition hover:scale-105 active:scale-95 group z-30">
                <i class="fa-solid fa-chevron-left group-hover:-translate-x-0.5 transition-transform text-xs md:text-sm"></i>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-black/20 hover:bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-full flex items-center justify-center transition hover:scale-105 active:scale-95 group z-30">
                <i class="fa-solid fa-chevron-right group-hover:translate-x-0.5 transition-transform text-xs md:text-sm"></i>
            </button>

            <!-- Slide Navigation Indicators (positioned to keep visible above overlap) -->
            <div class="absolute bottom-28 left-1/2 -translate-x-1/2 flex gap-2.5 z-30">
                <template x-for="(slide, index) in slides" :key="index">
                    <button @click="activeSlide = index" 
                            class="h-2 rounded-full transition-all duration-300"
                            :class="activeSlide === index ? 'w-8 bg-emerald-400' : 'w-2 bg-white/40 hover:bg-white/60'"
                            :aria-label="'Slide ' + (index + 1)"
                    ></button>
                </template>
            </div>
        </section>

        <!-- 2. Overlapping Statistics Quick-Bar -->
        <section class="relative z-20 -mt-20 md:-mt-24 max-w-7xl mx-auto px-6 reveal">
            <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-slate-100 shadow-[0_15px_40px_rgba(0,0,0,0.04)] p-6 md:p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100/50 flex items-center gap-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="text-left space-y-0.5">
                        <span class="block text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $statTeachers }}</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">ครูผ่านการอบรม</span>
                    </div>
                </div>
                <!-- Stat Card 2 -->
                <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100/50 flex items-center gap-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-sky-50 text-sky-500 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <div class="text-left space-y-0.5">
                        <span class="block text-2xl md:text-3xl font-extrabold text-sky-600 tracking-tight">{{ $statSchools }}</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">โรงเรียนเครือข่าย</span>
                    </div>
                </div>
                <!-- Stat Card 3 -->
                <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100/50 flex items-center gap-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="text-left space-y-0.5">
                        <span class="block text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $statDistricts }}</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">อำเภอครอบคลุม</span>
                    </div>
                </div>
                <!-- Stat Card 4 -->
                <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100/50 flex items-center gap-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="text-left space-y-0.5">
                        <span class="block text-2xl md:text-3xl font-extrabold text-amber-600 tracking-tight">{{ $statCourses }}</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">หลักสูตรจัดอบรม</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. About the Center Section -->
        <section id="about" class="max-w-7xl mx-auto px-6 scroll-mt-28 reveal">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Left Content -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                        บทบาทและเป้าหมาย
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                        บทบาทหน้าที่ของศูนย์ฯ <br>ในจังหวัดชุมพร
                    </h2>
                    <p class="text-slate-500 leading-relaxed text-sm md:text-base">
                        มุ่งเน้นการถ่ายทอดกระบวนการสอนแนวใหม่ โดยใช้หลักสูตรมาตรฐานของ สสวท. เป็นแกนกลาง เพื่อปรับปรุงคุณภาพการจัดการเรียนการสอนวิทยาศาสตร์ คณิตศาสตร์ และคอมพิวเตอร์ระดับประถมศึกษาให้ทัดเทียมมาตรฐานสากล
                    </p>
                    <div class="pt-2">
                        <a href="#contact" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                            ติดต่อสอบถามรายละเอียดเพิ่มเติม <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Right Content (Interactive Tabs) -->
                <div class="lg:col-span-7 space-y-8" x-data="{ currentAboutTab: 'missions' }">
                    <!-- Tab Buttons Menu -->
                    <div class="flex flex-wrap p-1 bg-slate-100 rounded-2xl border border-slate-200/50 shadow-inner w-full sm:w-fit">
                        <button @click="currentAboutTab = 'missions'" 
                                class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200 flex items-center gap-1.5 cursor-pointer"
                                :class="currentAboutTab === 'missions' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                            <i class="fa-solid fa-bullseye"></i> พันธกิจ
                        </button>
                        <button @click="currentAboutTab = 'objectives'" 
                                class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200 flex items-center gap-1.5 cursor-pointer"
                                :class="currentAboutTab === 'objectives' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                            <i class="fa-solid fa-crosshairs"></i> วัตถุประสงค์
                        </button>
                        <button @click="currentAboutTab = 'target'" 
                                class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200 flex items-center gap-1.5 cursor-pointer"
                                :class="currentAboutTab === 'target' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                            <i class="fa-solid fa-users-viewfinder"></i> กลุ่มเป้าหมาย
                        </button>
                    </div>

                    <!-- Panel: Missions -->
                    <div class="space-y-4" x-show="currentAboutTab === 'missions'" x-transition x-cloak>
                        <div class="group relative flex gap-4 p-5 bg-white border border-slate-100 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 hover:border-emerald-500/20 transition duration-300">
                            <span class="absolute top-3 right-5 text-xl font-black text-slate-100 group-hover:text-emerald-100/70 transition duration-300 select-none">01</span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-square-poll-vertical text-base"></i>
                            </div>
                            <div class="text-left space-y-1">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition duration-300">การขับเคลื่อนมาตรฐานของศูนย์ฯ</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">ดำเนินงานและขับเคลื่อน ศูนย์พัฒนาครูและบุคลากรทางการศึกษาของ สสวท. ระดับประถมศึกษา ประจำจังหวัดชุมพร ให้มีประสิทธิภาพตามมาตรฐาน</p>
                            </div>
                        </div>
                        <div class="group relative flex gap-4 p-5 bg-white border border-slate-100 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 hover:border-emerald-500/20 transition duration-300">
                            <span class="absolute top-3 right-5 text-xl font-black text-slate-100 group-hover:text-emerald-100/70 transition duration-300 select-none">02</span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-chalkboard-user text-base"></i>
                            </div>
                            <div class="text-left space-y-1">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition duration-300">ยกระดับสมรรถนะการจัดกิจกรรมการเรียนรู้</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">ส่งเสริมและสนับสนุน ให้ครูผู้สอนและบุคลากรทางการศึกษาได้รับการพัฒนา ยกระดับสมรรถนะการจัดกิจกรรมการเรียนรู้ และนำองค์ความรู้ไปขยายผลในสถานศึกษา</p>
                            </div>
                        </div>
                        <div class="group relative flex gap-4 p-5 bg-white border border-slate-100 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 hover:border-emerald-500/20 transition duration-300">
                            <span class="absolute top-3 right-5 text-xl font-black text-slate-100 group-hover:text-emerald-100/70 transition duration-300 select-none">03</span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-network-wired text-base"></i>
                            </div>
                            <div class="text-left space-y-1">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition duration-300">สร้างสรรค์เครือข่ายวิชาชีพ (PLC)</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">สร้างและพัฒนาเครือข่ายวิชาชีพ ผ่านกระบวนการชุมชนการเรียนรู้เชิงวิชาชีพ (PLC) เพื่อแก้ปัญหาการจัดการเรียนรู้วิทยาศาสตร์ คณิตศาสตร์ และเทคโนโลยี ร่วมกันในระดับพื้นที่</p>
                            </div>
                        </div>
                        <div class="group relative flex gap-4 p-5 bg-white border border-slate-100 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 hover:border-emerald-500/20 transition duration-300">
                            <span class="absolute top-3 right-5 text-xl font-black text-slate-100 group-hover:text-emerald-100/70 transition duration-300 select-none">04</span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-lightbulb text-base"></i>
                            </div>
                            <div class="text-left space-y-1">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition duration-300">ส่งเสริมการวิจัยและเผยแพร่นวัตกรรม</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">ส่งเสริมการวิจัย พัฒนา และเผยแพร่ นวัตกรรมการเรียนรู้ หรือผลงานการปฏิบัติที่เป็นเลิศ (Best Practice) ของครูและบุคลากรทางการศึกษาเพื่อเป็นต้นแบบทางวิชาการ</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Objectives -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" x-show="currentAboutTab === 'objectives'" x-transition x-cloak>
                        <div class="group relative bg-white border border-slate-100 p-6 rounded-2xl shadow-sm space-y-4 hover:border-sky-500/20 hover:shadow-lg hover:-translate-y-1 transition duration-300 text-left">
                            <span class="absolute top-4 right-5 text-xl font-black text-slate-100 group-hover:text-sky-100/70 transition duration-300 select-none">01</span>
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="space-y-1.5">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-sky-700 transition duration-300">พัฒนาครูจัด Active Learning</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">เพื่อพัฒนาศักยภาพครูผู้สอนวิทยาศาสตร์ คณิตศาสตร์ และเทคโนโลยี (ระดับประถมศึกษา) ให้มีความรู้ ความเข้าใจ และสามารถจัดกิจกรรมการเรียนรู้เชิงรุก (Active Learning) ตามแนวทาง สสวท.</p>
                            </div>
                        </div>
                        <div class="group relative bg-white border border-slate-100 p-6 rounded-2xl shadow-sm space-y-4 hover:border-sky-500/20 hover:shadow-lg hover:-translate-y-1 transition duration-300 text-left">
                            <span class="absolute top-4 right-5 text-xl font-black text-slate-100 group-hover:text-sky-100/70 transition duration-300 select-none">02</span>
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div class="space-y-1.5">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-sky-700 transition duration-300">ยกระดับผู้นำสถานศึกษา & AI</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">เพื่อยกระดับสมรรถนะผู้บริหารสถานศึกษาในเครือข่าย ให้มีความเป็นผู้นำทางวิชาการและมีความรู้ความเข้าใจในเทคโนโลยีดิจิทัลสมัยใหม่/ปัญญาประดิษฐ์ (AI) ในการบริหารสถานศึกษา</p>
                            </div>
                        </div>
                        <div class="group relative bg-white border border-slate-100 p-6 rounded-2xl shadow-sm space-y-4 hover:border-sky-500/20 hover:shadow-lg hover:-translate-y-1 transition duration-300 text-left">
                            <span class="absolute top-4 right-5 text-xl font-black text-slate-100 group-hover:text-sky-100/70 transition duration-300 select-none">03</span>
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-diagram-project"></i>
                            </div>
                            <div class="space-y-1.5">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-sky-700 transition duration-300">เชื่อมโยงเครือข่ายความร่วมมือ</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">เพื่อสร้างและเชื่อมโยงเครือข่ายความร่วมมือในการพัฒนาคุณภาพการศึกษาระหว่างโรงเรียนประถมศึกษา 8 อำเภอในจังหวัดชุมพร</p>
                            </div>
                        </div>
                        <div class="group relative bg-white border border-slate-100 p-6 rounded-2xl shadow-sm space-y-4 hover:border-sky-500/20 hover:shadow-lg hover:-translate-y-1 transition duration-300 text-left">
                            <span class="absolute top-4 right-5 text-xl font-black text-slate-100 group-hover:text-sky-100/70 transition duration-300 select-none">04</span>
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-500 group-hover:text-white transition duration-300 shadow-sm">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <div class="space-y-1.5">
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-sky-700 transition duration-300">คลังสื่อและอุปกรณ์การสอน</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">เพื่อเป็นแหล่งเผยแพร่สื่อ อุปกรณ์ และคลังนวัตกรรมการสอนที่สอดคล้องกับหลักสูตรมาตรฐานของ สสวท.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Target Group -->
                    <div class="space-y-6" x-show="currentAboutTab === 'target'" x-transition x-cloak>
                        <div class="bg-gradient-to-br from-indigo-500/5 via-purple-500/5 to-pink-500/5 border border-purple-100/80 p-6 md:p-8 rounded-3xl text-left space-y-6 shadow-sm hover:shadow-md transition duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shrink-0 shadow-inner">
                                    <i class="fa-solid fa-users-viewfinder"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-slate-800 text-sm md:text-base">กลุ่มเป้าหมายหลักของศูนย์พัฒนาครูฯ</h4>
                                    <p class="text-[10px] text-purple-500 font-bold uppercase tracking-wider">Target Group Details</p>
                                </div>
                            </div>
                            
                            <p class="text-slate-600 text-sm leading-relaxed font-medium">
                                ผู้บริหารสถานศึกษา, รองผู้อำนวยการฝ่ายวิชาการ, และครูผู้สอนแกนนำในกลุ่มสาระการเรียนรู้วิทยาศาสตร์และเทคโนโลยี และกลุ่มสาระการเรียนรู้คณิตศาสตร์ ของโรงเรียนเครือข่ายประถมศึกษาในจังหวัดชุมพร (ครอบคลุมโรงเรียนเครือข่ายประถมศึกษาทั่วทั้ง 8 อำเภอ)
                            </p>

                            <div class="border-t border-slate-200/60 pt-6">
                                <h5 class="text-xs font-bold text-slate-700 mb-4 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-nodes text-purple-500"></i> โครงสร้างการแบ่งประเภทกลุ่มเป้าหมาย
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-white/80 backdrop-blur-sm border border-slate-100 p-4 rounded-2xl flex items-start gap-3 shadow-sm hover:border-purple-300/40 transition duration-300">
                                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-sm">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </div>
                                        <div class="space-y-0.5">
                                            <span class="block font-bold text-xs text-slate-800">ฝ่ายบริหารวิชาการ</span>
                                            <span class="block text-[11px] text-slate-500 leading-normal">ผู้บริหารสถานศึกษา และรองผู้อำนวยการฝ่ายวิชาการ</span>
                                        </div>
                                    </div>
                                    <div class="bg-white/80 backdrop-blur-sm border border-slate-100 p-4 rounded-2xl flex items-start gap-3 shadow-sm hover:border-purple-300/40 transition duration-300">
                                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-sm">
                                            <i class="fa-solid fa-book-open-reader"></i>
                                        </div>
                                        <div class="space-y-0.5">
                                            <span class="block font-bold text-xs text-slate-800">กลุ่มครูแกนนำ</span>
                                            <span class="block text-[11px] text-slate-500 leading-normal">ครูแกนนำกลุ่มสาระวิทย์ เทคโนโลยี และคณิตศาสตร์</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-2xl flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 text-lg shadow-sm animate-pulse">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <div class="space-y-0.5">
                                    <span class="block font-extrabold text-xs text-emerald-800">ขอบเขตพื้นที่ให้บริการ</span>
                                    <span class="block text-xs text-emerald-700 font-semibold">ครอบคลุมโรงเรียนเครือข่ายประถมศึกษาทั่วทั้ง 8 อำเภอของจังหวัดชุมพร</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Academic Courses Section (Dynamic LMS Grid) -->
        <section class="max-w-7xl mx-auto px-6 scroll-mt-28 reveal" id="courses">
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-4">
                <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                    หลักสูตรประจำปี 2569
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                    หลักสูตรฝึกอบรมที่เปิดรับสมัคร
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    เพิ่มพูนความรู้ ความเข้าใจ และเก็บชั่วโมงพัฒนาวิชาชีพด้วยหลักสูตรการสอนมาตรฐานจาก สสวท.
                </p>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center mb-8 bg-white p-4 border border-slate-100 rounded-2xl shadow-sm">
                <!-- Search Box -->
                <div class="relative w-full md:max-w-xs">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="ค้นหาชื่อคอร์ส..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-555/20 focus:border-emerald-500 transition-all duration-200">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>

                <!-- Tabs filtering courses -->
                <div class="flex p-1 bg-slate-100 rounded-xl w-full md:w-auto overflow-x-auto no-scrollbar">
                    <button @click="statusFilter = 'all'" 
                            class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                            :class="statusFilter === 'all' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        ทั้งหมด
                    </button>
                    <button @click="statusFilter = 'open'" 
                            class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                            :class="statusFilter === 'open' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        เปิดรับสมัคร
                    </button>
                    <button @click="statusFilter = 'ongoing'" 
                            class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                            :class="statusFilter === 'ongoing' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        อยู่ระหว่างดำเนินการ
                    </button>
                    <button @click="statusFilter = 'closed'" 
                            class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                            :class="statusFilter === 'closed' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        เสร็จสิ้นโครงการ
                    </button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div x-show="loading" class="text-center py-20">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
                <p class="text-slate-400 text-xs font-bold">กำลังโหลดข้อมูลหลักสูตรอบรม...</p>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && filteredCourses().length === 0" class="text-center py-16 bg-white border border-slate-100 rounded-3xl p-8" x-cloak>
                <div class="w-16 h-16 bg-slate-50 text-slate-350 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-slate-100">
                    <i class="fa-solid fa-folder-open text-slate-400"></i>
                </div>
                <h4 class="font-bold text-slate-700 text-sm">ไม่พบข้อมูลหลักสูตรที่ค้นหา</h4>
                <p class="text-slate-400 text-xs mt-1">กรุณาลองปรับเปลี่ยนเงื่อนไขการค้นหาหรือแท็บที่ใช้กรองข้อมูล</p>
            </div>

            <!-- Dynamic Courses Grid -->
            <div x-show="!loading && filteredCourses().length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" x-cloak x-transition>
                <template x-for="course in filteredCourses()" :key="course.id">
                    <div class="group bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:border-slate-200/80 transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-1">
                        <div>
                            <!-- Cover Image -->
                            <div class="w-full h-48 bg-slate-50 border-b border-slate-100 relative overflow-hidden flex items-center justify-center shrink-0">
                                <img :src="course.cover_image_url || getFallbackImage(course.title)" alt="Cover" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <!-- Status Badge -->
                                <div class="absolute top-4 left-4">
                                    <template x-if="course.status === 'open'">
                                        <span class="px-2.5 py-1.5 bg-emerald-500 text-white font-bold rounded-xl text-[10px] shadow-sm tracking-wide">เปิดรับสมัคร</span>
                                    </template>
                                    <template x-if="course.status === 'ongoing'">
                                        <span class="px-2.5 py-1.5 bg-sky-500 text-white font-bold rounded-xl text-[10px] shadow-sm tracking-wide">กำลังดำเนินการ</span>
                                    </template>
                                    <template x-if="course.status === 'upcoming'">
                                        <span class="px-2.5 py-1.5 bg-amber-500 text-white font-bold rounded-xl text-[10px] shadow-sm tracking-wide">เตรียมเปิดสมัคร</span>
                                    </template>
                                    <template x-if="course.status === 'closed'">
                                        <span class="px-2.5 py-1.5 bg-slate-600 text-white font-bold rounded-xl text-[10px] shadow-sm tracking-wide">เสร็จสิ้นโครงการ</span>
                                    </template>
                                </div>

                                <!-- Hours Badge -->
                                <div class="absolute bottom-4 right-4">
                                    <span class="px-2 py-1 bg-slate-950/60 backdrop-blur-md text-emerald-300 font-bold rounded-lg text-[10px] border border-white/10" x-text="course.hours ? course.hours + ' ชั่วโมงอบรม' : 'ไม่มีชั่วโมงพัฒนา'"></span>
                                </div>
                            </div>

                            <!-- Content Details -->
                            <div class="p-6 md:p-8 space-y-4 text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider" x-text="course.duration_text || 'กำหนดการจะแจ้งให้ทราบภายหลัง'"></span>
                                    <template x-if="course.academic_year">
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded text-[9px] tracking-wide shrink-0" x-text="'ปีการศึกษา ' + course.academic_year"></span>
                                    </template>
                                </div>
                                <h4 class="text-base md:text-lg font-bold text-slate-800 leading-snug line-clamp-2 group-hover:text-emerald-600 transition-colors" x-text="course.title"></h4>
                                <p class="text-slate-500 text-xs leading-relaxed line-clamp-3" x-text="course.objectives || 'ไม่มีรายละเอียดเนื้อหาเบื้องต้นในระบบ'"></p>
                                
                                <hr class="border-slate-50">
                                
                                <!-- Meta fields -->
                                <div class="space-y-2 text-xs text-slate-400">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-group text-slate-350 w-4"></i>
                                        <span>กลุ่มเป้าหมาย: <strong class="text-slate-600" x-text="course.target_group || 'ครูและบุคลากรทางการศึกษา'"></strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-location-dot text-slate-355 w-4"></i>
                                        <span>สถานที่: <strong class="text-slate-600 truncate max-w-[200px]" x-text="course.location || 'ศูนย์พัฒนาครู สสวท. ชุมพร'"></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="px-6 md:px-8 pb-6 md:pb-8 pt-4 border-t border-slate-50 mt-auto flex gap-3 items-center">
                            <a :href="'/courses/' + course.id" class="flex-1 text-center py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-xl transition duration-200">
                                รายละเอียดเพิ่มเติม
                            </a>
                            <template x-if="course.status === 'open' && course.registration_link">
                                <a :href="course.registration_link" target="_blank" class="flex-1 text-center py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-100 hover:shadow-lg transition duration-200 flex items-center justify-center gap-1">
                                    ลงทะเบียนเข้าร่วม <i class="fa-solid fa-chevron-right text-[8px]"></i>
                                </a>
                            </template>
                            <template x-if="course.status !== 'open' || !course.registration_link">
                                <button disabled class="flex-1 text-center py-3 bg-slate-100 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed">
                                    ยังไม่เปิดสมัคร
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>


        </section>

        <!-- 5. Network Schools Section -->
        <section id="schools" class="py-20 bg-slate-50/60 border-t border-b border-slate-100 rounded-3xl scroll-mt-28 reveal">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center max-w-2xl mx-auto mb-10 space-y-4">
                    <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                        เครือข่ายโรงเรียน
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                        โรงเรียนเครือข่ายพัฒนาครู จังหวัดชุมพร
                    </h2>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        ร่วมขับเคลื่อนกระบวนการจัดการเรียนการสอนวิทยาศาสตร์และคณิตศาสตร์ ครอบคลุมทั่วถึง 8 อำเภอในจังหวัดชุมพร
                    </p>
                </div>

                <!-- Interactive Filters & School Search Box -->
                <div class="mb-10 space-y-4 bg-white p-5 border border-slate-100 rounded-2xl shadow-sm max-w-4xl mx-auto">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <!-- Search input box -->
                        <div class="relative w-full md:max-w-xs shrink-0">
                            <input type="text" 
                                   id="school-search" 
                                   placeholder="ค้นหาชื่อโรงเรียน..." 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>

                        <!-- Indicator count tag -->
                        <span id="school-count" class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/50">
                            กำลังโหลดข้อมูลโรงเรียน...
                        </span>
                    </div>

                    <!-- Scrollable Districts filter buttons bar -->
                    <div class="border-t border-slate-100 pt-4 flex gap-1.5 overflow-x-auto no-scrollbar scroll-smooth">
                        <button data-district="all" class="district-btn px-4 py-2 rounded-xl font-bold text-xs transition duration-200 shrink-0 bg-emerald-600 text-white shadow-sm">
                            ทั้งหมด
                        </button>
                        @php
                            $zones = ["อำเภอเมืองชุมพร", "อำเภอท่าแซะ", "อำเภอปะทิว"];
                        @endphp
                        @foreach ($zones as $zone)
                            <button data-district="{{ $zone }}" class="district-btn px-4 py-2 rounded-xl font-bold text-xs transition duration-200 shrink-0 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                {{ $zone }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Dynamic Schools Cards Grid Container -->
                <div id="school-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 transition-opacity duration-300 min-h-[120px]">
                    <!-- Rendered dynamically by script below -->
                </div>
            </div>
        </section>

        <!-- 6. Documents Section -->
        <section id="documents-home" class="max-w-7xl mx-auto px-6 scroll-mt-28 reveal">
            <!-- Section Header -->
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-4">
                <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                    คลังเอกสารดาวน์โหลด
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                    เอกสารเผยแพร่ล่าสุด
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    คู่มือ สื่อการสอน แบบฟอร์ม และรายงานกิจกรรมของศูนย์พัฒนาครู สสวท. จังหวัดชุมพร
                </p>
            </div>

            <!-- Loading spinner for documents -->
            <div id="document-loading" class="text-center py-10">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-emerald-500 border-t-transparent mb-2"></div>
                <p class="text-slate-400 text-[10px] font-bold">กำลังดาวน์โหลดเอกสาร...</p>
            </div>

            <!-- Empty state for documents -->
            <div id="document-empty" class="hidden text-center py-10 bg-white border border-slate-100 rounded-3xl p-8 max-w-md mx-auto shadow-sm">
                <p class="text-slate-400 text-xs">ยังไม่มีเอกสารเผยแพร่ในคลังข้อมูล</p>
            </div>

            <!-- Documents list grid -->
            <div id="document-container" class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto transition-opacity duration-300">
                <!-- Rendered dynamically by script below -->
            </div>

            <!-- View All Button -->
            <div class="text-center mt-10">
                <a href="/documents" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs py-3.5 px-7 rounded-xl shadow-md transition duration-200 hover:-translate-y-0.5 transform">
                    ดูคลังเอกสารทั้งหมด <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </section>

        <!-- 7. Contact Section -->
        <section id="contact" class="max-w-7xl mx-auto px-6 scroll-mt-28 reveal">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                <!-- Left: Contact Details Cards -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                        ข้อมูลการติดต่อ
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                        ติดต่อประสานงานวิชาการ
                    </h2>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        ยินดีให้บริการข้อมูลเกี่ยวกับการจัดอบรม การประสานงานวิชาการ หรือข้อสงสัยเกี่ยวกับโครงการของทางศูนย์พัฒนาครู สสวท. จังหวัดชุมพร
                    </p>
                    
                    <div class="space-y-4 pt-4">
                        <div class="bg-white p-5 border border-slate-100 rounded-2xl shadow-sm flex gap-4 hover:-translate-y-0.5 transition duration-300">
                            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 flex items-center justify-center rounded-xl shadow-inner shrink-0 text-base">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <div class="space-y-1 text-left">
                                <h4 class="font-bold text-slate-800 text-xs md:text-sm">ที่ตั้งศูนย์พัฒนาครู</h4>
                                <p class="text-xs text-slate-450 leading-relaxed text-slate-500">
                                    {{ $contactAddress }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white p-5 border border-slate-100 rounded-2xl shadow-sm flex gap-4 hover:-translate-y-0.5 transition duration-300">
                            <div class="w-10 h-10 bg-sky-50 text-sky-500 flex items-center justify-center rounded-xl shadow-inner shrink-0 text-base">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="space-y-1 text-left">
                                <h4 class="font-bold text-slate-800 text-xs md:text-sm">เบอร์โทรศัพท์ติดต่อ</h4>
                                <p class="text-xs text-slate-450 text-slate-500">
                                    {{ $contactPhone }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white p-5 border border-slate-100 rounded-2xl shadow-sm flex gap-4 hover:-translate-y-0.5 transition duration-300">
                            <div class="w-10 h-10 bg-purple-50 text-purple-500 flex items-center justify-center rounded-xl shadow-inner shrink-0 text-base">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div class="space-y-1 text-left">
                                <h4 class="font-bold text-slate-800 text-xs md:text-sm">อีเมลติดต่อฝ่ายวิชาการ</h4>
                                <p class="text-xs text-slate-450 text-slate-500">
                                    {{ $contactEmail }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Contact Form -->
                <div class="lg:col-span-7">
                    <div class="bg-white border border-slate-100 p-8 md:p-10 rounded-3xl shadow-sm">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <i class="fa-regular fa-paper-plane text-emerald-500"></i> ส่งข้อความถึงฝ่ายประสานงาน
                        </h3>
                        <form x-data="{ submitted: false }" @submit.prevent="submitted = true; $el.reset()" class="space-y-5">
                            <div x-show="submitted" class="p-4 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-100 flex items-center gap-2" x-cloak>
                                <i class="fa-solid fa-circle-check text-emerald-500 text-base animate-bounce"></i>
                                ส่งข้อความเรียบร้อยแล้ว! เจ้าหน้าที่วิชาการจะตอบกลับทางอีเมลประสานงานโดยเร็วที่สุด
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5 text-left">
                                    <label class="text-xs font-bold text-slate-500">ชื่อ-นามสกุล ผู้ติดต่อ</label>
                                    <input type="text" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="ชื่อ นามสกุล ของท่าน">
                                </div>
                                <div class="space-y-1.5 text-left">
                                    <label class="text-xs font-bold text-slate-500">ที่อยู่อีเมลประสานงาน</label>
                                    <input type="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="example@email.com">
                                </div>
                            </div>
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500">เรื่องที่ต้องการประสานงาน</label>
                                <input type="text" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="ระบุหัวข้อเรื่องการติดต่อ">
                            </div>
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500">รายละเอียดข้อมูลเพิ่มเติม</label>
                                <textarea required rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" placeholder="พิมพ์ข้อความรายละเอียดที่นี่..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 px-6 rounded-xl hover:bg-slate-800 transition duration-200 text-xs shadow-sm">
                                ส่งข้อความประสานงาน
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
    <script>
        // 1. Alpine.js Course Viewer Component
        function courseViewer() {
            return {
                loading: true,
                courses: [],
                searchQuery: '',
                statusFilter: 'all',

                initCourse() {
                    this.fetchCourses();
                },

                fetchCourses() {
                    this.loading = true;
                    axios.get('{{ route('api.courses.list') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.courses = response.data.data;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching courses:', error);
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                // Filter logic
                filteredCourses() {
                    return this.courses.filter(course => {
                        const searchLower = this.searchQuery.toLowerCase();
                        const matchesSearch = 
                            course.title.toLowerCase().includes(searchLower) ||
                            (course.objectives && course.objectives.toLowerCase().includes(searchLower)) ||
                            (course.target_group && course.target_group.toLowerCase().includes(searchLower)) ||
                            (course.location && course.location.toLowerCase().includes(searchLower));

                        if (this.statusFilter === 'all') return matchesSearch;
                        if (this.statusFilter === 'open') return matchesSearch && course.status === 'open';
                        if (this.statusFilter === 'ongoing') return matchesSearch && (course.status === 'ongoing' || course.status === 'upcoming');
                        if (this.statusFilter === 'closed') return matchesSearch && course.status === 'closed';

                        return matchesSearch;
                    });
                },

                // Fallback Images depending on Title keywords
                getFallbackImage(title) {
                    if (!title) return 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=800&q=80';
                    
                    const lower = title.toLowerCase();
                    if (lower.includes('ai') || lower.includes('ปัญญาประดิษฐ์') || lower.includes('คอมพิวเตอร์') || lower.includes('เทคโนโลยี') || lower.includes('ดิจิทัล')) {
                        return 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=800&q=80'; // AI / modern tech
                    }
                    if (lower.includes('วิทย') || lower.includes('โลก') || lower.includes(' climate') || lower.includes('สิ่งแวดล้อม') || lower.includes('สเต็ม') || lower.includes('stem')) {
                        return 'https://images.unsplash.com/photo-1507668077129-56e32842fceb?auto=format&fit=crop&w=800&q=80'; // Science / learning
                    }
                    if (lower.includes('คณิต') || lower.includes('คำนวณ') || lower.includes('วิเคราะห์') || lower.includes('คิด') || lower.includes('pisa')) {
                        return 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=800&q=80'; // Mathematical logic
                    }
                    return 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=800&q=80'; // general book banner
                }
            };
        }

        // 2. Vanilla School Listing, Filter & Search
        (function() { 
            let schoolData = [];
            let currentDistrict = 'all';
            let searchQuery = '';

            function render() {
                const container = document.getElementById('school-container');
                const buttons = document.querySelectorAll('.district-btn');
                const countEl = document.getElementById('school-count');
                if (!container) return;

                // Update Filter buttons visual state
                buttons.forEach(btn => {
                    if (btn.getAttribute('data-district') === currentDistrict) {
                        btn.className = "district-btn px-4 py-2 rounded-xl font-bold text-xs transition duration-200 shrink-0 bg-emerald-600 text-white shadow-sm";
                    } else {
                        btn.className = "district-btn px-4 py-2 rounded-xl font-bold text-xs transition duration-200 shrink-0 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800";
                    }
                });

                // Apply dynamic filters
                const filtered = schoolData.filter(s => {
                    const matchesDistrict = (currentDistrict === 'all' || s.district === currentDistrict);
                    const matchesSearch = s.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                          s.district.toLowerCase().includes(searchQuery.toLowerCase());
                    return matchesDistrict && matchesSearch;
                });
                
                // Update school count indicator
                if (countEl) {
                    countEl.textContent = `พบทั้งหมด ${filtered.length} โรงเรียน`;
                }

                container.style.opacity = '0';
                setTimeout(() => {
                    if (filtered.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full py-16 text-center text-slate-400 font-medium bg-white rounded-2xl border border-slate-100 p-8 shadow-sm">
                                <div class="mb-3 text-3xl">🏫</div>
                                <h5 class="font-bold text-slate-650 text-xs text-slate-500">ไม่มีข้อมูลโรงเรียนแกนนำในเงื่อนไขการค้นหานี้</h5>
                                <p class="text-[10px] text-slate-400 mt-1">กรุณาลองพิมพ์ข้อความค้นหาใหม่หรือปรับเปลี่ยนเขตอำเภอ</p>
                            </div>
                        `;
                    } else {
                        container.innerHTML = filtered.map(school => {
                            const hasWeb = !!school.website;
                            const tagOpen = hasWeb ? `<a href="${school.website}" target="_blank"` : `<div`;
                            const tagClose = hasWeb ? `</a>` : `</div>`;
                            const hoverClasses = hasWeb 
                                ? 'hover:border-sky-500/40 hover:shadow-sky-100 hover:shadow-md cursor-pointer' 
                                : 'hover:border-emerald-500/25 hover:shadow-md';
                            
                            // Emulate a school letter shield icon logo if database logo path is empty
                            const firstLetter = school.name.replace("โรงเรียน", "").trim().charAt(0);
                            
                            // Random gradient class depending on the school's ID to keep aesthetics high
                            const gradients = [
                                'from-emerald-400 to-teal-500',
                                'from-sky-400 to-blue-500',
                                'from-indigo-400 to-violet-500',
                                'from-amber-400 to-orange-500'
                            ];
                            const selectGradient = gradients[school.id % gradients.length];
                            
                            const logoEl = school.logo_url 
                                ? `<img src="${school.logo_url}" alt="${school.name} Logo" class="max-w-full max-h-full object-contain">`
                                : `<div class="w-full h-full bg-gradient-to-br ${selectGradient} text-white flex items-center justify-center font-bold text-xs shadow-inner uppercase">${firstLetter}</div>`;

                            return `
                                ${tagOpen} class="bg-white p-5 rounded-2xl border border-slate-100 ${hoverClasses} transition duration-300 group flex items-start gap-4 transform hover:-translate-y-0.5">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-sm shrink-0 overflow-hidden group-hover:scale-105 transition duration-300">
                                        ${logoEl}
                                    </div>
                                    <div class="space-y-0.5 overflow-hidden text-left flex-1">
                                        <h4 class="font-bold text-slate-800 text-xs md:text-sm leading-snug truncate" title="${school.name}">${school.name}</h4>
                                        <p class="text-[9px] text-emerald-600 font-semibold uppercase tracking-wider">${school.district}</p>
                                        <div class="flex items-center gap-1.5 pt-1">
                                            <span class="px-1.5 py-0.5 bg-slate-50 text-slate-400 border border-slate-100 rounded text-[8px] font-bold flex items-center gap-0.5">
                                                <i class="fa-solid fa-shield-halved text-emerald-500/80"></i> แกนนำ
                                            </span>
                                            ${hasWeb ? `<span class="px-1.5 py-0.5 bg-sky-50 text-sky-600 rounded text-[8px] font-bold flex items-center gap-0.5 hover:bg-sky-100">
                                                ลิงก์เว็บ <i class="fa-solid fa-arrow-up-right-from-square text-[6px]"></i>
                                            </span>` : ''}
                                        </div>
                                    </div>
                                ${tagClose}
                            `;
                        }).join('');
                    }
                    container.style.opacity = '1';
                }, 150);
            }

            function fetchSchools() {
                axios.get('{{ route('api.schools.list') }}')
                    .then(response => {
                        if (response.data.status === 'success') {
                            schoolData = response.data.data;
                            render();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching schools:', error);
                        const countEl = document.getElementById('school-count');
                        if (countEl) countEl.textContent = 'ไม่สามารถดาวน์โหลดข้อมูลโรงเรียนได้';
                    });
            }

            // Input Event listener for live searching
            const searchInput = document.getElementById('school-search');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value;
                    render();
                });
            }

            // Button Event listeners for district filters
            document.querySelectorAll('.district-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentDistrict = btn.getAttribute('data-district');
                    render();
                });
            });

            // 2.2 Documents Listing
            let documentData = [];

            function renderDocuments() {
                const container = document.getElementById('document-container');
                const loadingEl = document.getElementById('document-loading');
                const emptyEl = document.getElementById('document-empty');
                if (!container) return;

                if (loadingEl) loadingEl.style.display = 'none';

                if (documentData.length === 0) {
                    if (emptyEl) emptyEl.classList.remove('hidden');
                    return;
                }

                container.innerHTML = documentData.map(doc => {
                    // Get file type badge classes
                    let bgClass = 'bg-slate-500 shadow-slate-100';
                    let iconClass = 'fa-regular fa-file';
                    const ext = (doc.file_type || '').toLowerCase();
                    if (ext === 'pdf') { bgClass = 'bg-rose-500 shadow-rose-100'; iconClass = 'fa-regular fa-file-pdf'; }
                    else if (['doc', 'docx'].includes(ext)) { bgClass = 'bg-blue-500 shadow-blue-100'; iconClass = 'fa-regular fa-file-word'; }
                    else if (['xls', 'xlsx'].includes(ext)) { bgClass = 'bg-emerald-600 shadow-emerald-100'; iconClass = 'fa-regular fa-file-excel'; }
                    else if (['ppt', 'pptx'].includes(ext)) { bgClass = 'bg-orange-500 shadow-orange-100'; iconClass = 'fa-regular fa-file-powerpoint'; }
                    else if (['zip', 'rar', '7z'].includes(ext)) { bgClass = 'bg-purple-500 shadow-purple-100'; iconClass = 'fa-regular fa-file-zipper'; }

                    return `
                        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-emerald-500/20 transition duration-300 flex items-start gap-4 transform hover:-translate-y-0.5">
                            <div class="w-12 h-12 rounded-xl ${bgClass} text-white flex flex-col items-center justify-center font-extrabold text-[9px] uppercase shadow-sm shrink-0">
                                <i class="${iconClass} text-base mb-0.5"></i>
                                <span>${doc.file_type || 'FILE'}</span>
                            </div>
                            <div class="space-y-1 overflow-hidden text-left flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-extrabold text-slate-800 text-xs md:text-sm leading-snug truncate" title="${doc.title}">${doc.title}</h4>
                                    <p class="text-slate-400 text-[10px] mt-0.5 line-clamp-2">${doc.description || 'ไม่มีรายละเอียดเพิ่มเติม'}</p>
                                </div>
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-50">
                                    <div class="flex gap-3 text-[9px] text-slate-400 font-semibold">
                                        <span>ขนาด: ${doc.file_size || 'N/A'}</span>
                                        <span>ดาวน์โหลด: ${doc.download_count} ครั้ง</span>
                                    </div>
                                    <a href="/documents/download/${doc.id}" class="text-[10px] font-extrabold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1 cursor-pointer">
                                        ดาวน์โหลด <i class="fa-solid fa-cloud-arrow-down text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function fetchDocuments() {
                axios.get('/api/documents')
                    .then(response => {
                        if (response.data.status === 'success') {
                            documentData = response.data.data;
                            renderDocuments();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching documents:', error);
                        const container = document.getElementById('document-container');
                        if (container) {
                            container.innerHTML = `<p class="col-span-full text-center text-xs text-rose-500 font-bold">ไม่สามารถโหลดข้อมูลเอกสารได้</p>`;
                        }
                    });
            }

            // Initial trigger after DOM/Vite loaded to ensure window.axios exists
            window.addEventListener('load', () => {
                fetchSchools();
                fetchDocuments();

                // 3. Scroll Reveal intersection observer
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
        })();
    </script>
    @endpush
</x-layout>
