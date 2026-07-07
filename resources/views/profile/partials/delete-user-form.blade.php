<section class="space-y-6">
    <header class="border-b border-rose-100 pb-4 mb-6">
        <h3 class="text-lg font-extrabold text-rose-600 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 animate-pulse"></i> พื้นที่อันตราย (Danger Zone)
        </h3>
        <p class="text-xs text-slate-500 mt-1">
            เมื่อลบบัญชีของคุณแล้ว ข้อมูลทั้งหมดรวมถึงประวัติกิจกรรมการอบรมจะถูกลบออกอย่างถาวรและไม่สามารถกู้คืนได้
        </p>
    </header>

    <div>
        <button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs py-3.5 px-7 rounded-2xl shadow-lg shadow-rose-100 transition duration-250 cursor-pointer"
        >
            <i class="fa-solid fa-user-xmark"></i> ลบบัญชีผู้ใช้งานถาวร
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 md:p-12 space-y-6">
            @csrf
            @method('delete')

            <div>
                <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-circle-question text-rose-500"></i> คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีนี้?
                </h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    เมื่อบัญชีของคุณถูกลบ ข้อมูลและทรัพยากรทั้งหมดจะถูกดำเนินการลบออกจากระบบอย่างถาวร กรุณากรอกรหัสผ่านของคุณเพื่อยืนยันว่าคุณยอมรับเงื่อนไขนี้และยืนยันตัวตนจริง
                </p>
            </div>

            <div>
                <label for="password" class="block text-xs font-extrabold text-slate-700 mb-2 uppercase tracking-wide">รหัสผ่านสำหรับยืนยัน</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3.5 text-xs font-semibold focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition shadow-sm placeholder:text-slate-400 text-slate-800"
                    placeholder="รหัสผ่านของคุณ..."
                    required
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-rose-500 text-xs" />
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button 
                    type="button" 
                    x-on:click="$dispatch('close')" 
                    class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition"
                >
                    ยกเลิก
                </button>

                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-rose-600 text-white rounded-xl font-bold text-xs hover:bg-rose-700 transition shadow-lg shadow-rose-100"
                >
                    ยืนยันลบบัญชี
                </button>
            </div>
        </form>
    </x-modal>
</section>
