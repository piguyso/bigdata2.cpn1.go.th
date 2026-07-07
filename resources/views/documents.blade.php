<x-layout>
    <x-slot:title>คลังเอกสารเผยแพร่ | EE CPN1</x-slot>

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
    </style>

    <main class="py-16 md:py-24 space-y-12">
        <!-- Header Section -->
        <section class="max-w-7xl mx-auto px-6 text-center space-y-4">
            <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider">
                เอกสารเผยแพร่
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                คลังเอกสารและแบบฟอร์ม
            </h2>
            <p class="text-slate-500 text-sm max-w-2xl mx-auto leading-relaxed">
                ดาวน์โหลดคู่มือการอบรม แผนการสอน แบบฟอร์มอิเล็กทรอนิกส์ และเอกสารวิชาการต่างๆ ของศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1
            </p>
        </section>

        <!-- Search and Documents Library -->
        <section class="max-w-5xl mx-auto px-6 reveal active" x-data="documentLibrary()">
            <!-- Search bar -->
            <div class="mb-10 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" x-model="searchQuery" 
                       class="w-full bg-white border border-slate-200/80 rounded-2xl pl-12 pr-4 py-4 text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-sm placeholder:text-slate-400 text-slate-800" 
                       placeholder="ค้นหาเอกสาร หรือหัวข้อวิชาการที่ต้องการดาวน์โหลด...">
            </div>

            <!-- Documents Grid/List -->
            <div class="space-y-4">
                @if($documents->count() === 0)
                    <div class="text-center py-20 bg-white border border-slate-100 rounded-3xl max-w-md mx-auto shadow-sm p-8">
                        <div class="w-16 h-16 bg-slate-50 text-slate-350 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-slate-100">
                            <i class="fa-regular fa-folder-open text-slate-400"></i>
                        </div>
                        <h4 class="font-bold text-slate-700 text-sm">ยังไม่มีเอกสารเผยแพร่</h4>
                        <p class="text-slate-400 text-xs mt-1">ศูนย์ฯ กำลังจัดเตรียมและอัปโหลดเอกสารวิชาการเข้าระบบ</p>
                        <a href="/" class="mt-6 inline-block bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm">กลับหน้าหลัก</a>
                    </div>
                @else
                    <!-- Local Documents Array for client-side search -->
                    <div x-show="filteredDocs.length === 0" class="text-center py-12 bg-white rounded-3xl border border-slate-100 shadow-sm" x-cloak>
                        <p class="text-slate-450 text-xs font-bold text-slate-400">ไม่พบเอกสารที่ตรงกับการค้นหา</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <template x-for="doc in filteredDocs" :key="doc.id">
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 md:p-6 shadow-sm hover:shadow-md hover:border-emerald-500/20 transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 transform hover:-translate-y-0.5">
                                <div class="flex items-start gap-4">
                                    <!-- File Icon Badge -->
                                    <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center font-extrabold text-[9px] uppercase text-white shadow-sm shrink-0"
                                         :class="getFileTypeClass(doc.file_type)">
                                        <i class="text-base mb-0.5" :class="getFileIcon(doc.file_type)"></i>
                                        <span x-text="doc.file_type || 'FILE'"></span>
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="font-extrabold text-slate-800 text-sm md:text-base leading-snug" x-text="doc.title"></h3>
                                        <p class="text-slate-500 text-xs leading-relaxed" x-text="doc.description || 'ไม่มีรายละเอียดเพิ่มเติม'"></p>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-[10px] text-slate-400 font-semibold">
                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-hard-drive"></i>
                                                <span x-text="'ขนาด: ' + (doc.file_size || 'N/A')"></span>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-clock"></i>
                                                <span x-text="'อัปเดตเมื่อ: ' + formatDate(doc.updated_at)"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-6 border-t sm:border-t-0 border-slate-50 pt-3 sm:pt-0 shrink-0">
                                    <div class="text-left sm:text-right shrink-0">
                                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide">ยอดดาวน์โหลด</span>
                                        <span class="block text-sm font-extrabold text-emerald-600" x-text="doc.download_count + ' ครั้ง'"></span>
                                    </div>
                                    <a :href="'/documents/download/' + doc.id" 
                                       class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-lg shadow-emerald-100 hover:shadow-emerald-200 transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto hover:-translate-y-0.5 duration-200">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        ดาวน์โหลด
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif
            </div>
        </section>
    </main>

    @push('scripts')
    <script>
        function documentLibrary() {
            return {
                searchQuery: '',
                rawDocs: @json($documents),
                
                get filteredDocs() {
                    if (!this.searchQuery.trim()) {
                        return this.rawDocs;
                    }
                    const query = this.searchQuery.toLowerCase();
                    return this.rawDocs.filter(d => 
                        d.title.toLowerCase().includes(query) || 
                        (d.description && d.description.toLowerCase().includes(query))
                    );
                },

                getFileTypeClass(type) {
                    const ext = (type || '').toLowerCase();
                    if (['pdf'].includes(ext)) return 'bg-rose-500 shadow-rose-100';
                    if (['doc', 'docx'].includes(ext)) return 'bg-blue-500 shadow-blue-100';
                    if (['xls', 'xlsx'].includes(ext)) return 'bg-emerald-600 shadow-emerald-100';
                    if (['ppt', 'pptx'].includes(ext)) return 'bg-orange-500 shadow-orange-100';
                    if (['zip', 'rar', '7z'].includes(ext)) return 'bg-purple-500 shadow-purple-100';
                    return 'bg-slate-500 shadow-slate-100';
                },

                getFileIcon(type) {
                    const ext = (type || '').toLowerCase();
                    if (['pdf'].includes(ext)) return 'fa-regular fa-file-pdf';
                    if (['doc', 'docx'].includes(ext)) return 'fa-regular fa-file-word';
                    if (['xls', 'xlsx'].includes(ext)) return 'fa-regular fa-file-excel';
                    if (['ppt', 'pptx'].includes(ext)) return 'fa-regular fa-file-powerpoint';
                    if (['zip', 'rar', '7z'].includes(ext)) return 'fa-regular fa-file-zipper';
                    return 'fa-regular fa-file';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    return window.formatThaiDate(dateStr);
                }
            };
        }

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

