<x-layout>
    <x-slot:title>ตรวจประเมินผลงานส่ง LMS | การตั้งค่าระบบ</x-slot>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="lmsSubmissionsAdmin()" x-init="init()">
        
        <!-- Toast alert -->
        <div
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0"
            :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
            class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
            x-cloak
        >
            <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
            <span x-text="toast.message"></span>
        </div>

        <!-- Admin Header -->
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">ระบบจัดการข้อมูล LMS</h2>
                <p class="text-slate-500 text-xs mt-1">ตั้งค่าหลักสูตรอบรม, บทเรียน, แบบทดสอบ และอนุมัติงานส่งของนักเรียน</p>
            </div>
        </header>

        <!-- Sub-navigation Tabs -->
        <div class="border-b border-slate-200 mb-8 flex flex-wrap gap-1">
            <a href="{{ route('admin.lms.courses.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-graduation-cap mr-1"></i> 1. หลักสูตรทั้งหมด
            </a>
            <a href="{{ route('admin.lms.lessons.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-book-open mr-1"></i> 2. จัดการบทเรียน
            </a>
            <a href="{{ route('admin.lms.quizzes.index') }}" class="px-5 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-bold text-xs transition">
                <i class="fa-solid fa-clipboard-question mr-1"></i> 3. จัดการแบบทดสอบ
            </a>
            <a href="{{ route('admin.lms.submissions.index') }}" class="px-5 py-3 border-b-2 border-indigo-600 text-indigo-600 font-extrabold text-xs">
                <i class="fa-solid fa-file-signature mr-1"></i> 4. ตรวจผลงานส่ง
            </a>
        </div>

        <!-- Filters (Course Dropdown + Status Tabs) -->
        <div class="space-y-4 mb-6">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-150/60 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-xs font-extrabold text-slate-500 uppercase shrink-0">เลือกหลักสูตร:</label>
                    <select x-model="filterCourseId" @change="fetchData()" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition w-full sm:w-80">
                        <option value="">-- แสดงทุกหลักสูตร --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-[11px] font-bold text-slate-400" x-text="`แสดงทั้งหมด: ${submissions.length} รายการ`"></div>
            </div>

            <!-- Status filter sub-tabs -->
            <div class="flex flex-wrap gap-1.5 border-b border-slate-100 pb-3">
                <button type="button" @click="setFilterStatus('pending')" :class="filterStatus === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-550 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-extrabold transition cursor-pointer">
                    รอตรวจประเมิน (Pending)
                </button>
                <button type="button" @click="setFilterStatus('passed')" :class="filterStatus === 'passed' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-550 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-extrabold transition cursor-pointer">
                    ผ่านการประเมิน (Passed)
                </button>
                <button type="button" @click="setFilterStatus('failed')" :class="filterStatus === 'failed' ? 'bg-rose-500 text-white shadow-sm' : 'bg-slate-100 text-slate-550 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-extrabold transition cursor-pointer">
                    ไม่อนุมัติ/ให้แก้ไข (Failed)
                </button>
                <button type="button" @click="setFilterStatus('all')" :class="filterStatus === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-550 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-extrabold transition cursor-pointer">
                    ทั้งหมด (All)
                </button>
            </div>
        </div>

        <!-- Table Grid List -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-450 uppercase font-extrabold tracking-wider">
                            <th class="py-4 px-6">นักเรียน/ผู้ส่ง</th>
                            <th class="py-4 px-6">ข้อมูลบทเรียน</th>
                            <th class="py-4 px-6">ไฟล์ที่ส่ง</th>
                            <th class="py-4 px-6 w-32">วันที่ส่ง</th>
                            <th class="py-4 px-6 w-28 text-center">สถานะ</th>
                            <th class="py-4 px-6 text-center w-32">ประเมิน</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="submissions.length === 0">
                            <tr>
                                <td colspan="6" class="py-16 text-center text-slate-400 font-bold">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-200 block"></i>
                                    ยังไม่มีชิ้นงานส่งตามเงื่อนไขที่เลือก
                                </td>
                            </tr>
                        </template>
                        <template x-for="sub in submissions" :key="sub.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-800 text-xs block" x-text="sub.student_name"></span>
                                    <span class="text-[9px] text-slate-400 block" x-text="sub.student_school || 'สพป.ชุมพร เขต 1'"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-800 text-xs block" x-text="sub.lesson_title"></span>
                                    <span class="text-[9px] text-indigo-600 block mt-0.5 line-clamp-1" x-text="sub.course_title"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <a :href="`/${sub.file_url}`" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:text-indigo-800 hover:bg-slate-50 font-bold transition">
                                        <i class="fa-solid fa-file-pdf text-rose-500"></i> เปิดไฟล์งาน
                                    </a>
                                    <template x-if="sub.student_note">
                                        <div class="text-[9px] text-slate-450 mt-1 max-w-[200px] truncate" :title="sub.student_note" x-text="`โน้ต: ${sub.student_note}`"></div>
                                    </template>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-500" x-text="formatDate(sub.submitted_at)"></td>
                                <td class="py-4 px-6 text-center">
                                    <template x-if="sub.status === 'passed'">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px] font-bold">ผ่านแล้ว</span>
                                    </template>
                                    <template x-if="sub.status === 'failed'">
                                        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded text-[9px] font-bold">ให้แก้ไข</span>
                                    </template>
                                    <template x-if="sub.status === 'pending'">
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[9px] font-bold">รอการตรวจ</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button type="button" @click="openEvaluationModal(sub)" 
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-750 text-white font-extrabold text-xs rounded-xl shadow-md shadow-indigo-100/50 hover:shadow-lg hover:shadow-indigo-200/50 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer">
                                        <i class="fa-solid fa-file-circle-check text-[11px]"></i>
                                        <span>ประเมินผล</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Evaluation Form Modal Dialog -->
        <div x-show="modal.open" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-lg w-full p-8 relative space-y-5 text-left" @click.away="modal.open = false">
                
                <h3 class="text-lg font-extrabold text-slate-900">ตรวจประเมินผลงานส่งของนักเรียน</h3>

                <!-- Student summary info -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-xs space-y-1.5 leading-relaxed">
                    <div><span class="font-bold text-slate-500">ผู้ส่ง:</span> <span class="font-extrabold text-slate-800" x-text="activeSub.student_name"></span></div>
                    <div><span class="font-bold text-slate-500">โรงเรียน:</span> <span class="font-bold text-slate-700" x-text="activeSub.student_school || 'สพป.ชุมพร เขต 1'"></span></div>
                    <div><span class="font-bold text-slate-500">วิชา/บทเรียน:</span> <span class="font-bold text-slate-700" x-text="activeSub.lesson_title"></span></div>
                    <div x-show="activeSub.student_note"><span class="font-bold text-slate-500">บันทึกเพิ่มเติมจากผู้ส่ง:</span> <span class="text-slate-600 italic" x-text="activeSub.student_note"></span></div>
                </div>

                <!-- Rubric block if present -->
                <template x-if="activeSub.rubric_html">
                    <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100/50 text-[10px] text-indigo-850 leading-relaxed">
                        <span class="font-bold block mb-1">เกณฑ์การให้คะแนน (Rubric):</span>
                        <div x-html="activeSub.rubric_html"></div>
                    </div>
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">ความคิดเห็น/คำแนะนำป้อนกลับไปยังนักเรียน</label>
                        <textarea x-model="form.admin_comment" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition" placeholder="พิมพ์ความคิดเห็น หรือระบุจุดประสงค์ที่ต้องแก้ไข..."></textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-wrap justify-between items-center gap-3">
                    <button type="button" @click="modal.open = false" 
                            class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl font-bold text-xs text-slate-550 transition-all active:scale-95 cursor-pointer">
                        ยกเลิก
                    </button>
                    
                    <div class="flex gap-2">
                        <button type="button" 
                                @click="evaluate('failed')" 
                                :disabled="saving"
                                class="inline-flex items-center gap-1.5 px-5 py-3 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-750 text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-100/50 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 transition-all duration-200 cursor-pointer">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <template x-if="!saving">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </template>
                            <span>ให้แก้ไข</span>
                        </button>
                        <button type="button" 
                                @click="evaluate('passed')" 
                                :disabled="saving"
                                class="inline-flex items-center gap-1.5 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-750 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-100/50 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 transition-all duration-200 cursor-pointer">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                            <template x-if="!saving">
                                <i class="fa-solid fa-circle-check"></i>
                            </template>
                            <span>อนุมัติให้ผ่าน</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function lmsSubmissionsAdmin() {
        return {
            submissions: [],
            filterCourseId: '',
            filterStatus: 'pending',
            loading: false,
            saving: false,
            toast: { show: false, message: '', type: 'success' },
            modal: { open: false },
            activeSub: {},
            form: {
                admin_comment: ''
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            init() {
                this.fetchData();
            },

            fetchData() {
                this.loading = true;
                axios.get(`{{ route("admin.lms.submissions.data") }}?course_id=${this.filterCourseId}&status=${this.filterStatus}`)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.submissions = response.data.data;
                        }
                    })
                    .catch(error => {
                        this.showToast('ไม่สามารถดึงข้อมูลผลงานส่งได้', 'error');
                    })
                    .finally(() => { this.loading = false; });
            },

            setFilterStatus(status) {
                this.filterStatus = status;
                this.fetchData();
            },

            openEvaluationModal(sub) {
                this.activeSub = sub;
                this.form.admin_comment = sub.admin_comment || '';
                this.modal.open = true;
            },

            evaluate(decision) {
                this.saving = true;
                axios.post(`/admin/lms/submissions/${this.activeSub.id}/evaluate`, {
                    decision: decision,
                    admin_comment: this.form.admin_comment
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showToast(response.data.message, 'success');
                        this.modal.open = false;
                        this.fetchData();
                    } else {
                        this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                })
                .catch(error => {
                    this.showToast('ไม่สามารถส่งผลประเมินชิ้นงานนี้ได้', 'error');
                })
                .finally(() => { this.saving = false; });
            },

            formatDate(dStr) {
                if (!dStr) return '';
                const d = new Date(dStr);
                return d.toLocaleDateString('th-TH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        };
    }
    </script>
    @endpush
</x-layout>
