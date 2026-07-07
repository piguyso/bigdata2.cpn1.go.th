<x-layout>
    <x-slot:title>จัดการโครงสร้างศูนย์ | EE CPN1</x-slot>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="orgManager()" x-init="init()">
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
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">จัดการโครงสร้างบุคลากรศูนย์</h2>
                <p class="text-slate-500 text-sm mt-1">อัปเดตสมาชิก ที่ปรึกษา และคณะทำงานของศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="bg-white border border-slate-200 text-slate-655 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> เพิ่มบุคลากรใหม่
                </button>
            </div>
        </header>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังดาวน์โหลดโครงสร้างบุคลากร...</p>
        </div>

        <!-- Committee Filter Tabs -->
        <div x-show="!loading" class="mb-6 flex p-1 bg-slate-100 rounded-2xl w-fit overflow-x-auto no-scrollbar border border-slate-200/50 shadow-inner" x-cloak x-transition>
            <button @click="selectedCommittee = 'operations'" 
                    class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200"
                    :class="selectedCommittee === 'operations' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                คณะที่ปรึกษา
            </button>
            <button @click="selectedCommittee = 'executive'" 
                    class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200"
                    :class="selectedCommittee === 'executive' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                1. คณะกรรมการอำนวยการ
            </button>
            <button @click="selectedCommittee = 'academic'" 
                    class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200"
                    :class="selectedCommittee === 'academic' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                2. คณะกรรมการวิชาการ
            </button>
            <button @click="selectedCommittee = 'finance'" 
                    class="px-4 py-2.5 rounded-xl font-bold text-xs transition duration-200"
                    :class="selectedCommittee === 'finance' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                3. คณะกรรมการการเงิน & สถานที่
            </button>
        </div>

        <!-- Members Table View -->
        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-450 uppercase font-bold tracking-wider">
                            <th class="py-4 px-6 w-16">รูปถ่าย</th>
                            <th class="py-4 px-6">ชื่อ - นามสกุล</th>
                            <th class="py-4 px-6">ตำแหน่งในการทำงาน</th>
                            <th class="py-4 px-6">ประเภท</th>
                            <th class="py-4 px-6 w-20 text-center">เลเวล</th>
                            <th class="py-4 px-6 w-20 text-center">อันดับ</th>
                            <th class="py-4 px-6 text-center w-36">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="members.filter(m => (m.committee || 'operations') === selectedCommittee).length === 0">
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                    <div class="mb-2 text-2xl">👥</div>
                                    ยังไม่มีข้อมูลบุคลากรในคณะกรรมการชุดนี้ กดเพิ่มบุคลากรรายแรกได้เลย
                                </td>
                            </tr>
                        </template>
                        <template x-for="member in members.filter(m => (m.committee || 'operations') === selectedCommittee)" :key="member.id">
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="py-4 px-6">
                                    <div class="w-10 h-12 border border-slate-150 bg-slate-50 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
                                        <template x-if="member.photo_url">
                                            <img :src="member.photo_url" alt="Photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!member.photo_url">
                                            <span class="text-base text-slate-350">👤</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-800" x-text="member.name"></td>
                                <td class="py-4 px-6 text-slate-600" x-text="member.position"></td>
                                <td class="py-4 px-6">
                                    <template x-if="member.role === 'advisor'">
                                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 font-bold rounded-md">ที่ปรึกษา</span>
                                    </template>
                                    <template x-if="member.role === 'member'">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-md">กรรมการ / คณะทำงาน</span>
                                    </template>
                                    <template x-if="member.role_title">
                                        <span class="block text-[10px] text-slate-500 mt-1 font-semibold" x-text="member.role_title"></span>
                                    </template>
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-slate-550" x-text="member.level"></td>
                                <td class="py-4 px-6 text-center font-bold text-slate-500" x-text="member.sort_order"></td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal(member)" class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-2 rounded-lg transition" title="แก้ไขข้อมูล">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(member)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition" title="ลบข้อมูล">
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

        <!-- Add/Edit Org Member Modal -->
        <div x-show="modal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <form @submit.prevent="saveMember()" class="bg-white rounded-[2rem] max-w-xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] md:max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sitemap text-emerald-500"></i>
                        <span x-text="cropping ? 'ครอบตัดรูปภาพบุคลากร (4:5)' : (form.id ? 'แก้ไขข้อมูลบุคลากร' : 'เพิ่มบุคลากรใหม่')"></span>
                    </h3>
                    <button type="button" @click="cropping ? closeCropper() : (modal.open = false)" class="text-slate-400 hover:text-slate-650 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <div class="p-6 md:p-8 space-y-5 overflow-y-auto flex-1">
                    <!-- Standard Form Fields -->
                    <div x-show="!cropping" class="space-y-5">
                        <!-- Name Input -->
                        <div class="space-y-1.5 text-left">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อ - นามสกุล *</label>
                            <input type="text" 
                                   x-model="form.name" 
                                   required 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น นายอภิเดช เกื้อกูล">
                        </div>

                        <!-- Position Input -->
                        <div class="space-y-1.5 text-left">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ตำแหน่งหน้าที่ศูนย์ *</label>
                            <input type="text" 
                                   x-model="form.position" 
                                   required 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                   placeholder="เช่น ประธานคณะทำงานศูนย์ฯ หรือ ครูวิทยากรแกนนำ">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Committee selector -->
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">คณะกรรมการ / กลุ่มงาน *</label>
                                <select x-model="form.committee" 
                                        required 
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                    <option value="operations">คณะที่ปรึกษา</option>
                                    <option value="executive">1. คณะกรรมการอำนวยการ</option>
                                    <option value="academic">2. คณะกรรมการวิชาการ</option>
                                    <option value="finance">3. คณะกรรมการการเงิน & สถานที่</option>
                                </select>
                            </div>

                            <!-- Role Title Input -->
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">บทบาทในคณะกรรมการ (เช่น ประธาน, กรรมการ) *</label>
                                <input type="text" 
                                       x-model="form.role_title" 
                                       required 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                       placeholder="เช่น ประธาน, รองประธาน, กรรมการ, เลขานุการ">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Role selector -->
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">ประเภทบุคลากร *</label>
                                <select x-model="form.role" 
                                        required 
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                                    <option value="member">กรรมการ / คณะทำงาน</option>
                                    <option value="advisor">ที่ปรึกษา</option>
                                </select>
                            </div>

                            <!-- Level Input -->
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">เลเวลของผัง (ระดับชั้น) *</label>
                                <input type="number" 
                                       x-model.number="form.level" 
                                       required 
                                       min="1"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                       placeholder="เช่น 1 (บนสุด), 2, 3">
                            </div>

                            <!-- Sort Order Input -->
                            <div class="space-y-1.5 text-left">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">อันดับในเลเวล *</label>
                                <input type="number" 
                                       x-model.number="form.sort_order" 
                                       required 
                                       min="1"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition" 
                                       placeholder="เช่น 1 (ซ้ายสุด), 2, 3">
                            </div>
                        </div>

                        <!-- Photo Selector -->
                        <div class="space-y-2 text-left">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">รูปถ่ายประจำตัว</label>
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-16 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 flex items-center justify-center p-1 overflow-hidden shrink-0 relative">
                                    <template x-if="form.previewUrl">
                                        <img :src="form.previewUrl" alt="Photo Preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!form.previewUrl">
                                        <span class="text-slate-400 text-lg">👤</span>
                                    </template>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="$refs.photoInput.click()" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-[10px] hover:bg-slate-800 transition">
                                            เลือกไฟล์รูปภาพ...
                                        </button>
                                        <template x-if="form.previewUrl">
                                            <button type="button" @click="removePhoto()" class="text-rose-500 hover:bg-rose-50 px-2 py-1.5 rounded-lg font-bold text-[10px] transition">
                                                ลบรูปถ่าย
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-slate-400">แนะนำเป็นภาพหน้าตรง อัตราส่วนครอบตัด 4:5 (ภาพแนวตั้ง)</p>
                                </div>
                            </div>
                            <input type="file" x-ref="photoInput" class="hidden" accept="image/*" @change="photoSelected($event)">
                        </div>
                    </div>

                    <!-- Cropper Canvas View (Visible only when cropping photo) -->
                    <div x-show="cropping" class="space-y-4 flex flex-col" x-cloak>
                        <div class="p-4 bg-slate-100 flex justify-center items-center overflow-hidden h-[300px] rounded-2xl">
                            <img id="orgCropperImage" class="max-w-full max-h-full block">
                        </div>
                        
                        <!-- Controls -->
                        <div class="flex justify-between items-center bg-white text-slate-500">
                            <span class="text-[10px] font-medium text-slate-400">ขนาดบังคับอัตราส่วนแนวตั้ง 4:5</span>
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

                <!-- Actions (Form View) -->
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
                        <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล'"></span>
                    </button>
                </div>

                <!-- Actions (Crop View) -->
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
                    <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบข้อมูลบุคลากร</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ต้องการลบข้อมูล <span class="font-bold text-slate-700" x-text="deleteModal.memberName"></span> จากโครงสร้างคณะกรรมการหรือไม่? การดำเนินการนี้ไม่สามารถย้อนคืนได้
                    </p>
                </div>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-150 text-slate-600 rounded-xl font-bold text-xs transition">
                        ยกเลิก
                    </button>
                    <button type="button" @click="deleteMember()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition shadow-md shadow-rose-100">
                        ยืนยันการลบ
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function orgManager() {
            return {
                loading: true,
                saving: false,
                members: [],
                cropping: false,
                selectedCommittee: 'operations',
                form: {
                    id: null,
                    name: '',
                    position: '',
                    role: 'member',
                    committee: 'operations',
                    role_title: '',
                    level: 1,
                    sort_order: 1,
                    photo_data: null,
                    previewUrl: null,
                    delete_photo: false
                },
                modal: {
                    open: false
                },
                deleteModal: {
                    open: false,
                    id: null,
                    memberName: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                cropper: null,
                selectedFile: null,

                init() {
                    this.fetchMembers();
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                fetchMembers() {
                    this.loading = true;
                    axios.get('{{ route('admin.org.data') }}')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.members = response.data.data;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching members:', error);
                            this.showToast('ไม่สามารถโหลดข้อมูลบุคลากรได้', 'error');
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                openCreateModal() {
                    const filtered = this.members.filter(m => (m.committee || 'operations') === this.selectedCommittee);
                    this.form = {
                        id: null,
                        name: '',
                        position: '',
                        role: 'member',
                        committee: this.selectedCommittee,
                        role_title: '',
                        level: 1,
                        sort_order: filtered.length ? Math.max(...filtered.map(m => m.sort_order)) + 1 : 1,
                        photo_data: null,
                        previewUrl: null,
                        delete_photo: false
                    };
                    this.cropping = false;
                    this.modal.open = true;
                },

                openEditModal(member) {
                    this.form = {
                        id: member.id,
                        name: member.name,
                        position: member.position,
                        role: member.role,
                        committee: member.committee || 'operations',
                        role_title: member.role_title || '',
                        level: member.level || 1,
                        sort_order: member.sort_order,
                        photo_data: null,
                        previewUrl: member.photo_url,
                        delete_photo: false
                    };
                    this.cropping = false;
                    this.modal.open = true;
                },

                photoSelected(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;
                    
                    this.selectedFile = files[0];
                    const reader = new FileReader();
                    
                    reader.onload = (e) => {
                        this.cropping = true;
                        this.$nextTick(() => {
                            const image = document.getElementById('orgCropperImage');
                            image.src = e.target.result;
                            
                            if (this.cropper) {
                                this.cropper.destroy();
                            }
                            
                            // Initialize Cropper for Vertical 4:5 ratio photo
                            this.cropper = new Cropper(image, {
                                aspectRatio: 4 / 5,
                                viewMode: 1,
                                autoCropArea: 1,
                                responsive: true,
                                checkOrientation: false
                            });
                        });
                    };
                    
                    reader.readAsDataURL(this.selectedFile);
                },

                cropImage() {
                    if (!this.cropper) return;
                    
                    // Crop dimensions
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 400,
                        height: 500
                    });
                    
                    this.form.photo_data = canvas.toDataURL('image/jpeg', 0.85);
                    this.form.previewUrl = this.form.photo_data;
                    this.form.delete_photo = false;
                    this.closeCropper();
                },

                closeCropper() {
                    this.cropping = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    this.$refs.photoInput.value = '';
                },

                removePhoto() {
                    this.form.photo_data = null;
                    this.form.previewUrl = null;
                    this.form.delete_photo = true;
                },

                saveMember() {
                    this.saving = true;
                    axios.post('{{ route('admin.org.save') }}', this.form)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.modal.open = false;
                                this.fetchMembers();
                            } else {
                                this.showToast(response.data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving member:', error);
                            this.showToast('เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                },

                confirmDelete(member) {
                    this.deleteModal.id = member.id;
                    this.deleteModal.memberName = member.name;
                    this.deleteModal.open = true;
                },

                deleteMember() {
                    axios.delete(`/admin/org/${this.deleteModal.id}`)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showToast(response.data.message, 'success');
                                this.deleteModal.open = false;
                                this.fetchMembers();
                            } else {
                                this.showToast(response.data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting member:', error);
                            this.showToast('เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
                        });
                }
            };
        }
    </script>
    @endpush
</x-layout>

