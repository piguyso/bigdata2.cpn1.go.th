<x-layout>
    <x-slot:title>นำเข้ารายชื่อโรงเรียน | BigData</x-slot>

    <div class="py-10 max-w-7xl mx-auto px-6" x-data="schoolImportManager()" x-init="init()">

        {{-- Toast --}}
        <div x-show="toast.show" x-transition
             class="fixed bottom-5 right-5 z-50 px-5 py-4 rounded-2xl shadow-xl text-white text-xs font-bold"
             :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
             x-text="toast.message" x-cloak></div>

        {{-- Delete confirm modal --}}
        <div x-show="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 space-y-5">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-rose-50 border border-rose-100 rounded-2xl flex items-center justify-center mx-auto text-rose-500 text-2xl"><i class="fa-solid fa-trash-can"></i></div>
                    <h3 class="font-extrabold text-slate-800 text-lg">ยืนยันลบข้อมูลโรงเรียน</h3>
                    <p class="text-xs text-slate-500">การลบจะลบข้อมูลโรงเรียนทั้งหมดในเขตพื้นที่ <strong x-text="areaName"></strong> และประวัติการนำเข้าทั้งหมด</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="deleteModal = false" class="flex-1 bg-slate-100 text-slate-700 px-5 py-3 rounded-xl font-bold text-xs hover:bg-slate-200 transition">ยกเลิก</button>
                    <button type="button" @click="confirmDelete()" :disabled="deleteLoading" class="flex-1 bg-rose-600 text-white px-5 py-3 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition">
                        <i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                        <span x-text="deleteLoading ? 'กำลังลบ...' : 'ยืนยันลบ'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete log confirm modal --}}
        <div x-show="deleteLogModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 space-y-5">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-rose-50 border border-rose-100 rounded-2xl flex items-center justify-center mx-auto text-rose-500 text-2xl"><i class="fa-solid fa-trash-can"></i></div>
                    <h3 class="font-extrabold text-slate-800 text-lg">ยืนยันลบประวัติและข้อมูลชุดนี้</h3>
                    <p class="text-xs text-slate-500">การลบจะลบประวัติการนำเข้าและข้อมูลโรงเรียนของเขตนี้เพื่อย้อนกลับสถานะ</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="deleteLogModal = false" class="flex-1 bg-slate-100 text-slate-700 px-5 py-3 rounded-xl font-bold text-xs hover:bg-slate-200 transition">ยกเลิก</button>
                    <button type="button" @click="confirmDeleteLog()" :disabled="deleteLoading" class="flex-1 bg-rose-600 text-white px-5 py-3 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition">
                        <i x-show="deleteLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                        <span x-text="deleteLoading ? 'กำลังลบ...' : 'ยืนยันลบ'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Header --}}
        <header class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold text-orange-600 uppercase tracking-wider">BOPP DMC School Import</p>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">นำเข้ารายชื่อโรงเรียน</h2>
                <p class="text-slate-500 text-sm mt-1">ดึงข้อมูลโรงเรียนจากไฟล์ DMC ของ BOPP กรองเฉพาะเขตพื้นที่ปัจจุบัน แล้วบันทึกลงตาราง system_school</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.schools.index') }}" class="bg-white border border-slate-200 text-slate-650 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-school"></i> จัดการโรงเรียน
                </a>
            </div>
        </header>

        {{-- Summary Cards --}}
        <section class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">เขตพื้นที่</p>
                <p class="text-sm font-extrabold text-slate-900 mt-1" x-text="areaName || '-'"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">dmc_schools (เขตนี้)</p>
                <p class="text-2xl font-extrabold text-orange-600 mt-1" x-text="dmcAreaCount"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">โรงเรียนในระบบ (เขตนี้)</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="areaCount"></p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">dmc_schools ทั้งหมด</p>
                <p class="text-2xl font-extrabold text-slate-500 mt-1" x-text="dmcTotal"></p>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] gap-6 items-start">

            {{-- Main import panel --}}
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-orange-500"></i>
                        ดึงและตรวจสอบข้อมูลจาก BOPP
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Source Info --}}
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-database text-emerald-500"></i>
                            <span class="text-[10px] font-bold text-emerald-600 uppercase">แหล่งข้อมูล: Local DB (dmc_schools)</span>
                        </div>
                        <div class="text-emerald-700 font-bold">โรงเรียนในไฟล์ DMC ทั้งหมด: <span x-text="dmcTotal.toLocaleString()"></span> แห่ง</div>
                        <div class="text-emerald-600">ตรงกับเขตพื้นที่นี้: <span class="font-bold" x-text="dmcAreaCount.toLocaleString()"></span> แห่ง</div>
                        <div x-show="dmcTotal === 0" class="mt-2 text-amber-600 font-bold">
                            ⚠ ยังไม่มีข้อมูลใน dmc_schools — กรุณารัน: <code>php artisan db:seed --class=DmcSchoolSeeder</code>
                        </div>
                    </div>

                    {{-- Mode & Area --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">โหมดการนำเข้า</span>
                            <select x-model="form.mode" class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold outline-none focus:border-orange-500">
                                <option value="replace">Replace — แทนที่ข้อมูลเดิมของเขตนี้ทั้งหมด</option>
                                <option value="merge">Merge — เพิ่ม/อัปเดตโดยใช้รหัส SMIS เป็น key</option>
                            </select>
                        </label>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">เขตพื้นที่ที่จะนำเข้า</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800" x-text="areaName || '-'"></div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="areaCode"></div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="previewImport()" :disabled="previewLoading"
                                class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-orange-700 disabled:opacity-50 transition shadow-md shadow-orange-100 inline-flex items-center justify-center gap-2">
                            <i x-show="previewLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!previewLoading" class="fa-solid fa-magnifying-glass-chart"></i>
                            <span x-text="previewLoading ? 'กำลังดาวน์โหลด...' : 'ตรวจสอบก่อนนำเข้า'"></span>
                        </button>
                        <button type="button" @click="runImport()" :disabled="importLoading || !preview"
                                class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 disabled:opacity-50 transition shadow-md shadow-emerald-100 inline-flex items-center justify-center gap-2">
                            <i x-show="importLoading" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i x-show="!importLoading" class="fa-solid fa-database"></i>
                            <span x-text="importLoading ? 'กำลังนำเข้า...' : 'ยืนยันนำเข้าข้อมูล'"></span>
                        </button>
                        <button type="button" @click="deleteModal = true" :disabled="deleteLoading || areaCount === 0"
                                class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-rose-700 disabled:opacity-50 transition shadow-md shadow-rose-100 inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-trash-can"></i>
                            ลบข้อมูลทั้งหมด
                        </button>
                    </div>

                    {{-- Preview Result --}}
                    <div x-show="preview" class="space-y-4" x-cloak>
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-3">ผลการตรวจสอบ</p>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">ทั้งหมดในไฟล์</p>
                                    <p class="text-xl font-extrabold text-slate-900 mt-1" x-text="(preview?.rows_fetched ?? 0).toLocaleString()"></p>
                                </div>
                                <div class="bg-orange-50 border border-orange-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-orange-400 uppercase">ตรงเขตพื้นที่</p>
                                    <p class="text-xl font-extrabold text-orange-700 mt-1" x-text="(preview?.rows_filtered ?? 0).toLocaleString()"></p>
                                </div>
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">ในระบบ (เขตนี้)</p>
                                    <p class="text-xl font-extrabold text-slate-900 mt-1" x-text="(preview?.rows_existing_area ?? 0).toLocaleString()"></p>
                                </div>
                                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl px-4 py-3">
                                    <p class="text-[10px] font-bold text-emerald-500 uppercase">จะนำเข้า</p>
                                    <p class="text-xl font-extrabold text-emerald-700 mt-1" x-text="(preview?.rows_filtered ?? 0).toLocaleString()"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Sample table --}}
                        <div x-show="preview?.sample?.length > 0" class="border border-slate-100 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                <h4 class="font-bold text-slate-700 text-xs">ตัวอย่างข้อมูล (15 รายการแรก)</h4>
                                <span class="text-[10px] text-slate-400 font-bold" x-text="(preview?.rows_filtered ?? 0) + ' รายการที่ตรงเขต'"></span>
                            </div>
                            <div class="overflow-x-auto max-h-80">
                                <table class="w-full text-[10px]">
                                    <thead class="bg-slate-50 border-b border-slate-100 sticky top-0">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">SMIS</th>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">รหัส 10 หลัก</th>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">ชื่อโรงเรียน</th>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">ตำบล</th>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">อำเภอ</th>
                                            <th class="text-left px-3 py-2 font-bold text-slate-500 whitespace-nowrap">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(row, i) in (preview?.sample ?? [])" :key="i">
                                            <tr class="hover:bg-slate-50 transition">
                                                <td class="px-3 py-2 font-mono text-slate-600" x-text="row.smis"></td>
                                                <td class="px-3 py-2 font-mono text-slate-500" x-text="row.ministry"></td>
                                                <td class="px-3 py-2 text-slate-800 font-bold max-w-xs truncate" x-text="row.schoolname"></td>
                                                <td class="px-3 py-2 text-slate-500" x-text="row.tambon"></td>
                                                <td class="px-3 py-2 text-slate-500" x-text="row.amper"></td>
                                                <td class="px-3 py-2">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                          :class="row.statusID === '1' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600'"
                                                          x-text="row.statusDetail || (row.statusID === '1' ? 'เปิด' : 'ปิด')"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- No match warning --}}
                        <div x-show="preview && preview.rows_filtered === 0"
                             class="bg-amber-50 border border-amber-100 rounded-2xl px-4 py-4 text-xs text-amber-700">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                            ไม่พบโรงเรียนที่ตรงกับรหัสเขตพื้นที่ <strong x-text="areaCode"></strong> ในไฟล์ที่ดาวน์โหลด กรุณาตรวจสอบการตั้งค่า area_code ในระบบ
                        </div>
                    </div>
                </div>
            </section>

            {{-- History sidebar --}}
            <section class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>
                        ประวัติการนำเข้า
                    </h3>
                </div>

                <div class="divide-y divide-slate-100 max-h-[640px] overflow-y-auto">
                    <template x-if="logs.length === 0">
                        <div class="px-6 py-8 text-center text-slate-400 text-xs font-bold">ยังไม่มีประวัติการนำเข้า</div>
                    </template>
                    <template x-for="log in logs" :key="log.id">
                        <div class="px-5 py-4 hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <span class="text-xs font-extrabold text-slate-800" x-text="log.rows_imported?.toLocaleString() + ' รายการ'"></span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full"
                                          :class="log.mode === 'replace' ? 'bg-orange-50 text-orange-600' : 'bg-blue-50 text-blue-600'"
                                          x-text="log.mode === 'replace' ? 'Replace' : 'Merge'"></span>
                                    <button type="button" 
                                            @click="openDeleteLogModal(log)" 
                                            class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-rose-50 border border-slate-100 bg-white shadow-sm shrink-0"
                                            title="ลบประวัตินี้">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-[10px] text-slate-400 font-bold">
                                <span x-text="'กรอง ' + log.rows_filtered + ' จาก ' + log.rows_fetched + ' แถว'"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                                <i class="fa-solid fa-user-circle"></i>
                                <span x-text="log.created_by_name || 'ระบบ'"></span>
                                <i class="fa-solid fa-clock ml-1"></i>
                                <span x-text="formatDate(log.created_at)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

        </div>
    </div>

    @push('scripts')
    <script>
    function schoolImportManager() {
        return {
            areaCode: '',
            areaName: '',
            schoolCount: 0,
            areaCount: 0,
            dmcTotal: 0,
            dmcAreaCount: 0,
            logs: [],
            preview: null,
            form: { mode: 'replace' },
            previewLoading: false,
            importLoading: false,
            deleteLoading: false,
            deleteModal: false,
            deleteLogModal: false,
            selectedLog: null,
            toast: { show: false, type: 'success', message: '' },

            init() {
                window.addEventListener('load', () => this.loadData());
            },

            async loadData() {
                try {
                    const res = await axios.get('{{ route('admin.school-import.data') }}');
                    if (res.data.status === 'success') {
                        this.areaCode     = res.data.area_code;
                        this.areaName     = res.data.area_name;
                        this.schoolCount  = res.data.school_count;
                        this.areaCount    = res.data.area_count;
                        this.dmcTotal     = res.data.dmc_total ?? 0;
                        this.dmcAreaCount = res.data.dmc_area_count ?? 0;
                        this.logs         = res.data.logs || [];
                    }
                } catch (e) {
                    this.showToast('ไม่สามารถโหลดข้อมูลได้', 'error');
                }
            },

            async previewImport() {
                this.previewLoading = true;
                this.preview = null;
                try {
                    const res = await axios.post('{{ route('admin.school-import.preview') }}', {
                        mode: this.form.mode,
                        _token: document.querySelector('meta[name=csrf-token]')?.content,
                    });
                    if (res.data.status === 'success') {
                        this.preview = res.data.preview;
                        this.showToast('ตรวจสอบข้อมูลเรียบร้อย พบ ' + (res.data.preview?.rows_filtered ?? 0) + ' รายการ', 'success');
                    } else {
                        this.showToast(res.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                } catch (e) {
                    this.showToast(e.response?.data?.message || 'ไม่สามารถดาวน์โหลดข้อมูลได้', 'error');
                } finally {
                    this.previewLoading = false;
                }
            },

            async runImport() {
                if (!this.preview || this.preview.rows_filtered === 0) {
                    this.showToast('กรุณาตรวจสอบข้อมูลก่อนนำเข้า', 'error');
                    return;
                }
                this.importLoading = true;
                try {
                    const res = await axios.post('{{ route('admin.school-import.import') }}', {
                        mode: this.form.mode,
                        _token: document.querySelector('meta[name=csrf-token]')?.content,
                    });
                    if (res.data.status === 'success') {
                        this.showToast(res.data.message, 'success');
                        this.preview = null;
                        await this.loadData();
                    } else {
                        this.showToast(res.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                } catch (e) {
                    this.showToast(e.response?.data?.message || 'เกิดข้อผิดพลาดในการนำเข้า', 'error');
                } finally {
                    this.importLoading = false;
                }
            },

            async confirmDelete() {
                this.deleteLoading = true;
                try {
                    const res = await axios.delete('{{ route('admin.school-import.delete') }}', {
                        data: { _token: document.querySelector('meta[name=csrf-token]')?.content },
                    });
                    if (res.data.status === 'success') {
                        this.showToast(res.data.message, 'success');
                        this.preview = null;
                        this.deleteModal = false;
                        await this.loadData();
                    } else {
                        this.showToast(res.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                } catch (e) {
                    this.showToast(e.response?.data?.message || 'เกิดข้อผิดพลาดในการลบ', 'error');
                } finally {
                    this.deleteLoading = false;
                }
            },

            openDeleteLogModal(log) {
                this.selectedLog = log;
                this.deleteLogModal = true;
            },

            async confirmDeleteLog() {
                if (!this.selectedLog) return;
                this.deleteLoading = true;
                try {
                    const url = '{{ route('admin.school-import.delete-log', ':id') }}'.replace(':id', this.selectedLog.id);
                    const res = await axios.delete(url, {
                        data: { _token: document.querySelector('meta[name=csrf-token]')?.content }
                    });
                    if (res.data.status === 'success') {
                        this.showToast(res.data.message, 'success');
                        this.deleteLogModal = false;
                        this.selectedLog = null;
                        await this.loadData();
                    } else {
                        this.showToast(res.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                } catch (e) {
                    this.showToast(e.response?.data?.message || 'เกิดข้อผิดพลาดในการลบประวัติ', 'error');
                } finally {
                    this.deleteLoading = false;
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('th-TH', { day: '2-digit', month: 'short', year: 'numeric' })
                    + ' ' + d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, type, message };
                setTimeout(() => { this.toast.show = false; }, 4000);
            },
        };
    }
    </script>
    @endpush
</x-layout>
