<x-layout>
    <x-slot:title>จัดการสมาชิกและกำหนดสิทธิ์ | EE CPN1</x-slot>

    <div class="py-12 max-w-6xl mx-auto px-6" x-data="userManager()" x-init="init()">
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
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">จัดการสมาชิกและกำหนดสิทธิ์</h2>
                <p class="text-slate-500 text-sm mt-1">เพิ่ม แก้ไขสิทธิ์ และจัดการบทบาทของผู้ดูแลระบบ ครูผู้สอน และสมาชิกโรงเรียนเครือข่าย</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-50 transition shadow-sm">
                    ← กลับแดชบอร์ด
                </a>
                <button type="button" @click="openCreateModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> เพิ่มสมาชิกใหม่
                </button>
            </div>
        </header>

        <!-- Search Toolbar -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white p-4 border border-slate-100 rounded-2xl shadow-sm">
            <div class="relative w-full sm:max-w-xs">
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="ค้นหาชื่อ หรืออีเมล..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto no-scrollbar">
                <button @click="roleFilter = 'all'" 
                        class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                        :class="roleFilter === 'all' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-500 hover:text-slate-800'">
                    ทั้งหมด
                </button>
                <button @click="roleFilter = 'admin'" 
                        class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                        :class="roleFilter === 'admin' ? 'bg-amber-50 text-amber-600' : 'text-slate-500 hover:text-slate-800'">
                    ผู้ดูแลระบบ (Admin)
                </button>
                <button @click="roleFilter = 'teacher'" 
                        class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                        :class="roleFilter === 'teacher' ? 'bg-sky-50 text-sky-600' : 'text-slate-500 hover:text-slate-800'">
                    ครูผู้สอน (Teacher)
                </button>
                <button @click="roleFilter = 'user'" 
                        class="px-4 py-2 rounded-lg font-bold text-xs transition duration-200 shrink-0"
                        :class="roleFilter === 'user' ? 'bg-slate-100 text-slate-700' : 'text-slate-500 hover:text-slate-800'">
                    สมาชิก (User)
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm" x-transition>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
            <p class="text-slate-400 text-xs font-bold">กำลังโหลดรายชื่อสมาชิก...</p>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && filteredUsers().length === 0" class="text-center py-16 bg-white border border-slate-100 rounded-3xl p-8" x-cloak>
            <div class="w-16 h-16 bg-slate-50 text-slate-350 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-slate-100">
                <i class="fa-solid fa-users-slash text-slate-400"></i>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">ไม่พบข้อมูลสมาชิก</h4>
            <p class="text-slate-400 text-xs mt-1">กรุณาลองปรับเปลี่ยนคำค้นหาหรือตัวกรองบทบาท</p>
        </div>

        <!-- Users Table View -->
        <div x-show="!loading && filteredUsers().length > 0" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" x-cloak x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-450 uppercase font-bold tracking-wider">
                            <th class="py-4 px-6 w-16 text-center">โปรไฟล์</th>
                            <th class="py-4 px-6">ชื่อ-นามสกุล</th>
                            <th class="py-4 px-6">อีเมล</th>
                            <th class="py-4 px-6 w-36 text-center">สิทธิ์การใช้งาน</th>
                            <th class="py-4 px-6 w-40">วันที่ลงทะเบียน</th>
                            <th class="py-4 px-6 w-28 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <template x-for="user in filteredUsers()" :key="user.id">
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="py-4 px-6 text-center">
                                    <!-- User Profile Avatar/Logo -->
                                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 shrink-0 mx-auto shadow-inner flex items-center justify-center bg-slate-50">
                                        <template x-if="user.logo_url">
                                            <img :src="user.logo_url" alt="Logo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!user.logo_url">
                                            <span class="text-xs font-bold text-emerald-600 uppercase" x-text="user.name.charAt(0)"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-800 text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <span x-text="user.name"></span>
                                        <template x-if="user.id === {{ Auth::id() }}">
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-bold">บัญชีของคุณ</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-500" x-text="user.email"></td>
                                <td class="py-4 px-6 text-center">
                                    <!-- Role Badge -->
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide shadow-sm"
                                          :class="getRoleBadgeClass(user.role)"
                                          x-text="getRoleText(user.role)">
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-500" x-text="formatDate(user.created_at)"></td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    <button type="button" @click="openEditModal(user)" class="bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-100 hover:bg-indigo-50 w-8 h-8 rounded-lg inline-flex items-center justify-center transition shadow-sm cursor-pointer" title="แก้ไขข้อมูล/สิทธิ์">
                                        <i class="fa-solid fa-user-gear"></i>
                                    </button>
                                    <button type="button" 
                                            @click="confirmDelete(user)" 
                                            :disabled="user.id === {{ Auth::id() }}"
                                            class="bg-slate-50 text-rose-500 hover:text-rose-600 border border-slate-100 hover:bg-rose-50 w-8 h-8 rounded-lg inline-flex items-center justify-center transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed"
                                            :class="user.id !== {{ Auth::id() }} ? 'cursor-pointer' : ''"
                                            title="ลบผู้ใช้งาน">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
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
                 class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                
                <header class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-800 text-sm md:text-base" x-text="modal.isEdit ? 'แก้ไขข้อมูลสมาชิก / สิทธิ์' : 'เพิ่มสมาชิกใหม่'"></h3>
                    <button type="button" @click="modal.open = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </header>

                <form @submit.prevent="saveForm()" class="overflow-y-auto p-6 space-y-4 flex-1">
                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.name" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800" placeholder="ระบุชื่อจริงและนามสกุล">
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">อีเมล <span class="text-rose-500">*</span></label>
                        <input type="email" x-model="form.email" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800" placeholder="example@domain.com">
                    </div>

                    <!-- Role -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">สิทธิ์การใช้งาน (บทบาท) <span class="text-rose-500">*</span></label>
                        <select x-model="form.role" 
                                :disabled="form.id === {{ Auth::id() }}"
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800 disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="user">สมาชิกทั่วไป (User)</option>
                            <option value="teacher">ครูผู้สอนแกนนำ (Teacher)</option>
                            <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                        </select>
                        <template x-if="form.id === {{ Auth::id() }}">
                            <p class="text-[9px] text-amber-500 font-semibold mt-1">คุณไม่สามารถเปลี่ยนบทบาทของบัญชีที่คุณกำลังเข้าใช้งานอยู่ได้</p>
                        </template>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">
                            รหัสผ่าน 
                            <span class="text-rose-500" x-show="!modal.isEdit">*</span>
                            <span class="text-slate-450 font-normal" x-show="modal.isEdit">(ระบุเมื่อต้องการเปลี่ยนรหัสผ่านใหม่)</span>
                        </label>
                        <input type="password" x-model="form.password" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold text-slate-800" placeholder="รหัสผ่านอย่างน้อย 8 ตัวอักษร">
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
                    <i class="fa-solid fa-user-minus"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="font-extrabold text-slate-800 text-sm">ยืนยันการลบสมาชิก?</h3>
                    <p class="text-slate-400 text-[10px] leading-relaxed">คุณกำลังลบข้อมูลของสมาชิกชื่อ "<span class="font-bold text-slate-600" x-text="deleteModal.name"></span>" ออกจากระบบอย่างถาวร</p>
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
        function userManager() {
            return {
                users: [],
                loading: false,
                searchQuery: '',
                roleFilter: 'all',
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
                    name: '',
                    deleting: false
                },
                form: {
                    id: null,
                    name: '',
                    email: '',
                    role: 'user',
                    password: ''
                },

                init() {
                    this.loadUsers();
                },

                showToast(type, message) {
                    this.toast.show = true;
                    this.toast.type = type;
                    this.toast.message = message;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                async loadUsers() {
                    this.loading = true;
                    try {
                        const res = await axios.get('{{ route('admin.users.data') }}');
                        if (res.data.status === 'success') {
                            this.users = res.data.data;
                        } else {
                            this.showToast('error', res.data.message || 'โหลดข้อมูลผู้ใช้ล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast('error', 'ระบบขัดข้องกรุณาลองใหม่อีกครั้ง');
                    } finally {
                        this.loading = false;
                    }
                },

                filteredUsers() {
                    return this.users.filter(user => {
                        const matchesQuery = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                             user.email.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchesRole = this.roleFilter === 'all' || user.role === this.roleFilter;
                        return matchesQuery && matchesRole;
                    });
                },

                openCreateModal() {
                    this.modal.isEdit = false;
                    this.form = {
                        id: null,
                        name: '',
                        email: '',
                        role: 'user',
                        password: ''
                    };
                    this.modal.open = true;
                },

                openEditModal(user) {
                    this.modal.isEdit = true;
                    this.form = {
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        role: user.role,
                        password: ''
                    };
                    this.modal.open = true;
                },

                async saveForm() {
                    if (!this.form.name.trim()) {
                        this.showToast('error', 'กรุณาระบุชื่อ-นามสกุล');
                        return;
                    }
                    if (!this.form.email.trim()) {
                        this.showToast('error', 'กรุณาระบุอีเมล');
                        return;
                    }
                    if (!this.modal.isEdit && !this.form.password) {
                        this.showToast('error', 'กรุณาระบุรหัสผ่าน');
                        return;
                    }
                    if (this.form.password && this.form.password.length < 8) {
                        this.showToast('error', 'รหัสผ่านต้องมีความยาวไม่ต่ำกว่า 8 ตัวอักษร');
                        return;
                    }

                    this.modal.saving = true;
                    try {
                        const url = this.modal.isEdit ? `/admin/users/${this.form.id}/save` : '{{ route('admin.users.save') }}';
                        const res = await axios.post(url, {
                            name: this.form.name,
                            email: this.form.email,
                            role: this.form.role,
                            password: this.form.password || null
                        });

                        if (res.data.status === 'success') {
                            this.showToast('success', res.data.message);
                            this.modal.open = false;
                            this.loadUsers();
                        } else {
                            this.showToast('error', res.data.message || 'บันทึกข้อมูลล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        const errors = err.response?.data?.errors;
                        const errorMsg = err.response?.data?.message;
                        if (errors) {
                            const firstErr = Object.values(errors)[0][0];
                            this.showToast('error', firstErr);
                        } else if (errorMsg) {
                            this.showToast('error', errorMsg);
                        } else {
                            this.showToast('error', 'ไม่สามารถบันทึกข้อมูลผู้ใช้ได้');
                        }
                    } finally {
                        this.modal.saving = false;
                    }
                },

                confirmDelete(user) {
                    if (user.id === {{ Auth::id() }}) {
                        this.showToast('error', 'คุณไม่สามารถลบบัญชีของคุณเองได้');
                        return;
                    }
                    this.deleteModal.id = user.id;
                    this.deleteModal.name = user.name;
                    this.deleteModal.deleting = false;
                    this.deleteModal.open = true;
                },

                async executeDelete() {
                    this.deleteModal.deleting = true;
                    try {
                        const res = await axios.delete(`/admin/users/${this.deleteModal.id}`);
                        if (res.data.status === 'success') {
                            this.showToast('success', res.data.message);
                            this.deleteModal.open = false;
                            this.loadUsers();
                        } else {
                            this.showToast('error', res.data.message || 'ลบข้อมูลล้มเหลว');
                        }
                    } catch (err) {
                        console.error(err);
                        const errorMsg = err.response?.data?.message;
                        if (errorMsg) {
                            this.showToast('error', errorMsg);
                        } else {
                            this.showToast('error', 'ระบบขัดข้องกรุณาลองใหม่อีกครั้ง');
                        }
                    } finally {
                        this.deleteModal.deleting = false;
                    }
                },

                getRoleBadgeClass(role) {
                    if (role === 'admin') return 'bg-amber-100 text-amber-800 border border-amber-200/50';
                    if (role === 'teacher') return 'bg-sky-100 text-sky-800 border border-sky-200/50';
                    return 'bg-slate-100 text-slate-600 border border-slate-200/50';
                },

                getRoleText(role) {
                    if (role === 'admin') return 'ผู้ดูแลระบบ (Admin)';
                    if (role === 'teacher') return 'ครูผู้สอน (Teacher)';
                    return 'สมาชิก (User)';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    try {
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('th-TH', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        return dateStr;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layout>

