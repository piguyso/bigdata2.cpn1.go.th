<x-layout>
    <x-slot:title>จัดการคลังเอกสาร | IPST Chumphon</x-slot>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="documentManager()" x-init="init()">
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
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">จัดการคลังเอกสาร</h2>
                <p class="text-slate-500 text-sm mt-1">อัปโหลด แก้ไข และจัดการเอกสารเผยแพร่ ประกาศ คู่มือ หรือแบบฟอร์มต่าง ๆ</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> อัปโหลดเอกสารใหม่
                </button>
            </div>
        </header>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังโหลดเอกสาร...</p>
        </div>

        <!-- Documents Table View -->
        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-450 uppercase font-bold tracking-wider">
                            <th class="py-4 px-6 w-16 text-center">ประเภท</th>
                            <th class="py-4 px-6">ชื่อไฟล์ / รายละเอียด</th>
                            <th class="py-4 px-6 w-28">ขนาดไฟล์</th>
                            <th class="py-4 px-6 w-24 text-center">ดาวน์โหลด</th>
                            <th class="py-4 px-6 w-20 text-center">ลำดับ</th>
                            <th class="py-4 px-6 w-32 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <template x-if="documents.length === 0">
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 font-bold">
                                    <div class="text-3xl mb-2">📁</div>
                                    ไม่มีเอกสารในระบบ
                                </td>
                            </tr>
                        </template>
                        <template x-for="doc in documents" :key="doc.id">
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="py-4 px-6 text-center">
                                    <!-- File Extension Badge -->
                                    <span class="inline-flex w-10 h-10 rounded-xl items-center justify-center font-bold text-[10px] uppercase text-white shadow-sm"
                                          :class="getFileTypeClass(doc.file_type)"
                                          x-text="doc.file_type || 'FILE'">
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800 text-sm" x-text="doc.title"></div>
                                    <div class="text-slate-400 text-[10px] mt-0.5" x-text="doc.description || '- ไม่มีรายละเอียด -'"></div>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-500" x-text="doc.file_size || 'N/A'"></td>
                                <td class="py-4 px-6 text-center font-extrabold text-emerald-600">
                                    <span class="bg-emerald-50 px-2.5 py-1 rounded-md text-[10px]" x-text="doc.download_count"></span>
                                </td>
                                <td class="py-4 px-6 text-center font-semibold text-slate-500" x-text="doc.sort_order"></td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    <a :href="'/documents/download/' + doc.id" class="bg-slate-50 text-slate-600 hover:text-emerald-600 border border-slate-100 hover:bg-emerald-50 w-8 h-8 rounded-lg inline-flex items-center justify-center transition shadow-sm cursor-pointer" title="ดาวน์โหลดไฟล์">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <button type="button" @click="openEditModal(doc)" class="bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-100 hover:bg-indigo-50 w-8 h-8 rounded-lg inline-flex items-center justify-center transition shadow-sm cursor-pointer" title="แก้ไขเอกสาร">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="confirmDelete(doc)" class="bg-slate-50 text-rose-500 hover:text-rose-600 border border-slate-100 hover:bg-rose-50 w-8 h-8 rounded-lg inline-flex items-center justify-center transition shadow-sm cursor-pointer" title="ลบเอกสาร">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Document Modal -->
        <div x-show="modal.open" 
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6"
             x-cloak>
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="modal.open = false"></div>

            <!-- Content -->
            <div x-show="modal.open"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                
                <header class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-800 text-sm md:text-base" x-text="modal.isEdit ? 'แก้ไขข้อมูลเอกสาร' : 'อัปโหลดเอกสารใหม่'"></h3>
                    <button type="button" @click="modal.open = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </header>

                <form @submit.prevent="saveForm()" class="overflow-y-auto p-6 space-y-5 flex-1">
                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">ชื่อเอกสาร / หัวข้อ <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.title" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800" placeholder="ระบุชื่อเรียกของเอกสาร เช่น คู่มือการอบรมแกนนำวิทยาศาสตร์">
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">รายละเอียดเพิ่มเติม (ระบุหรือไม่ก็ได้)</label>
                        <textarea x-model="form.description" rows="3" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800" placeholder="อธิบายสั้น ๆ เกี่ยวกับเนื้อหาในเอกสาร"></button></textarea>
                    </div>

                    <!-- File input -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">
                            ไฟล์เอกสาร 
                            <span class="text-rose-500" x-show="!modal.isEdit">*</span>
                            <span class="text-slate-400 font-normal" x-show="modal.isEdit">(อัปโหลดไฟล์ใหม่หากต้องการแก้ไขไฟล์เดิม)</span>
                        </label>
                        <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-6 text-center hover:bg-slate-100/50 transition relative group">
                            <input type="file" @change="handleFileUpload($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                            <div class="space-y-2">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mx-auto text-lg group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="text-[11px] font-bold text-slate-600" x-text="form.fileName || 'ลากไฟล์มาวาง หรือคลิกเพื่อเลือกไฟล์'"></div>
                                <div class="text-[9px] text-slate-400">รองรับ PDF, DOCX, XLSX, PPTX, ZIP (สูงสุด 15MB)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">ลำดับการแสดงผล</label>
                        <input type="number" x-model.number="form.sort_order" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800">
                        <p class="text-[9px] text-slate-400">ลำดับน้อยกว่าจะแสดงผลก่อน</p>
                    </div>

                    <!-- Footer buttons inside form to keep them together -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="modal.open = false" class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-200 transition cursor-pointer">ยกเลิก</button>
                        <button type="submit" :disabled="modal.saving" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 cursor-pointer disabled:opacity-50 flex items-center gap-2">
                            <span x-show="modal.saving" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="modal.saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModal.open" 
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6"
             x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="deleteModal.open = false"></div>

            <div x-show="deleteModal.open"
                 x-transition:enter="transition ease-out duration-350 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-100 p-6 space-y-4 text-center">
                
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto shadow-inner">
                    <i class="fa-solid fa-trash-can"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบเอกสาร?</h3>
                    <p class="text-slate-400 text-[10px] leading-relaxed">คุณกำลังลบไฟล์เอกสาร "<span class="font-bold text-slate-600" x-text="deleteModal.title"></span>" การกระทำนี้ไม่สามารถย้อนกลับได้</p>
                </div>

                <div class="pt-2 flex items-center justify-center gap-3">
                    <button type="button" @click="deleteModal.open = false" class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-200 transition cursor-pointer">ยกเลิก</button>
                    <button type="button" @click="executeDelete()" :disabled="deleteModal.deleting" class="bg-rose-500 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-600 transition shadow-md shadow-rose-100 cursor-pointer disabled:opacity-50 flex items-center gap-2">
                        <span x-show="deleteModal.deleting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="deleteModal.deleting ? 'กำลังลบ...' : 'ยืนยันลบ'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function documentManager() {
            return {
                documents: [],
                loading: false,
                toast: {
                    show: false,
                    type: 'success',
                    message: ''
                },
                modal: {
                    open: false,
                    isEdit: false,
                    saving: false
                },
                deleteModal: {
                    open: false,
                    id: null,
                    title: '',
                    deleting: false
                },
                form: {
                    id: null,
                    title: '',
                    description: '',
                    sort_order: 0,
                    file: null,
                    fileName: ''
                },

                init() {
                    this.loadDocuments();
                },

                showToast(type, message) {
                    this.toast.show = true;
                    this.toast.type = type;
                    this.toast.message = message;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                async loadDocuments() {
                    this.loading = true;
                    try {
                        const res = await axios.get('{{ route('admin.documents.data') }}');
                        if (res.data.status === 'success') {
                            this.documents = res.data.data;
                        } else {
                            this.showToast('error', res.data.message || 'โหลดเอกสารล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast('error', 'ระบบขัดข้องกรุณาลองใหม่อีกครั้ง');
                    } finally {
                        this.loading = false;
                    }
                },

                openCreateModal() {
                    this.modal.isEdit = false;
                    this.form = {
                        id: null,
                        title: '',
                        description: '',
                        sort_order: this.documents.length ? Math.max(...this.documents.map(d => d.sort_order)) + 10 : 10,
                        file: null,
                        fileName: ''
                    };
                    this.modal.open = true;
                },

                openEditModal(doc) {
                    this.modal.isEdit = true;
                    this.form = {
                        id: doc.id,
                        title: doc.title,
                        description: doc.description || '',
                        sort_order: doc.sort_order,
                        file: null,
                        fileName: doc.file_path ? doc.file_path.split('/').pop() : ''
                    };
                    this.modal.open = true;
                },

                handleFileUpload(event) {
                    const files = event.target.files;
                    if (files.length > 0) {
                        this.form.file = files[0];
                        this.form.fileName = files[0].name;
                    }
                },

                async saveForm() {
                    if (!this.form.title.trim()) {
                        this.showToast('error', 'กรุณาระบุชื่อเอกสาร');
                        return;
                    }
                    if (!this.modal.isEdit && !this.form.file) {
                        this.showToast('error', 'กรุณาอัปโหลดไฟล์เอกสาร');
                        return;
                    }

                    this.modal.saving = true;
                    try {
                        const formData = new FormData();
                        if (this.form.id) formData.append('id', this.form.id);
                        formData.append('title', this.form.title);
                        formData.append('description', this.form.description || '');
                        formData.append('sort_order', this.form.sort_order);
                        if (this.form.file) {
                            formData.append('file', this.form.file);
                        }

                        const res = await axios.post('{{ route('admin.documents.save') }}', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        if (res.data.status === 'success') {
                            this.showToast('success', res.data.message);
                            this.modal.open = false;
                            this.loadDocuments();
                        } else {
                            this.showToast('error', res.data.message || 'บันทึกข้อมูลล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        const errors = err.response?.data?.errors;
                        if (errors) {
                            const firstErr = Object.values(errors)[0][0];
                            this.showToast('error', firstErr);
                        } else {
                            this.showToast('error', 'ไม่สามารถบันทึกเอกสารได้');
                        }
                    } finally {
                        this.modal.saving = false;
                    }
                },

                confirmDelete(doc) {
                    this.deleteModal.id = doc.id;
                    this.deleteModal.title = doc.title;
                    this.deleteModal.deleting = false;
                    this.deleteModal.open = true;
                },

                async executeDelete() {
                    this.deleteModal.deleting = true;
                    try {
                        const res = await axios.delete(`/admin/documents/${this.deleteModal.id}`);
                        if (res.data.status === 'success') {
                            this.showToast('success', res.data.message);
                            this.deleteModal.open = false;
                            this.loadDocuments();
                        } else {
                            this.showToast('error', res.data.message || 'ลบข้อมูลล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast('error', 'ระบบขัดข้องกรุณาลองใหม่อีกครั้ง');
                    } finally {
                        this.deleteModal.deleting = false;
                    }
                },

                getFileTypeClass(type) {
                    const ext = (type || '').toLowerCase();
                    if (['pdf'].includes(ext)) return 'bg-rose-500 shadow-rose-100';
                    if (['doc', 'docx'].includes(ext)) return 'bg-blue-500 shadow-blue-100';
                    if (['xls', 'xlsx'].includes(ext)) return 'bg-emerald-600 shadow-emerald-100';
                    if (['ppt', 'pptx'].includes(ext)) return 'bg-orange-500 shadow-orange-100';
                    if (['zip', 'rar', '7z'].includes(ext)) return 'bg-purple-500 shadow-purple-100';
                    return 'bg-slate-500 shadow-slate-100';
                }
            };
        }
    </script>
    @endpush
</x-layout>
