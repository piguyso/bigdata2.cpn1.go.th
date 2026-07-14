<x-layout>
    <x-slot:title>จัดการปีการศึกษา | BigData สพป.ชพ.1</x-slot>

    <div class="py-10 max-w-5xl mx-auto px-6" x-data="academicYearManager()" x-init="init()">
        <div x-show="toast.show"
             x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message"
             x-cloak></div>

        <header class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">Academic Years</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">จัดการปีการศึกษา</h2>
                <p class="text-slate-500 text-sm mt-1">กำหนดปีการศึกษากลางสำหรับแยกชุดข้อมูลในระบบ</p>
            </div>
            <button type="button" @click="openCreateModal()" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i> เพิ่มปีการศึกษา
            </button>
        </header>

        <section class="mb-5 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ปีการศึกษาปัจจุบัน</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="activeYear ? activeYear : '-'"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">จำนวนปีทั้งหมด</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="years.length"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">สถานะระบบ</p>
                <p class="text-sm font-extrabold mt-2" :class="activeYear ? 'text-emerald-600' : 'text-rose-600'" x-text="activeYear ? 'พร้อมแยกข้อมูลตามปี' : 'ยังไม่ได้ตั้งปีปัจจุบัน'"></p>
            </div>
        </section>

        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังโหลดข้อมูลปีการศึกษา...</p>
        </div>

        <div x-show="!loading" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                            <th class="py-4 px-6 w-32">ปีการศึกษา</th>
                            <th class="py-4 px-6">ชื่อเรียก</th>
                            <th class="py-4 px-6 w-52">ช่วงวันที่</th>
                            <th class="py-4 px-6 w-32 text-center">สถานะ</th>
                            <th class="py-4 px-6 w-44 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="years.length === 0">
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 font-medium">ยังไม่มีข้อมูลปีการศึกษา</td>
                            </tr>
                        </template>
                        <template x-for="year in years" :key="year.id">
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 rounded-md font-extrabold" x-text="year.year"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800" x-text="year.name"></div>
                                    <div class="text-[10px] text-slate-400 mt-1" x-text="'ลำดับ: ' + year.sort_order"></div>
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    <span x-text="formatRange(year)"></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-md font-bold text-[10px]"
                                          :class="year.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                          x-text="year.is_active ? 'ปีปัจจุบัน' : 'ทั่วไป'"></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="setActive(year)" class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-2 rounded-lg transition disabled:opacity-30" :disabled="year.is_active" title="ตั้งเป็นปีปัจจุบัน">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </button>
                                        <button type="button" @click="openEditModal(year)" class="text-slate-500 hover:text-orange-600 hover:bg-orange-50 px-2 py-2 rounded-lg transition" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete(year)" class="text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-2 rounded-lg transition disabled:opacity-30" :disabled="year.is_active" title="ลบ">
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
            <form @submit.prevent="saveYear()" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/70">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-orange-500"></i>
                        <span x-text="form.id ? 'แก้ไขปีการศึกษา' : 'เพิ่มปีการศึกษา'"></span>
                    </h3>
                    <button type="button" @click="modal.open = false" class="text-slate-400 hover:text-slate-700 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">ปีการศึกษา *</span>
                            <input type="text" x-model="form.year" required maxlength="4" pattern="[0-9]{4}" class="form-input" placeholder="เช่น 2569">
                        </label>
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">ลำดับ</span>
                            <input type="number" x-model="form.sort_order" min="0" class="form-input">
                        </label>
                    </div>
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-slate-500">ชื่อเรียก</span>
                        <input type="text" x-model="form.name" maxlength="100" class="form-input" placeholder="เช่น ปีการศึกษา 2569">
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">วันที่เริ่มต้น</span>
                            <input type="date" x-model="form.starts_at" class="form-input">
                        </label>
                        <label class="space-y-1.5 block">
                            <span class="text-xs font-bold text-slate-500">วันที่สิ้นสุด</span>
                            <input type="date" x-model="form.ends_at" class="form-input">
                        </label>
                    </div>
                    <label class="flex items-center gap-3 bg-slate-50 rounded-xl border border-slate-100 px-4 py-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-xs font-bold text-slate-600">ตั้งเป็นปีการศึกษาปัจจุบัน</span>
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
                <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบปีการศึกษา</h3>
                <p class="text-xs text-slate-400 leading-relaxed mt-2 mb-6">ต้องการลบ <span class="font-bold text-slate-700" x-text="deleteModal.name"></span> หรือไม่?</p>
                <div class="flex gap-2.5">
                    <button type="button" @click="deleteModal.open = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">ยกเลิก</button>
                    <button type="button" @click="deleteYear()" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">ยืนยันการลบ</button>
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
            function academicYearManager() {
                const blankForm = () => ({
                    id: null,
                    year: '',
                    name: '',
                    starts_at: '',
                    ends_at: '',
                    is_active: false,
                    sort_order: ''
                });

                return {
                    loading: true,
                    saving: false,
                    years: [],
                    activeYear: null,
                    form: blankForm(),
                    modal: { open: false },
                    deleteModal: { open: false, id: null, name: '' },
                    toast: { show: false, message: '', type: 'success' },

                    init() {
                        this.fetchYears();
                    },

                    fetchYears() {
                        this.loading = true;
                        axios.get('{{ route('admin.academic-years.data') }}')
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.years = response.data.data;
                                    this.activeYear = response.data.active_year;
                                }
                            })
                            .catch(() => this.showToast('ไม่สามารถโหลดข้อมูลปีการศึกษาได้', 'error'))
                            .finally(() => this.loading = false);
                    },

                    openCreateModal() {
                        this.form = blankForm();
                        this.form.year = this.nextYear();
                        this.form.name = this.form.year ? 'ปีการศึกษา ' + this.form.year : '';
                        this.form.sort_order = this.form.year;
                        this.modal.open = true;
                    },

                    openEditModal(year) {
                        this.form = {
                            id: year.id,
                            year: year.year,
                            name: year.name,
                            starts_at: year.starts_at || '',
                            ends_at: year.ends_at || '',
                            is_active: !!year.is_active,
                            sort_order: year.sort_order
                        };
                        this.modal.open = true;
                    },

                    saveYear() {
                        this.saving = true;
                        axios.post('{{ route('admin.academic-years.save') }}', this.form)
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.showToast(response.data.message, 'success');
                                    this.modal.open = false;
                                    this.fetchYears();
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

                    setActive(year) {
                        axios.post(`{{ url('/admin/academic-years') }}/${year.id}/active`)
                            .then(response => {
                                this.showToast(response.data.message || 'ตั้งปีปัจจุบันเรียบร้อยแล้ว', response.data.status === 'success' ? 'success' : 'error');
                                this.fetchYears();
                            })
                            .catch(() => this.showToast('เกิดข้อผิดพลาดในการตั้งปีปัจจุบัน', 'error'));
                    },

                    confirmDelete(year) {
                        this.deleteModal = { open: true, id: year.id, name: year.name };
                    },

                    deleteYear() {
                        const id = this.deleteModal.id;
                        this.deleteModal.open = false;
                        axios.delete(`{{ url('/admin/academic-years') }}/${id}`)
                            .then(response => {
                                this.showToast(response.data.message || 'ลบข้อมูลเรียบร้อยแล้ว', response.data.status === 'success' ? 'success' : 'error');
                                this.fetchYears();
                            })
                            .catch(error => {
                                const msg = error.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล';
                                this.showToast(msg, 'error');
                            });
                    },

                    formatRange(year) {
                        if (!year.starts_at && !year.ends_at) return '-';
                        return `${year.starts_at || '-'} ถึง ${year.ends_at || '-'}`;
                    },

                    nextYear() {
                        if (this.years.length === 0) {
                            return String(new Date().getFullYear() + 543);
                        }
                        const maxYear = Math.max(...this.years.map(item => parseInt(item.year, 10)).filter(Boolean));
                        return String(maxYear + 1);
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
