<x-layout>
    <x-slot:title>จัดการหลักสูตรอบรม | EE CPN1</x-slot>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="courseManager()" x-init="init()">
        <!-- Toast Notification (Floating Glassmorphic) -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl backdrop-blur-md border border-white/20"
             :class="toast.type === 'success' ? 'bg-emerald-500/95 text-white' : 'bg-rose-500/95 text-white'"
             x-cloak>
            <template x-if="toast.type === 'success'">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </template>
            <template x-if="toast.type === 'error'">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </template>
            <span class="text-xs font-bold" x-text="toast.message"></span>
        </div>

        <header class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight font-sans">จัดการหลักสูตรการอบรม</h2>
                <p class="text-slate-500 text-sm mt-1">เพิ่ม แก้ไข ลบข้อมูลหลักสูตร และปรับปรุงรายงานสรุปผลกิจกรรมการอบรมครู</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> เพิ่มหลักสูตรใหม่
                </button>
            </div>
        </header>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังดาวน์โหลดข้อมูลหลักสูตร...</p>
        </div>

        <!-- Courses List View -->
        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-450 uppercase font-bold tracking-wider">
                            <th class="py-4 px-6 w-36">ภาพหน้าปก</th>
                            <th class="py-4 px-6 w-20 text-center">ลำดับ</th>
                            <th class="py-4 px-6">ชื่อหลักสูตร</th>
                            <th class="py-4 px-6 w-24">ชั่วโมงอบรม</th>
                            <th class="py-4 px-6 w-32">สถานะ</th>
                            <th class="py-4 px-6">ระยะเวลา/สถานที่</th>
                            <th class="py-4 px-6 text-center w-36">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="courses.length === 0">
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                    <div class="mb-2 text-2xl">📚</div>
                                    ยังไม่มีข้อมูลหลักสูตรอบรมในระบบ
                                </td>
                            </tr>
                        </template>
                        <template x-for="course in courses" :key="course.id">
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="py-4 px-6">
                                    <div class="w-24 h-14 border border-slate-100 bg-slate-50 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
                                        <template x-if="course.cover_image_url">
                                            <img :src="course.cover_image_url" alt="Cover" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!course.cover_image_url">
                                            <div class="text-[10px] text-slate-400 font-bold">ไม่มีรูปปก</div>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center font-extrabold text-slate-650" x-text="course.sort_order"></td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-slate-800 text-xs leading-snug" x-text="course.title"></span>
                                        <template x-if="course.academic_year">
                                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 font-bold rounded text-[8px] tracking-wide shrink-0" x-text="'ปี ' + course.academic_year"></span>
                                        </template>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5" x-text="'กลุ่มเป้าหมาย: ' + (course.target_group || '-')"></div>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-700">
                                    <span x-text="course.hours ? course.hours + ' ชม.' : '-'"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <template x-if="course.status === 'open'">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-md text-[10px]">เปิดรับสมัคร</span>
                                    </template>
                                    <template x-if="course.status === 'ongoing'">
                                        <span class="px-2.5 py-1 bg-sky-50 text-sky-700 font-bold rounded-md text-[10px]">กำลังดำเนินการ</span>
                                    </template>
                                    <template x-if="course.status === 'upcoming'">
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-bold rounded-md text-[10px]">เตรียมเปิดสมัคร</span>
                                    </template>
                                    <template x-if="course.status === 'closed'">
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 font-bold rounded-md text-[10px]">ปิดการอบรม</span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    <div x-text="course.duration_text || '-'"></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5" x-text="'สถานที่: ' + (course.location || '-')"></div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal(course)" class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-2 rounded-lg transition" title="แก้ไข/รายงานสรุป">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(course)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition" title="ลบหลักสูตร">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Course Modal -->
        <div x-show="modal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <form @submit.prevent="saveCourse()" class="bg-white rounded-[2rem] max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] md:max-h-[85vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-book text-emerald-500"></i>
                        <span x-text="cropping ? 'ครอบตัดภาพปกหลักสูตร (16:9)' : (form.id ? 'แก้ไขข้อมูลและรายงานหลักสูตร' : 'เพิ่มหลักสูตรการอบรมใหม่')"></span>
                    </h3>
                    <button type="button" @click="cropping ? closeCropper() : (modal.open = false)" class="text-slate-400 hover:text-slate-650 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                    
                    <!-- Tabs Menu (Only visible when NOT cropping) -->
                    <div x-show="!cropping" class="px-6 border-b border-slate-100 flex gap-4 bg-slate-50/30 shrink-0">
                        <button type="button" 
                                @click="activeTab = 'general'" 
                                class="py-3 text-xs font-bold transition border-b-2 px-1"
                                :class="activeTab === 'general' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-450 hover:text-slate-650'">
                            1. รายละเอียดหลักสูตร
                        </button>
                        <button type="button" 
                                @click="activeTab = 'report'" 
                                class="py-3 text-xs font-bold transition border-b-2 px-1"
                                :class="activeTab === 'report' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-450 hover:text-slate-650'">
                            2. รายงานการอบรม & รูปภาพกิจกรรม
                        </button>
                    </div>

                    <!-- Scrollable Content container -->
                    <div class="p-6 md:p-8 space-y-5 overflow-y-auto flex-1">
                        
                        <!-- Tabs Content 1: General Info -->
                        <div x-show="activeTab === 'general' && !cropping" class="space-y-4">
                            <!-- Title Input -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อหลักสูตร (Course Title) *</label>
                                <input type="text" 
                                       x-model="form.title" 
                                       required 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                       placeholder="เช่น การประยุกต์ใช้ AI ในชั้นเรียนประถม">
                            </div>

                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Hours Input -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">จำนวนชั่วโมงการอบรม (Hours)</label>
                                    <input type="text" 
                                           x-model="form.hours" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น 15, 20 ชั่วโมง">
                                </div>

                                <!-- Status Selector -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">สถานะหลักสูตร (Status) *</label>
                                    <select x-model="form.status" 
                                            required 
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                        <option value="upcoming">เตรียมเปิดสมัคร (Upcoming)</option>
                                        <option value="open">เปิดรับสมัคร (Open)</option>
                                        <option value="ongoing">กำลังดำเนินการ (Ongoing)</option>
                                        <option value="closed">ปิดการอบรม (Closed)</option>
                                    </select>
                                </div>

                                <!-- Sort Order Input -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ลำดับการเรียง (Sort Order) *</label>
                                    <input type="number" 
                                           x-model="form.sort_order" 
                                           required 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น 0, 1, 2">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Duration Text -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ระยะเวลาการอบรม (Duration Text)</label>
                                    <input type="text" 
                                           x-model="form.duration_text" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น ธ.ค. 68 - มี.ค. 69">
                                </div>

                                <!-- Academic Year Input -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ปีการศึกษา (Academic Year)</label>
                                    <input type="text" 
                                           x-model="form.academic_year" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น 2568, 2569">
                                </div>

                                <!-- Target Group -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">กลุ่มเป้าหมาย (Target Group)</label>
                                    <input type="text" 
                                           x-model="form.target_group" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น ครูผู้สอนวิทยาศาสตร์ ป.4-ป.6">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Location -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">สถานที่จัดงาน (Location)</label>
                                    <input type="text" 
                                           x-model="form.location" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="เช่น ศูนย์คอมพิวเตอร์ โรงเรียนอนุบาลชุมพร">
                                </div>

                                <!-- Registration URL -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ลิงก์ลงทะเบียนสมัคร (Registration URL)</label>
                                    <input type="url" 
                                           x-model="form.registration_link" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                           placeholder="https://forms.gle/your-form-url">
                                </div>
                            </div>

                            <!-- Objectives -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คำอธิบาย/วัตถุประสงค์ (Objectives)</label>
                                <textarea x-model="form.objectives" 
                                          rows="4" 
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                          placeholder="ระบุวัตถุประสงค์หรือข้อมูลแนะนำหลักสูตร..."></textarea>
                            </div>

                            <!-- Cover Image Selector -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">รูปภาพแบนเนอร์ปกหลักสูตร (Cover Banner)</label>
                                <div class="flex flex-col gap-3">
                                    <div class="w-full h-40 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 flex items-center justify-center p-1.5 overflow-hidden relative">
                                        <template x-if="form.previewUrl">
                                            <img :src="form.previewUrl" alt="Cover Preview" class="w-full h-full object-cover rounded-xl">
                                        </template>
                                        <template x-if="!form.previewUrl">
                                            <div class="text-center text-slate-400">
                                                <i class="fa-regular fa-image text-3xl mb-1.5 block"></i>
                                                <span class="text-xs">คลิกเลือกไฟล์เพื่ออัปโหลดแบนเนอร์หลักสูตร</span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="$refs.coverInput.click()" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-slate-800 transition">
                                            เลือกไฟล์รูปปก...
                                        </button>
                                        <template x-if="form.previewUrl">
                                            <button type="button" @click="removeCover()" class="text-rose-500 hover:bg-rose-50 px-2 py-1.5 rounded-lg font-bold text-[10px] transition">
                                                ลบรูปปก
                                            </button>
                                        </template>
                                        <span class="text-[9px] text-slate-400 ms-auto">แนะนำภาพแนวนอน (16:9) รองรับการครอบตัด</span>
                                    </div>
                                    <input type="file" x-ref="coverInput" class="hidden" accept="image/*" @change="coverSelected($event)">
                                </div>
                            </div>
                        </div>

                        <!-- Tabs Content 2: Reports & Activity Gallery -->
                        <div x-show="activeTab === 'report' && !cropping" class="space-y-5" x-cloak>
                            <!-- Report Text -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">รายงานผลสรุปกิจกรรมหลังการอบรม (Training Summary Report)</label>
                                <textarea x-model="form.report_text" 
                                          rows="5" 
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                          placeholder="ระบุข้อความสรุปผลลัพธ์การอบรม เช่น จำนวนครูที่จบหลักสูตร, ผลตอบรับ, หรือข้อตกลงร่วม..."></textarea>
                            </div>

                            <!-- Activity Images Gallery Uploader -->
                            <div class="space-y-3">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">คลังภาพถ่ายกิจกรรมการอบรม (Activity Gallery)</label>
                                
                                <!-- Uploader Button & Info -->
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="$refs.reportFilesInput.click()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-emerald-700 transition flex items-center gap-1.5">
                                        <i class="fa-solid fa-images"></i> เพิ่มภาพกิจกรรม...
                                    </button>
                                    <span class="text-[10px] text-slate-400">เลือกไฟล์รูปภาพกิจกรรมพร้อมกันได้ครั้งละหลายไฟล์</span>
                                    <input type="file" x-ref="reportFilesInput" class="hidden" accept="image/*" multiple @change="addReportImage($event)">
                                </div>

                                <!-- Current/New Previews Gallery Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                                    <!-- Existing Images from database -->
                                    <template x-for="img in form.existing_report_images" :key="img.path">
                                        <div class="relative w-full h-20 border border-slate-100 rounded-xl overflow-hidden group bg-slate-50">
                                            <img :src="img.url" alt="Existing activity" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                <button type="button" @click="removeExistingReportImage(img.path)" class="text-white hover:text-rose-400 text-sm p-2 transition" title="ลบรูป">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                            <span class="absolute top-1 left-1 bg-slate-900/60 text-white font-bold text-[8px] px-1.5 py-0.5 rounded-md">รูปเดิม</span>
                                        </div>
                                    </template>

                                    <!-- Newly selected images (Base64) -->
                                    <template x-for="(img, idx) in form.new_report_previews" :key="idx">
                                        <div class="relative w-full h-20 border border-slate-100 rounded-xl overflow-hidden group bg-slate-50">
                                            <img :src="img" alt="New activity" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                <button type="button" @click="removeNewReportImage(idx)" class="text-white hover:text-rose-400 text-sm p-2 transition" title="ลบรูป">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                            <span class="absolute top-1 left-1 bg-emerald-600/90 text-white font-bold text-[8px] px-1.5 py-0.5 rounded-md">ใหม่</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Attached Files Uploader -->
                            <div class="space-y-3 pt-4 border-t border-slate-100">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">เอกสารแนบรายงานผลการอบรม (Report Attachments)</label>
                                
                                <!-- Uploader Button & Info -->
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="$refs.reportDocsInput.click()" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-slate-800 transition flex items-center gap-1.5">
                                        <i class="fa-solid fa-file-arrow-up"></i> เพิ่มไฟล์เอกสาร...
                                    </button>
                                    <span class="text-[10px] text-slate-400">รองรับไฟล์เอกสาร PDF, Word, Excel, PowerPoint, ZIP ฯลฯ</span>
                                    <input type="file" x-ref="reportDocsInput" class="hidden" multiple @change="addReportFile($event)">
                                </div>

                                <!-- Current/New Files List -->
                                <div class="space-y-2 pt-1 max-h-48 overflow-y-auto pr-1">
                                    <!-- Existing Files from database -->
                                    <template x-for="file in form.existing_report_files" :key="file.path">
                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100 group transition hover:bg-slate-100/50">
                                            <div class="flex items-center gap-2 overflow-hidden flex-1">
                                                <i class="fa-solid fa-file-pdf text-rose-500 text-sm" x-show="file.name.toLowerCase().endsWith('.pdf')"></i>
                                                <i class="fa-solid fa-file-word text-blue-500 text-sm" x-show="file.name.toLowerCase().endsWith('.doc') || file.name.toLowerCase().endsWith('.docx')"></i>
                                                <i class="fa-solid fa-file-excel text-emerald-600 text-sm" x-show="file.name.toLowerCase().endsWith('.xls') || file.name.toLowerCase().endsWith('.xlsx')"></i>
                                                <i class="fa-solid fa-file-zipper text-amber-600 text-sm" x-show="file.name.toLowerCase().endsWith('.zip') || file.name.toLowerCase().endsWith('.rar')"></i>
                                                <i class="fa-solid fa-file text-slate-400 text-sm" x-show="!file.name.toLowerCase().endsWith('.pdf') && !file.name.toLowerCase().endsWith('.doc') && !file.name.toLowerCase().endsWith('.docx') && !file.name.toLowerCase().endsWith('.xls') && !file.name.toLowerCase().endsWith('.xlsx') && !file.name.toLowerCase().endsWith('.zip') && !file.name.toLowerCase().endsWith('.rar')"></i>
                                                
                                                <span class="text-[11px] font-bold text-slate-705 truncate" x-text="file.name"></span>
                                                <span class="px-1.5 py-0.5 bg-slate-200 text-slate-600 font-bold rounded text-[8px] tracking-wide shrink-0">ไฟล์เดิม</span>
                                            </div>
                                            <button type="button" @click="removeExistingReportFile(file.path)" class="text-slate-405 hover:text-rose-500 text-xs px-2 py-1 transition" title="ลบไฟล์">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Newly selected files -->
                                    <template x-for="(file, idx) in form.new_report_files" :key="idx">
                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100 group transition hover:bg-slate-100/50">
                                            <div class="flex items-center gap-2 overflow-hidden flex-1">
                                                <i class="fa-solid fa-file-pdf text-rose-500 text-sm" x-show="file.name.toLowerCase().endsWith('.pdf')"></i>
                                                <i class="fa-solid fa-file-word text-blue-500 text-sm" x-show="file.name.toLowerCase().endsWith('.doc') || file.name.toLowerCase().endsWith('.docx')"></i>
                                                <i class="fa-solid fa-file-excel text-emerald-600 text-sm" x-show="file.name.toLowerCase().endsWith('.xls') || file.name.toLowerCase().endsWith('.xlsx')"></i>
                                                <i class="fa-solid fa-file-zipper text-amber-600 text-sm" x-show="file.name.toLowerCase().endsWith('.zip') || file.name.toLowerCase().endsWith('.rar')"></i>
                                                <i class="fa-solid fa-file text-slate-400 text-sm" x-show="!file.name.toLowerCase().endsWith('.pdf') && !file.name.toLowerCase().endsWith('.doc') && !file.name.toLowerCase().endsWith('.docx') && !file.name.toLowerCase().endsWith('.xls') && !file.name.toLowerCase().endsWith('.xlsx') && !file.name.toLowerCase().endsWith('.zip') && !file.name.toLowerCase().endsWith('.rar')"></i>
                                                
                                                <span class="text-[11px] font-bold text-slate-705 truncate" x-text="file.name"></span>
                                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded text-[8px] tracking-wide shrink-0">ใหม่</span>
                                            </div>
                                            <button type="button" @click="removeNewReportFile(idx)" class="text-slate-405 hover:text-rose-500 text-xs px-2 py-1 transition" title="ลบไฟล์">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Cropper Canvas View (Visible only when cropping cover image) -->
                        <div x-show="cropping" class="space-y-4 flex flex-col" x-cloak>
                            <div class="p-4 sm:p-6 bg-slate-100 flex justify-center items-center overflow-hidden h-48 sm:h-64 md:h-72 rounded-2xl">
                                <img id="courseCropperImage" class="max-w-full max-h-full block">
                            </div>
                            
                            <!-- Cropper Controls -->
                            <div class="flex justify-between items-center bg-white text-slate-500">
                                <span class="text-[10px] font-medium text-slate-400">กรอบอัตราส่วนแบบแนวนอน 16:9 เท่านั้น</span>
                                <div class="flex gap-2">
                                    <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                    </button>
                                    <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="หมุนขวา" @click="cropper.rotate(90)">
                                        <i class="fa-solid fa-rotate-right text-xs"></i>
                                    </button>
                                    <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                                        <i class="fa-solid fa-magnifying-glass-plus text-xs"></i>
                                    </button>
                                    <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="ซูมออก" @click="cropper.zoom(-0.1)">
                                        <i class="fa-solid fa-magnifying-glass-minus text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions (Standard views) -->
                    <div x-show="!cropping" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50 shrink-0">
                        <button type="button" @click="modal.open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                :disabled="saving" 
                                class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-lg shadow-emerald-100 flex items-center gap-2">
                            <template x-if="saving">
                                <i class="fa-solid fa-circle-notch animate-spin"></i>
                            </template>
                            <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูลหลักสูตร'"></span>
                        </button>
                    </div>

                    <!-- Modal Actions (Crop View) -->
                    <div x-show="cropping" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50/50 shrink-0" x-cloak>
                        <button type="button" @click="closeCropper()" class="px-4 py-2 bg-white border border-slate-200 text-slate-650 rounded-lg font-bold text-[10px] hover:bg-slate-50 transition">
                            ยกเลิก
                        </button>
                        <button type="button" @click="cropImage()" class="px-5 py-2 bg-emerald-600 text-white rounded-lg font-bold text-[10px] hover:bg-emerald-700 transition">
                            บันทึกการครอบตัด
                        </button>
                    </div>
            </form>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-[2rem] max-w-sm w-full overflow-hidden shadow-2xl border border-slate-100 p-6 flex flex-col gap-5">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบหลักสูตรอบรม</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ต้องการลบหลักสูตร <span class="font-bold text-slate-650" x-text="deleteModal.courseTitle"></span> หรือไม่? ข้อมูลสรุปและรูปแกลเลอรีทั้งหมดจะถูกลบและกู้คืนไม่ได้
                    </p>
                </div>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-150 text-slate-600 rounded-xl font-bold text-xs transition">
                        ยกเลิก
                    </button>
                    <button type="button" @click="deleteCourse()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition shadow-md shadow-rose-100">
                        ยืนยันการลบ
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function courseManager() {
            return {
                loading: true,
                saving: false,
                courses: [],
                cropping: false,
                activeTab: 'general',
                form: {
                    id: null,
                    title: '',
                    hours: '',
                    academic_year: '',
                    objectives: '',
                    registration_link: '',
                    target_group: '',
                    location: '',
                    status: 'upcoming',
                    sort_order: 0,
                    duration_text: '',
                    report_text: '',
                    cover_image_data: '',
                    previewUrl: null,
                    delete_cover_image: false,
                    existing_report_images: [], // array of {path: '...', url: '...'}
                    new_report_images: [],      // array of base64
                    new_report_previews: [],    // array of base64
                    existing_report_files: [],   // array of {name: '...', path: '...', url: '...'}
                    new_report_files: []        // array of {name: '...', data: '...'}
                },
                modal: {
                    open: false
                },
                deleteModal: {
                    open: false,
                    courseId: null,
                    courseTitle: ''
                },
                cropper: null,
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                init() {
                    this.fetchCourses();
                },

                fetchCourses() {
                    this.loading = true;
                    axios.get('{{ route('admin.courses.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.courses = response.data.data;
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Courses Error:', error);
                            this.showToast('ไม่สามารถโหลดข้อมูลหลักสูตรได้', 'error');
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                openCreateModal() {
                    this.resetForm();
                    this.activeTab = 'general';
                    this.modal.open = true;
                },

                openEditModal(course) {
                    this.resetForm();
                    this.form.id = course.id;
                    this.form.title = course.title;
                    this.form.hours = course.hours || '';
                    this.form.academic_year = course.academic_year || '';
                    this.form.objectives = course.objectives || '';
                    this.form.registration_link = course.registration_link || '';
                    this.form.target_group = course.target_group || '';
                    this.form.location = course.location || '';
                    this.form.status = course.status;
                    this.form.sort_order = course.sort_order || 0;
                    this.form.duration_text = course.duration_text || '';
                    this.form.report_text = course.report_text || '';
                    this.form.previewUrl = course.cover_image_url;
                    
                    // Maps database response reports
                    this.form.existing_report_images = course.report_images_urls ? [...course.report_images_urls] : [];
                    this.form.existing_report_files = course.report_files_urls ? [...course.report_files_urls] : [];
                    
                    this.activeTab = 'general';
                    this.modal.open = true;
                },

                resetForm() {
                    this.form.id = null;
                    this.form.title = '';
                    this.form.hours = '';
                    this.form.academic_year = '';
                    this.form.objectives = '';
                    this.form.registration_link = '';
                    this.form.target_group = '';
                    this.form.location = '';
                    this.form.status = 'upcoming';
                    this.form.sort_order = 0;
                    this.form.duration_text = '';
                    this.form.report_text = '';
                    this.form.cover_image_data = '';
                    this.form.previewUrl = null;
                    this.form.delete_cover_image = false;
                    this.form.existing_report_images = [];
                    this.form.new_report_images = [];
                    this.form.new_report_previews = [];
                    this.form.existing_report_files = [];
                    this.form.new_report_files = [];
                    this.cropping = false;
                    if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                    if (this.$refs.reportFilesInput) this.$refs.reportFilesInput.value = '';
                    if (this.$refs.reportDocsInput) this.$refs.reportDocsInput.value = '';
                },

                coverSelected(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.cropping = true;
                        const imageEl = document.getElementById('courseCropperImage');
                        imageEl.src = e.target.result;
                        
                        setTimeout(() => {
                            this.initCropper(imageEl);
                        }, 100);
                    };
                    reader.readAsDataURL(file);
                },

                initCropper(imageEl) {
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    this.cropper = new Cropper(imageEl, {
                        aspectRatio: 16 / 9, // Landscape ratio
                        viewMode: 1,
                        background: false,
                        responsive: true,
                        checkOrientation: false
                    });
                },

                cropImage() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 640,
                        height: 360
                    });
                    this.form.cover_image_data = canvas.toDataURL('image/jpeg');
                    this.form.previewUrl = this.form.cover_image_data;
                    this.form.delete_cover_image = false;
                    this.closeCropper();
                },

                removeCover() {
                    this.form.previewUrl = null;
                    this.form.cover_image_data = '';
                    this.form.delete_cover_image = true;
                    if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                },

                closeCropper() {
                    this.cropping = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    if (this.$refs.coverInput) this.$refs.coverInput.value = '';
                },

                addReportImage(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.form.new_report_images.push(e.target.result);
                            this.form.new_report_previews.push(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeNewReportImage(index) {
                    this.form.new_report_images.splice(index, 1);
                    this.form.new_report_previews.splice(index, 1);
                },

                removeExistingReportImage(path) {
                    this.form.existing_report_images = this.form.existing_report_images.filter(img => img.path !== path);
                },

                addReportFile(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.form.new_report_files.push({
                                name: file.name,
                                data: e.target.result
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeNewReportFile(index) {
                    this.form.new_report_files.splice(index, 1);
                },

                removeExistingReportFile(path) {
                    this.form.existing_report_files = this.form.existing_report_files.filter(file => file.path !== path);
                },

                saveCourse() {
                    this.saving = true;
                    
                    // Maps existing report image paths for payload
                    const existingPaths = this.form.existing_report_images.map(img => img.path);

                    const payload = {
                        id: this.form.id,
                        title: this.form.title,
                        hours: this.form.hours,
                        academic_year: this.form.academic_year,
                        objectives: this.form.objectives,
                        registration_link: this.form.registration_link,
                        target_group: this.form.target_group,
                        location: this.form.location,
                        status: this.form.status,
                        sort_order: this.form.sort_order,
                        duration_text: this.form.duration_text,
                        report_text: this.form.report_text,
                        cover_image_data: this.form.cover_image_data,
                        delete_cover_image: this.form.delete_cover_image,
                        existing_report_images: existingPaths,
                        new_report_images: this.form.new_report_images,
                        existing_report_files: this.form.existing_report_files,
                        new_report_files: this.form.new_report_files
                    };

                    axios.post('{{ route('admin.courses.save') }}', payload)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.modal.open = false;
                                this.fetchCourses();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Save Course Error:', error);
                            const msg = error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                            this.showToast(msg, 'error');
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                },

                confirmDelete(course) {
                    this.deleteModal.courseId = course.id;
                    this.deleteModal.courseTitle = course.title;
                    this.deleteModal.open = true;
                },

                deleteCourse() {
                    this.deleteModal.open = false;
                    const id = this.deleteModal.courseId;

                    axios.delete(`{{ url('/admin/courses') }}/${id}`)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.fetchCourses();
                            } else {
                                this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Delete Course Error:', error);
                            this.showToast('เกิดข้อผิดพลาดในการลบหลักสูตรอบรม', 'error');
                        });
                },

                showToast(message, type = 'success') {
                    this.toast.show = true;
                    this.toast.message = message;
                    this.toast.type = type;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                }
            };
        }
    </script>
    @endpush
</x-layout>

