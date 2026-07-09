@php
    $activeMajors = \Illuminate\Support\Facades\DB::table('obec_majors')
        ->where('is_active', 1)
        ->orderBy('name')
        ->pluck('name')
        ->toArray();
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />

<section
    x-data="teacherProfileForm()"
    x-init="init()"
    class="space-y-8 animate-fade-in"
    id="teacher-profile-section"
>
    {{-- ===== HEADER ===== --}}
    <header class="border-b border-slate-100 pb-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-emerald-500"></i>
                    แก้ไขข้อมูลประวัติการปฏิบัติหน้าที่
                </h3>
                <p class="text-xs text-slate-500 mt-1">อัปเดตข้อมูลครู ประวัติการศึกษา และทักษะภาษาต่างประเทศ</p>
            </div>
        </div>
    </header>

    {{-- ===== EDIT FORM ===== --}}
    <form @submit.prevent class="space-y-10">

        {{-- ---- Section 1: รูปโปรไฟล์ ---- --}}
        <div class="space-y-4">
            <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-camera text-emerald-400"></i> รูปโปรไฟล์ครู
            </h4>
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="shrink-0 relative group">
                    <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-emerald-500/20 shadow-md bg-slate-100 flex items-center justify-center cursor-pointer"
                         @click="$refs.imageInput.click()">
                        <template x-if="form.preview_url">
                            <img :src="form.preview_url" alt="รูปโปรไฟล์" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!form.preview_url && form.profile_image_url_resolved && !imageLoadError">
                            <img :src="form.profile_image_url_resolved" alt="รูปโปรไฟล์" class="w-full h-full object-cover" x-on:error="imageLoadError = true">
                        </template>
                        <template x-if="!form.preview_url && (!form.profile_image_url_resolved || imageLoadError)">
                            <i class="fa-solid fa-users text-4xl text-slate-300"></i>
                        </template>
                        <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <i class="fa-solid fa-camera text-white text-xl"></i>
                        </div>
                    </div>
                    <input type="file" x-ref="imageInput" accept="image/*" class="hidden" @change="onImageSelected($event)">
                </div>
                <div class="text-xs text-slate-500 space-y-1">
                    <p class="font-bold text-slate-600">อัปโหลดรูปภาพ</p>
                    <p>รองรับไฟล์ JPG, PNG, WEBP (สูงสุด 5MB)</p>
                    <p>แนะนำขนาด 1:1 (สี่เหลี่ยมจัตุรัส) เพื่อความสวยงาม</p>
                    <button type="button"
                        @click="$refs.imageInput.click()"
                        class="inline-flex items-center gap-2 border border-slate-300 hover:border-emerald-400 text-slate-700 hover:text-emerald-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer mt-2">
                        <i class="fa-solid fa-upload"></i> เลือกรูปภาพ
                    </button>
                </div>
            </div>
        </div>

        {{-- ---- Section 2: ข้อมูลส่วนตัว ---- --}}
        <div class="space-y-4">
            <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-id-card text-emerald-400"></i> ข้อมูลส่วนตัว
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- คำนำหน้า --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">คำนำหน้า <span class="text-rose-500">*</span></label>
                    <select x-model="form.prefix"
                        class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                        <option value="">-- เลือก --</option>
                        <option value="นาย">นาย</option>
                        <option value="นาง">นาง</option>
                        <option value="นางสาว">นางสาว</option>
                        <option value="ว่าที่ร้อยตรี">ว่าที่ร้อยตรี</option>
                        <option value="ว่าที่ร้อยตรีหญิง">ว่าที่ร้อยตรีหญิง</option>
                    </select>
                    <p class="mt-1 text-rose-500 text-[10px] font-bold" x-show="errors.prefix" x-text="errors.prefix" x-cloak></p>
                </div>
                {{-- ชื่อ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ชื่อ <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.first_name" placeholder="ชื่อ"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                    <p class="mt-1 text-rose-500 text-[10px] font-bold" x-show="errors.first_name" x-text="errors.first_name" x-cloak></p>
                </div>
                {{-- นามสกุล --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">นามสกุล <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.last_name" placeholder="นามสกุล"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                    <p class="mt-1 text-rose-500 text-[10px] font-bold" x-show="errors.last_name" x-text="errors.last_name" x-cloak></p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- วันเกิด (พ.ศ.) - Thai date picker --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">วันเกิด (พ.ศ.)</label>
                    <div class="grid grid-cols-3 gap-1.5" x-data="{
                        get _p() { return window.isoToThai(form.birth_date); },
                        get tDay()   { return this._p.day    ? String(this._p.day)   : ''; },
                        get tMonth() { return this._p.month  ? String(this._p.month) : ''; },
                        get tYear()  { return this._p.yearBE ? String(this._p.yearBE): ''; },
                        setDay(v)  { const p=window.isoToThai(form.birth_date); form.birth_date=window.thaiToIso(p.yearBE||'',p.month||'',v); calcBirthYear(); },
                        setMonth(v){ const p=window.isoToThai(form.birth_date); form.birth_date=window.thaiToIso(p.yearBE||'',v,p.day||''); calcBirthYear(); },
                        setYear(v) { const p=window.isoToThai(form.birth_date); form.birth_date=window.thaiToIso(v,p.month||'',p.day||''); calcBirthYear(); },
                    }">
                        <select :value="tDay" @change="setDay($event.target.value)"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <option value="">วัน</option>
                            <template x-for="d in Array.from({length:31},(_,i)=>i+1)" :key="d">
                                <option :value="String(d)" :selected="tDay===String(d)" x-text="d"></option>
                            </template>
                        </select>
                        <select :value="tMonth" @change="setMonth($event.target.value)"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <option value="">เดือน</option>
                            <template x-for="(name,i) in window.thaiMonths" :key="i">
                                <option :value="String(i+1)" :selected="tMonth===String(i+1)" x-text="name"></option>
                            </template>
                        </select>
                        <input type="number" :value="tYear" @change="setYear($event.target.value)" placeholder="ปี พ.ศ."
                            min="2430" max="2600"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                </div>
                {{-- ปีเกิด พ.ศ. --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ปีเกิด (พ.ศ.)</label>
                    <input type="number" x-model="form.birth_year_be" placeholder="เช่น 2520"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                </div>
                {{-- อายุ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">อายุ (ปี)</label>
                    <input type="number" x-model="form.age" placeholder="อายุ"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                </div>
                {{-- ปีที่เกษียณอายุราชการ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ปีที่เกษียณอายุราชการ (พ.ศ.)</label>
                    <div class="w-full border border-slate-200 bg-slate-50 text-slate-700 font-extrabold rounded-xl px-3 py-2.5 text-sm transition" x-text="retirementYearBeText"></div>
                </div>
            </div>
        </div>

        {{-- ---- Section 3: ข้อมูลตำแหน่งและสังกัด ---- --}}
        <div class="space-y-4">
            <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-emerald-400"></i> ตำแหน่งและสังกัด
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- รหัสโรงเรียน --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">รหัสโรงเรียน (SMIS)</label>
                    <input type="text" x-model="form.school_code" readonly placeholder="จะเติมอัตโนมัติเมื่อเลือกโรงเรียน"
                        class="w-full border border-slate-200 bg-slate-50 text-slate-500 rounded-xl px-3 py-2.5 text-sm focus:outline-none cursor-not-allowed transition">
                </div>
                {{-- ชื่อโรงเรียน --}}
                <div class="relative" x-data="{ clickAway() { showSchoolDropdown = false; } }" @click.away="clickAway()">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ชื่อสถานศึกษา <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.school_name" 
                           @input.debounce.300ms="searchSchoolAutocomplete()" 
                           @focus="if (schoolSearchList.length > 0) showSchoolDropdown = true"
                           placeholder="พิมพ์ชื่อโรงเรียนเพื่อค้นหา..."
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                    
                    <!-- Suggestions Dropdown -->
                    <div x-show="showSchoolDropdown && schoolSearchList.length > 0" 
                         class="absolute z-[999] left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-slate-100" 
                         x-cloak>
                        <template x-for="school in schoolSearchList">
                            <button type="button" @click="selectSchool(school)" 
                                    class="w-full text-left px-4 py-3 hover:bg-emerald-50 text-xs text-slate-700 transition flex flex-col gap-0.5 cursor-pointer">
                                <span class="font-bold text-slate-900" x-text="school.school_name"></span>
                                <span class="text-[10px] text-slate-400" x-text="'รหัส SMIS: ' + school.school_code + ' | เครือข่าย: ' + (school.school_network || '-')"></span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-1 text-rose-500 text-[10px] font-bold" x-show="errors.school_name" x-text="errors.school_name" x-cloak></p>
                </div>
                {{-- เครือข่าย --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">เครือข่ายสถานศึกษา</label>
                    <input type="text" x-model="form.school_network" placeholder="ชื่อเครือข่าย"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                </div>
                {{-- ตำแหน่ง --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ตำแหน่ง <span class="text-rose-500">*</span></label>
                    <select x-model="form.position"
                        class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                        <option value="">-- เลือก --</option>
                        <option value="ครูผู้ช่วย">ครูผู้ช่วย</option>
                        <option value="ครู">ครู</option>
                        <option value="ศึกษานิเทศก์">ศึกษานิเทศก์</option>
                        <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                        <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                        <option value="รองผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา">รองผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา</option>
                        <option value="ผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา">ผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา</option>
                    </select>
                    <p class="mt-1 text-rose-500 text-[10px] font-bold" x-show="errors.position" x-text="errors.position" x-cloak></p>
                </div>
                {{-- วิทยฐานะ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">วิทยฐานะ</label>
                    <select x-model="form.academic_rank"
                        class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                        <option value="">-- ไม่มี / ยังไม่มี --</option>
                        <option value="ครูชำนาญการ">ครูชำนาญการ</option>
                        <option value="ครูชำนาญการพิเศษ">ครูชำนาญการพิเศษ</option>
                        <option value="ครูเชี่ยวชาญ">ครูเชี่ยวชาญ</option>
                        <option value="ครูเชี่ยวชาญพิเศษ">ครูเชี่ยวชาญพิเศษ</option>
                        <option value="ศึกษานิเทศก์ชำนาญการ">ศึกษานิเทศก์ชำนาญการ</option>
                        <option value="ศึกษานิเทศก์ชำนาญการพิเศษ">ศึกษานิเทศก์ชำนาญการพิเศษ</option>
                        <option value="รองผู้อำนวยการชำนาญการ">รองผู้อำนวยการชำนาญการ</option>
                        <option value="รองผู้อำนวยการชำนาญการพิเศษ">รองผู้อำนวยการชำนาญการพิเศษ</option>
                        <option value="ผู้อำนวยการชำนาญการ">ผู้อำนวยการชำนาญการ</option>
                        <option value="ผู้อำนวยการชำนาญการพิเศษ">ผู้อำนวยการชำนาญการพิเศษ</option>
                        <option value="ผู้อำนวยการเชี่ยวชาญ">ผู้อำนวยการเชี่ยวชาญ</option>
                    </select>
                </div>
                {{-- กลุ่มสาระที่บรรจุ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">กลุ่มสาระ/สาขาที่บรรจุ</label>
                    <select x-model="form.recruitment_subject"
                        class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                        <option value="">-- เลือก --</option>
                        @foreach($activeMajors as $major)
                            <option value="{{ $major }}">{{ $major }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- วันบรรจุ (พ.ศ.) - Thai date picker --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">วันที่บรรจุแต่งตั้ง (พ.ศ.)</label>
                    <div class="grid grid-cols-3 gap-1.5" x-data="{
                        get _p() { return window.isoToThai(form.appointed_date); },
                        get tDay()   { return this._p.day    ? String(this._p.day)   : ''; },
                        get tMonth() { return this._p.month  ? String(this._p.month) : ''; },
                        get tYear()  { return this._p.yearBE ? String(this._p.yearBE): ''; },
                        setDay(v)  { const p=window.isoToThai(form.appointed_date); form.appointed_date=window.thaiToIso(p.yearBE||'',p.month||'',v); calcAppointedYear(); },
                        setMonth(v){ const p=window.isoToThai(form.appointed_date); form.appointed_date=window.thaiToIso(p.yearBE||'',v,p.day||''); calcAppointedYear(); },
                        setYear(v) { const p=window.isoToThai(form.appointed_date); form.appointed_date=window.thaiToIso(v,p.month||'',p.day||''); calcAppointedYear(); },
                    }">
                        <select :value="tDay" @change="setDay($event.target.value)"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <option value="">วัน</option>
                            <template x-for="d in Array.from({length:31},(_,i)=>i+1)" :key="d">
                                <option :value="String(d)" :selected="tDay===String(d)" x-text="d"></option>
                            </template>
                        </select>
                        <select :value="tMonth" @change="setMonth($event.target.value)"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <option value="">เดือน</option>
                            <template x-for="(name,i) in window.thaiMonths" :key="i">
                                <option :value="String(i+1)" :selected="tMonth===String(i+1)" x-text="name"></option>
                            </template>
                        </select>
                        <input type="number" :value="tYear" @change="setYear($event.target.value)" placeholder="ปี พ.ศ."
                            min="2430" max="2600"
                            class="border border-slate-200 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                </div>
                {{-- ปีที่บรรจุ พ.ศ. --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">ปีที่บรรจุ (พ.ศ.)</label>
                    <input type="number" x-model="form.appointed_year_be" placeholder="เช่น 2554"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition">
                </div>
                {{-- อายุราชการ --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">อายุราชการ</label>
                    <div class="w-full border border-slate-200 bg-slate-50 text-slate-700 font-extrabold rounded-xl px-3 py-2.5 text-sm transition" x-text="serviceYearsText"></div>
                </div>
            </div>
            {{-- งานพิเศษ --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">งานพิเศษ / ภาระงานอื่นๆ</label>
                <textarea x-model="form.other_workload" rows="3" placeholder="ระบุงานพิเศษหรือภาระงานเพิ่มเติม..."
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none transition resize-none"></textarea>
            </div>
        </div>

        {{-- ---- Section 4: ประวัติการศึกษา (ปริญญา) ---- --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-emerald-400"></i> ประวัติการศึกษา (ระดับปริญญา)
                </h4>
                <button type="button" @click="addEducation()"
                    class="inline-flex items-center gap-1.5 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> เพิ่มวุฒิการศึกษา
                </button>
            </div>

            {{-- Empty state --}}
            <p x-show="form.educations.length === 0" class="text-slate-400 text-xs italic">ยังไม่มีข้อมูลการศึกษา — คลิก "เพิ่มวุฒิการศึกษา" เพื่อเพิ่ม</p>

            {{-- Education rows --}}
            <template x-for="(edu, idx) in form.educations" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-end bg-violet-50/40 p-4 rounded-2xl border border-violet-100 group">
                    {{-- ลำดับ --}}
                    <div class="col-span-12 flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-violet-500 text-white text-[10px] font-extrabold rounded-full shrink-0" x-text="idx + 1"></span>
                        <span class="text-[10px] font-bold text-violet-600 uppercase tracking-wider">วุฒิการศึกษาที่ <span x-text="idx + 1"></span></span>
                    </div>
                    {{-- ระดับปริญญา --}}
                    <div class="col-span-12 sm:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ระดับการศึกษา <span class="text-rose-400">*</span></label>
                        <select x-model="edu.edu_level"
                            class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-violet-400 focus:border-violet-400 focus:outline-none transition">
                            <option value="">-- เลือกระดับ --</option>
                            <option value="ปริญญาตรี">ปริญญาตรี</option>
                            <option value="ปริญญาโท">ปริญญาโท</option>
                            <option value="ปริญญาเอก">ปริญญาเอก</option>
                        </select>
                    </div>
                    {{-- สาขาวิชา --}}
                    <div class="col-span-12 sm:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">สาขาวิชา</label>
                        <input type="text" x-model="edu.edu_field" placeholder="เช่น ครุศาสตร์, วิทยาศาสตร์"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-violet-400 focus:border-violet-400 focus:outline-none transition bg-white">
                    </div>
                    {{-- วิชาเอก --}}
                    <div class="col-span-11 sm:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">วิชาเอก</label>
                        <select x-model="edu.edu_major"
                            class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-violet-400 focus:border-violet-400 focus:outline-none transition">
                            <option value="">-- เลือกวิชาเอก --</option>
                            @foreach($activeMajors as $major)
                                <option value="{{ $major }}">{{ $major }}</option>
                            @endforeach
                            <option value="อื่นๆ">อื่นๆ (ระบุในสาขาวิชา)</option>
                        </select>
                    </div>
                    {{-- ปุ่มลบ --}}
                    <div class="col-span-1 flex justify-center items-end pb-0.5">
                        <button type="button" @click="removeEducation(idx)"
                            class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer"
                            title="ลบรายการนี้">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- ---- Section 5: วิชาที่สอน ---- --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-book-open text-emerald-400"></i> วิชาที่ทำการเรียนการสอน
                </h4>
                <button type="button" @click="addSubject()"
                    class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> เพิ่มวิชา
                </button>
            </div>
            <div class="space-y-3" x-show="form.subjects.length === 0">
                <p class="text-slate-400 text-xs italic">ยังไม่มีวิชาที่สอน — คลิก "เพิ่มวิชา" เพื่อเพิ่ม</p>
            </div>
            <template x-for="(subject, idx) in form.subjects" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded-2xl border border-slate-100 group relative">
                    <div class="col-span-5">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">กลุ่มสาระการเรียนรู้ที่สอน</label>
                        <select x-model="subject.subject_name" required
                            class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition">
                            <option value="">-- เลือกกลุ่มสาระ --</option>
                            <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                            <option value="วิทยาศาสตร์และเทคโนโลยี">วิทยาศาสตร์และเทคโนโลยี</option>
                            <option value="วิทยาการคำนวณ">วิทยาการคำนวณ</option>
                            <option value="ภาษาไทย">ภาษาไทย</option>
                            <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                            <option value="สังคมศึกษา ศาสนา และวัฒนธรรม">สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                            <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                            <option value="ศิลปะ">ศิลปะ</option>
                            <option value="การงานอาชีพ">การงานอาชีพ</option>
                            <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
                        </select>
                    </div>
                    <div class="col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ชั้นปีที่สอน</label>
                        <select x-model="subject.subject_grade"
                            class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition">
                            <option value="">-- เลือก --</option>
                            <optgroup label="ระดับอนุบาล">
                                <option value="อนุบาล 1">อนุบาล 1</option>
                                <option value="อนุบาล 2">อนุบาล 2</option>
                                <option value="อนุบาล 3">อนุบาล 3</option>
                            </optgroup>
                            <optgroup label="ระดับประถมศึกษา">
                                <option value="ป.1">ป.1</option>
                                <option value="ป.2">ป.2</option>
                                <option value="ป.3">ป.3</option>
                                <option value="ป.4">ป.4</option>
                                <option value="ป.5">ป.5</option>
                                <option value="ป.6">ป.6</option>
                            </optgroup>
                            <optgroup label="ระดับมัธยมศึกษา">
                                <option value="ม.1">ม.1</option>
                                <option value="ม.2">ม.2</option>
                                <option value="ม.3">ม.3</option>
                                <option value="ม.4">ม.4</option>
                                <option value="ม.5">ม.5</option>
                                <option value="ม.6">ม.6</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ชม./สัปดาห์</label>
                        <input type="number" x-model="subject.subject_hours" placeholder="0" min="0" max="999"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition bg-white">
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <button type="button" @click="removeSubject(idx)"
                            class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- ---- Section 6: CEFR (ภาษาอังกฤษ) ---- --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-language text-emerald-400"></i> ทักษะภาษาอังกฤษ (CEFR)
                </h4>
                <button type="button" @click="addCefr()"
                    class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> เพิ่ม CEFR
                </button>
            </div>
            <template x-for="(item, idx) in form.cefr" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    {{-- แหล่งที่มา (Dropdown + Textbox) --}}
                    <div class="col-span-2 space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">แหล่งที่มา</label>
                        <select x-model="item.source_select" @change="item.source = (item.source_select === 'obec' ? 'obec' : '')"
                            class="w-full border border-slate-200 bg-white rounded-xl px-2 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition">
                            <option value="obec">สพฐ.</option>
                            <option value="other">อื่น ๆ</option>
                        </select>
                        <input type="text" x-show="item.source_select === 'other'" x-model="item.source" placeholder="ระบุแหล่งที่มา..."
                            class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none transition bg-white" x-cloak>
                    </div>
                    {{-- ระดับ CEFR (col-span-1) --}}
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ระดับ</label>
                        <select x-model="item.cefr_level"
                            class="w-full border border-slate-200 bg-white rounded-xl px-2 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition">
                            <option value="">-</option>
                            <option value="A1">A1</option>
                            <option value="A2">A2</option>
                            <option value="B1">B1</option>
                            <option value="B2">B2</option>
                            <option value="C1">C1</option>
                            <option value="C2">C2</option>
                        </select>
                    </div>
                    {{-- เลขที่ใบรับรอง (col-span-2) --}}
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">เลขที่ใบรับรอง</label>
                        <input type="text" x-model="item.cert_no" placeholder="เลขที่"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition bg-white">
                    </div>
                    {{-- วันที่ออกใบรับรอง CEFR (พ.ศ.) (col-span-3 with month taking 6/12 width) --}}
                    <div class="col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">วันที่ออกใบรับรอง (พ.ศ.)</label>
                        <div class="grid grid-cols-12 gap-1.5" x-data="{
                            get _p() { return window.isoToThai(item.cert_date); },
                            get tDay()   { return this._p.day    ? String(this._p.day)   : ''; },
                            get tMonth() { return this._p.month  ? String(this._p.month) : ''; },
                            get tYear()  { return this._p.yearBE ? String(this._p.yearBE): ''; },
                            setDay(v)  { const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(p.yearBE||'',p.month||'',v); calcCefrYear(idx); },
                            setMonth(v){ const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(p.yearBE||'',v,p.day||''); calcCefrYear(idx); },
                            setYear(v) { const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(v,p.month||'',p.day||''); calcCefrYear(idx); },
                        }">
                            <select :value="tDay" @change="setDay($event.target.value)"
                                class="col-span-3 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                                <option value="">วัน</option>
                                <template x-for="d in Array.from({length:31},(_,i)=>i+1)" :key="d">
                                    <option :value="String(d)" :selected="tDay===String(d)" x-text="d"></option>
                                </template>
                            </select>
                            <select :value="tMonth" @change="setMonth($event.target.value)"
                                class="col-span-6 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                                <option value="">เดือน</option>
                                <template x-for="(name,i) in window.thaiMonths" :key="i">
                                    <option :value="String(i+1)" :selected="tMonth===String(i+1)" x-text="name"></option>
                                </template>
                            </select>
                            <input type="number" :value="tYear" @change="setYear($event.target.value)" placeholder="ปี"
                                min="2430" max="2600"
                                class="col-span-3 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                    </div>
                    {{-- ผู้ออกใบรับรอง (col-span-3) --}}
                    <div class="col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ผู้ออกใบรับรอง</label>
                        <input type="text" x-model="item.issuer" placeholder="หน่วยงาน"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none transition bg-white">
                    </div>
                    {{-- ปุ่มลบ (col-span-1) --}}
                    <div class="col-span-1 flex justify-center">
                        <button type="button" @click="removeCefr(idx)"
                            class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="form.cefr.length === 0" class="text-slate-400 text-xs italic">ยังไม่มีข้อมูล CEFR</p>
        </div>

        {{-- ---- Section 7: HSK (ภาษาจีน) ---- --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-language text-sky-400"></i> ทักษะภาษาจีน (HSK)
                </h4>
                <button type="button" @click="addHsk()"
                    class="inline-flex items-center gap-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> เพิ่ม HSK
                </button>
            </div>
            <template x-for="(item, idx) in form.hsk" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    {{-- แหล่งที่มา (Dropdown + Textbox) --}}
                    <div class="col-span-2 space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">แหล่งที่มา</label>
                        <select x-model="item.source_select" @change="item.source = (item.source_select === 'obec' ? 'obec' : '')"
                            class="w-full border border-slate-200 bg-white rounded-xl px-2 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none transition">
                            <option value="obec">สพฐ.</option>
                            <option value="other">อื่น ๆ</option>
                        </select>
                        <input type="text" x-show="item.source_select === 'other'" x-model="item.source" placeholder="ระบุแหล่งที่มา..."
                            class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-sky-400 focus:outline-none transition bg-white" x-cloak>
                    </div>
                    {{-- ระดับ HSK (col-span-1) --}}
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ระดับ</label>
                        <select x-model="item.hsk_level"
                            class="w-full border border-slate-200 bg-white rounded-xl px-2 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none transition">
                            <option value="">-</option>
                            <option value="HSK 1">HSK 1</option>
                            <option value="HSK 2">HSK 2</option>
                            <option value="HSK 3">HSK 3</option>
                            <option value="HSK 4">HSK 4</option>
                            <option value="HSK 5">HSK 5</option>
                            <option value="HSK 6">HSK 6</option>
                            <option value="HSKK 初级">HSKK 初级</option>
                            <option value="HSKK 中级">HSKK 中级</option>
                            <option value="HSKK 高级">HSKK 高级</option>
                        </select>
                    </div>
                    {{-- เลขที่ใบรับรอง (col-span-2) --}}
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">เลขที่ใบรับรอง</label>
                        <input type="text" x-model="item.cert_no" placeholder="เลขที่"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none transition bg-white">
                    </div>
                    {{-- วันที่ออกใบรับรอง HSK (พ.ศ.) (col-span-3 with month taking 6/12 width) --}}
                    <div class="col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">วันที่ออกใบรับรอง (พ.ศ.)</label>
                        <div class="grid grid-cols-12 gap-1.5" x-data="{
                            get _p() { return window.isoToThai(item.cert_date); },
                            get tDay()   { return this._p.day    ? String(this._p.day)   : ''; },
                            get tMonth() { return this._p.month  ? String(this._p.month) : ''; },
                            get tYear()  { return this._p.yearBE ? String(this._p.yearBE): ''; },
                            setDay(v)  { const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(p.yearBE||'',p.month||'',v); calcHskYear(idx); },
                            setMonth(v){ const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(p.yearBE||'',v,p.day||''); calcHskYear(idx); },
                            setYear(v) { const p=window.isoToThai(item.cert_date); item.cert_date=window.thaiToIso(v,p.month||'',p.day||''); calcHskYear(idx); },
                        }">
                            <select :value="tDay" @change="setDay($event.target.value)"
                                class="col-span-3 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-sky-400 focus:outline-none bg-white">
                                <option value="">วัน</option>
                                <template x-for="d in Array.from({length:31},(_,i)=>i+1)" :key="d">
                                    <option :value="String(d)" :selected="tDay===String(d)" x-text="d"></option>
                                </template>
                            </select>
                            <select :value="tMonth" @change="setMonth($event.target.value)"
                                class="col-span-6 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-sky-400 focus:outline-none bg-white">
                                <option value="">เดือน</option>
                                <template x-for="(name,i) in window.thaiMonths" :key="i">
                                    <option :value="String(i+1)" :selected="tMonth===String(i+1)" x-text="name"></option>
                                </template>
                            </select>
                            <input type="number" :value="tYear" @change="setYear($event.target.value)" placeholder="ปี"
                                min="2430" max="2600"
                                class="col-span-3 border border-slate-200 rounded-xl px-1.5 py-2 text-xs focus:ring-2 focus:ring-sky-400 focus:outline-none">
                        </div>
                    </div>
                    {{-- ผู้ออกใบรับรอง (col-span-3) --}}
                    <div class="col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ผู้ออกใบรับรอง</label>
                        <input type="text" x-model="item.issuer" placeholder="หน่วยงาน"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none transition bg-white">
                    </div>
                    {{-- ปุ่มลบ (col-span-1) --}}
                    <div class="col-span-1 flex justify-center">
                        <button type="button" @click="removeHsk(idx)"
                            class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="form.hsk.length === 0" class="text-slate-400 text-xs italic">ยังไม่มีข้อมูล HSK</p>
        </div>

        {{-- ---- Section 8: รางวัลที่ได้รับ ---- --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-400"></i> รางวัล/ผลงานดีเด่น
                </h4>
                <button type="button" @click="addAward()"
                    class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold py-2 px-4 rounded-xl transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> เพิ่มรางวัล
                </button>
            </div>
            <template x-for="(award, idx) in form.awards" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div class="col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ชื่อผลงาน / ชื่อนักเรียน</label>
                        <input type="text" x-model="award.work_name" placeholder="ชื่อผลงาน"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none transition bg-white">
                    </div>
                    <div class="col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ชื่อรางวัล</label>
                        <input type="text" x-model="award.award_name" placeholder="ชื่อรางวัล"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none transition bg-white">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ผู้มอบรางวัล</label>
                        <input type="text" x-model="award.issuer" placeholder="หน่วยงาน"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none transition bg-white">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ปี พ.ศ.</label>
                        <input type="number" x-model="award.award_date_be" placeholder="เช่น 2566" min="2540" max="2600"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none transition bg-white">
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <button type="button" @click="removeAward(idx)"
                            class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="form.awards.length === 0" class="text-slate-400 text-xs italic">ยังไม่มีรางวัล/ผลงาน</p>
        </div>

        {{-- ---- Save Button ---- --}}
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100">
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100">
            <button
                type="button"
                @click="save()"
                :disabled="saving"
                class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 text-white font-bold text-xs py-3.5 px-7 rounded-2xl shadow-lg transition cursor-pointer"
            >
                <template x-if="saving">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </template>
                <template x-if="!saving">
                    <i class="fa-solid fa-floppy-disk"></i>
                </template>
                <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูลประวัติ'"></span>
            </button>
        </div>
    </form>

    {{-- ===== CROPPER MODAL ===== --}}
    <div x-show="cropperModalOpen" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto" 
         x-cloak>
        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="cancelCrop()"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-slate-100 p-6 space-y-4 flex flex-col max-h-[90vh]">
            <header class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-800 text-sm md:text-base">
                    <i class="fa-solid fa-crop-simple text-emerald-500"></i> ตัดครอบรูปภาพโปรไฟล์
                </h3>
                <button type="button" @click="cancelCrop()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="flex-1 overflow-hidden bg-slate-50 flex items-center justify-center rounded-2xl min-h-[300px]">
                <img id="cropper-image" :src="rawImageSrc" class="max-h-[60vh] max-w-full">
            </div>

            <footer class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" @click="cancelCrop()" 
                        class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-200 transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" @click="confirmCrop()" 
                        class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-100 cursor-pointer">
                    ตกลง (ตัดรูป)
                </button>
            </footer>
        </div>
    </div>

    {{-- ===== TOAST ===== --}}
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
</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
function teacherProfileForm() {
    return {
        loading: false,
        editing: true,
        saving: false,
        schoolLookupStatus: '',
        toast: { show: false, message: '', type: 'success' },
        imageLoadError: false,
        errors: {},
        schoolSearchList: [],
        showSchoolDropdown: false,
        rawImageSrc: '',
        cropperModalOpen: false,
        cropperInstance: null,
        form: {
            profile_found: false,
            prefix: '',
            first_name: '',
            last_name: '',
            birth_date: '',
            birth_year_be: '',
            age: '',
            school_code: '',
            school_name: '',
            school_network: '',
            position: '',
            academic_rank: '',
            recruitment_subject: '',
            appointed_date: '',
            appointed_year_be: '',
            educations: [],
            other_workload: '',
            profile_image_path: '',
            profile_image_url_resolved: '',
            profile_image_data: '',
            preview_url: '',
            subjects: [],
            cefr: [],
            hsk: [],
            awards: [],
        },

        init() {
            window.addEventListener('load', () => {
                this.fetchData();
            });
        },

        fetchData() {
            this.loading = true;
            this.imageLoadError = false;
            axios.get('{{ route("api.profile.teacher.get") }}')
                .then(res => {
                    if (res.data.status === 'success' && res.data.data) {
                        const d = res.data.data;
                        this.form.profile_found    = true;
                        this.form.prefix           = d.prefix || '';
                        this.form.first_name       = d.first_name || '';
                        this.form.last_name        = d.last_name || '';
                        this.form.birth_date       = d.birth_date || '';
                        this.form.birth_year_be    = d.birth_year_be || '';
                        this.form.age              = d.age || '';
                        this.form.school_code      = d.school_code || '';
                        this.form.school_name      = d.school_name || '';
                        this.form.school_network   = d.school_network || '';
                        this.form.position         = d.position || '';
                        this.form.academic_rank    = d.academic_rank || '';
                        this.form.recruitment_subject = d.recruitment_subject || '';
                        this.form.appointed_date   = d.appointed_date || '';
                        this.form.appointed_year_be= d.appointed_year_be || '';
                        this.form.educations = (d.educations || []).map(e => ({
                            edu_level: e.edu_level || '',
                            edu_field: e.field_of_study || e.edu_field || '',
                            edu_major: e.major || e.edu_major || '',
                        }));
                        // Backward-compat: convert old bachelor/master/doctoral fields if educations is empty
                        if (this.form.educations.length === 0) {
                            const legacyLevels = [
                                { field: 'bachelor_major', level: 'ปริญญาตรี' },
                                { field: 'master_major',   level: 'ปริญญาโท'  },
                                { field: 'doctoral_major', level: 'ปริญญาเอก' },
                            ];
                            legacyLevels.forEach(({ field, level }) => {
                                if (d[field]) {
                                    this.form.educations.push({ edu_level: level, edu_field: d[field], edu_major: '' });
                                }
                            });
                        }
                        this.form.other_workload   = d.other_workload || '';
                        this.form.profile_image_path = d.profile_image_path || '';
                        this.form.profile_image_url_resolved = d.profile_image_url_resolved || '';
                        this.form.subjects = (d.subjects || []).map(s => ({
                            subject_name:  s.subject_name  || '',
                            subject_grade: s.subject_grade || '',
                            subject_hours: s.subject_hours || '',
                        }));
                        this.form.cefr = (d.cefr || []).map(c => {
                            const src = c.source || 'obec';
                            return {
                                source:        src,
                                source_select: src === 'obec' ? 'obec' : 'other',
                                cefr_level:    c.cefr_level  || '',
                                cert_no:       c.cert_no     || '',
                                cert_date:     c.cert_date   || '',
                                cert_date_be:  c.cert_date_be|| '',
                                issuer:        c.issuer      || '',
                            };
                        });
                        this.form.hsk = (d.hsk || []).map(h => {
                            const src = h.source || 'obec';
                            return {
                                source:        src,
                                source_select: src === 'obec' ? 'obec' : 'other',
                                hsk_level:     h.hsk_level   || '',
                                cert_no:       h.cert_no     || '',
                                cert_date:     h.cert_date   || '',
                                cert_date_be:  h.cert_date_be|| '',
                                issuer:        h.issuer      || '',
                            };
                        });
                        this.form.awards = (d.awards || []).map(a => ({
                            work_name:    a.work_name    || '',
                            award_name:   a.award_name   || '',
                            award_date:   a.award_date   || '',
                            award_date_be:a.award_date_be|| '',
                            issuer:       a.issuer       || '',
                        }));
                    } else {
                        this.form.profile_found = false;
                    }
                })
                .catch(err => {
                    console.error('Fetch teacher error:', err);
                })
                .finally(() => {
                    this.loading = false;
                });
        },



        save() {
            this.saving = true;
            this.errors = {};
            const payload = { ...this.form };
            axios.post('{{ route("api.profile.teacher") }}', payload)
                .then(res => {
                    if (res.data.status === 'success') {
                        this.showToast(res.data.message, 'success');
                        this.form.profile_image_data = '';
                        this.form.profile_found = true;
                        this.fetchData();
                    } else {
                        this.showToast(res.data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                })
                .catch(err => {
                    if (err.response?.data?.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(err.response.data.errors).map(([k, v]) => [k, v[0]])
                        );
                    }
                    this.showToast(err.response?.data?.message ?? 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                })
                .finally(() => {
                    this.saving = false;
                });
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        // -- School lookup by code (searches teacher_profile records for matching school) --
        lookupSchool() {
            const code = (this.form.school_code || '').trim();
            if (!/^\d{8}$/.test(code)) {
                this.schoolLookupStatus = '';
                return;
            }
            this.schoolLookupStatus = '...';
            // Just provide feedback that code is valid format
            this.schoolLookupStatus = 'กรุณากรอกชื่อสถานศึกษาด้านล่าง';
        },

        // -- Computed year helpers --
        calcBirthYear() {
            if (this.form.birth_date) {
                const y = new Date(this.form.birth_date).getFullYear();
                this.form.birth_year_be = y + 543;
                const today = new Date();
                this.form.age = today.getFullYear() - y;
            }
        },
        calcAppointedYear() {
            if (this.form.appointed_date) {
                this.form.appointed_year_be = new Date(this.form.appointed_date).getFullYear() + 543;
            }
        },
        calcCefrYear(idx) {
            if (this.form.cefr[idx] && this.form.cefr[idx].cert_date) {
                this.form.cefr[idx].cert_date_be = new Date(this.form.cefr[idx].cert_date).getFullYear() + 543;
            }
        },
        calcHskYear(idx) {
            if (this.form.hsk[idx] && this.form.hsk[idx].cert_date) {
                this.form.hsk[idx].cert_date_be = new Date(this.form.hsk[idx].cert_date).getFullYear() + 543;
            }
        },

        // -- Dynamic row management --
        addEducation() {
            this.form.educations.push({ edu_level: 'ปริญญาตรี', edu_field: '', edu_major: '' });
        },
        removeEducation(idx) {
            this.form.educations.splice(idx, 1);
        },
        addSubject() {
            this.form.subjects.push({ subject_name: '', subject_grade: '', subject_hours: '' });
        },
        removeSubject(idx) {
            this.form.subjects.splice(idx, 1);
        },
        addCefr() {
            this.form.cefr.push({ source: 'obec', source_select: 'obec', cefr_level: '', cert_no: '', cert_date: '', cert_date_be: '', issuer: '' });
        },
        removeCefr(idx) {
            this.form.cefr.splice(idx, 1);
        },
        addHsk() {
            this.form.hsk.push({ source: 'obec', source_select: 'obec', hsk_level: '', cert_no: '', cert_date: '', cert_date_be: '', issuer: '' });
        },
        removeHsk(idx) {
            this.form.hsk.splice(idx, 1);
        },
        addAward() {
            this.form.awards.push({ work_name: '', award_name: '', award_date: '', award_date_be: '', issuer: '' });
        },
        removeAward(idx) {
            this.form.awards.splice(idx, 1);
        },

        // -- Image cropping methods using Cropper.js --
        onImageSelected(event) {
            this.imageLoadError = false;
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                this.showToast('ไฟล์รูปภาพต้องมีขนาดไม่เกิน 5MB', 'error');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.rawImageSrc = e.target.result;
                this.cropperModalOpen = true;
                
                this.$nextTick(() => {
                    const image = document.getElementById('cropper-image');
                    if (this.cropperInstance) {
                        this.cropperInstance.destroy();
                    }
                    this.cropperInstance = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                });
            };
            reader.readAsDataURL(file);
        },

        confirmCrop() {
            if (!this.cropperInstance) return;
            const canvas = this.cropperInstance.getCroppedCanvas({
                width: 400,
                height: 400,
            });
            const croppedBase64 = canvas.toDataURL('image/jpeg');
            this.form.profile_image_data = croppedBase64;
            this.form.preview_url = croppedBase64;
            
            this.cropperModalOpen = false;
            this.cropperInstance.destroy();
            this.cropperInstance = null;
            this.rawImageSrc = '';
            
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        },

        cancelCrop() {
            this.cropperModalOpen = false;
            if (this.cropperInstance) {
                this.cropperInstance.destroy();
                this.cropperInstance = null;
            }
            this.rawImageSrc = '';
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        },

        // -- School name autocomplete methods --
        searchSchoolAutocomplete() {
            const q = (this.form.school_name || '').trim();
            if (q.length < 2) {
                this.schoolSearchList = [];
                this.showSchoolDropdown = false;
                return;
            }
            axios.get('{{ route("api.schools.search") }}', { params: { q } })
                .then(res => {
                    if (res.data.status === 'success') {
                        this.schoolSearchList = res.data.data;
                        this.showSchoolDropdown = this.schoolSearchList.length > 0;
                    }
                })
                .catch(err => {
                    console.error('School search error:', err);
                });
        },

        selectSchool(school) {
            this.form.school_name = school.school_name;
            this.form.school_code = school.school_code;
            this.form.school_network = school.school_network || '';
            this.schoolSearchList = [];
            this.showSchoolDropdown = false;
        },

        // -- Dyn calculated getters --
        get serviceYearsText() {
            if (!this.form.appointed_date) return '-';
            const appointed = new Date(this.form.appointed_date);
            const today = new Date();
            
            if (isNaN(appointed.getTime())) return '-';
            
            let years = today.getFullYear() - appointed.getFullYear();
            let months = today.getMonth() - appointed.getMonth();
            let days = today.getDate() - appointed.getDate();
            
            if (days < 0) {
                months--;
                const prevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                days += prevMonth.getDate();
            }
            if (months < 0) {
                years--;
                months += 12;
            }
            
            let textParts = [];
            if (years > 0) textParts.push(`${years} ปี`);
            if (months > 0) textParts.push(`${months} เดือน`);
            if (days > 0) textParts.push(`${days} วัน`);
            
            return textParts.length > 0 ? textParts.join(' ') : '0 วัน';
        },

        get retirementYearBeText() {
            if (!this.form.birth_date) return '-';
            const birth = new Date(this.form.birth_date);
            if (isNaN(birth.getTime())) return '-';
            
            const birthMonth = birth.getMonth() + 1; // 1-indexed
            const birthDay = birth.getDate();
            const birthYearBe = birth.getFullYear() + 543;
            
            const isBeforeOrOnSept30 = (birthMonth < 9) || (birthMonth === 9 && birthDay <= 30);
            const retireYearBe = isBeforeOrOnSept30 ? (birthYearBe + 60) : (birthYearBe + 61);
            
            return `พ.ศ. ${retireYearBe} (30 ก.ย. ${retireYearBe})`;
        },
    };
}
</script>
@endpush
