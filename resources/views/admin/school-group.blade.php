<x-layout>
    <x-slot:title>จัดการเครือข่ายสถานศึกษา | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-5xl mx-auto px-6" x-data="schoolGroupManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message"
             x-cloak></div>

        <header class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">School Groups</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">จัดการเครือข่ายสถานศึกษา</h2>
                <p class="text-slate-500 text-sm mt-1">เพิ่ม แก้ไข และลบข้อมูลจากตาราง system_group</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.schools.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-school"></i> จัดการโรงเรียน
                </a>
                <button type="button" @click="openCreateModal()" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> เพิ่มเครือข่าย
                </button>
            </div>
        </header>

        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังโหลดข้อมูลเครือข่าย...</p>
        </div>

        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                            <th class="py-4 px-6 w-32">รหัส</th>
                            <th class="py-4 px-6">ชื่อเครือข่าย</th>
                            <th class="py-4 px-6 w-40 text-center">จำนวนโรงเรียน</th>
                            <th class="py-4 px-6 w-32 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="groups.length === 0">
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400 font-medium">ยังไม่มีข้อมูลเครือข่ายสถานศึกษา</td>
                            </tr>
                        </template>
                        <template x-for="group in groups" :key="group.id">
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 rounded-md font-extrabold" x-text="group.code"></span>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-800" x-text="group.name"></td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md font-bold text-[10px]" x-text="group.schools_count + ' โรงเรียน'"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal(group)" class="text-slate-500 hover:text-orange-600 hover:bg-orange-50 px-2 py-2 rounded-lg transition" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(group)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition" title="ลบ">
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

        <div x-show="modal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <form @submit.prevent="saveGroup()" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/70">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-orange-500"></i>
                        <span x-text="form.id ? 'แก้ไขเครือข่ายสถานศึกษา' : 'เพิ่มเครือข่ายสถานศึกษา'"></span>
                    </h3>
                    <button type="button" @click="modal.open = false" class="text-slate-400 hover:text-slate-700 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-slate-500">รหัสเครือข่าย *</span>
                        <input type="text" x-model="form.code" required maxlength="2" class="form-input" placeholder="เช่น 01">
                    </label>
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-slate-500">ชื่อเครือข่าย *</span>
                        <input type="text" x-model="form.name" required maxlength="150" class="form-input" placeholder="เช่น เมืองชุมพร 1">
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/70">
                    <button type="button" @click="modal.open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition">ยกเลิก</button>
                    <button type="submit" :disabled="saving" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition inline-flex items-center gap-2">
                        <i x-show="saving" class="fa-solid fa-circle-notch animate-spin"></i>
                        <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div x-show="deleteModal.open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center text-xl mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบเครือข่าย</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบ <span class="font-bold text-slate-700" x-text="deleteModal.name"></span> หรือไม่?</p>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button>
                    <button type="button" @click="deleteGroup()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .form-input {
                width: 100%;
                border: 1px solid #e2e8f0;
                border-radius: 0.75rem;
                background: #f8fafc;
                padding: 0.625rem 1rem;
                font-size: 0.75rem;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            }
            .form-input:focus {
                background: #fff;
                border-color: #f97316;
                box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
            }
        </style>
        <script>
            function schoolGroupManager() {
                const blankForm = () => ({ id: null, code: '', name: '' });

                return {
                    loading: true,
                    saving: false,
                    groups: [],
                    form: blankForm(),
                    modal: { open: false },
                    deleteModal: { open: false, id: null, name: '' },
                    toast: { show: false, message: '', type: 'success' },

                    init() {
                        this.fetchGroups();
                    },

                    fetchGroups() {
                        this.loading = true;
                        axios.get('{{ route('admin.school-group.data') }}')
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.groups = response.data.data;
                                }
                            })
                            .catch(() => this.showToast('ไม่สามารถโหลดข้อมูลเครือข่ายได้', 'error'))
                            .finally(() => this.loading = false);
                    },

                    openCreateModal() {
                        this.form = blankForm();
                        this.modal.open = true;
                    },

                    openEditModal(group) {
                        this.form = { id: group.id, code: group.code, name: group.name };
                        this.modal.open = true;
                    },

                    saveGroup() {
                        this.saving = true;
                        axios.post('{{ route('admin.school-group.save') }}', this.form)
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.showToast(response.data.message, 'success');
                                    this.modal.open = false;
                                    this.fetchGroups();
                                } else {
                                    this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
                                }
                            })
                            .catch(error => {
                                const msg = error.response?.data?.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                                this.showToast(msg, 'error');
                            })
                            .finally(() => this.saving = false);
                    },

                    confirmDelete(group) {
                        this.deleteModal = { open: true, id: group.id, name: group.name };
                    },

                    deleteGroup() {
                        const id = this.deleteModal.id;
                        this.deleteModal.open = false;
                        axios.delete(`{{ url('/admin/school-group') }}/${id}`)
                            .then(response => {
                                this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', response.data.status === 'success' ? 'success' : 'error');
                                this.fetchGroups();
                            })
                            .catch(error => {
                                const msg = error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล';
                                this.showToast(msg, 'error');
                            });
                    },

                    showToast(message, type = 'success') {
                        this.toast = { show: true, message, type };
                        setTimeout(() => this.toast.show = false, 3500);
                    }
                };
            }
        </script>
    @endpush
</x-layout>
