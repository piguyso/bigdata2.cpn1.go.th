@if($teacherProfile)
<section class="space-y-8 animate-fade-in">
    <header class="border-b border-slate-100 pb-4 mb-6">
        <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-address-card text-emerald-500"></i> ข้อมูลประวัติการปฏิบัติหน้าที่
        </h3>
        <p class="text-xs text-slate-500 mt-1">
            ข้อมูลพื้นฐาน การศึกษา และวิทยฐานะตามที่ได้บันทึกไว้ในระบบสำรวจข้อมูล
        </p>
    </header>

    <div class="flex flex-col md:flex-row gap-8 items-start">
        <div class="shrink-0 flex flex-col items-center gap-3">
            <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-emerald-500/20 shadow-md bg-emerald-50 flex items-center justify-center">
                @if(!empty($teacherProfile['profile_image_path']))
                    <img src="{{ asset('storage/' . $teacherProfile['profile_image_path']) }}"
                         alt="Teacher Profile"
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('hidden');">
                    <div class="hidden w-full h-full flex items-center justify-center">
                        <i class="fa-solid fa-users text-emerald-600 text-4xl"></i>
                    </div>
                @else
                    <i class="fa-solid fa-users text-emerald-600 text-4xl"></i>
                @endif
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold rounded-full text-[9px] uppercase tracking-wider text-center">
                {{ $teacherProfile['position'] ?? '-' }}
            </span>
            @if(!empty($teacherProfile['academic_rank']))
                <span class="px-3 py-1 bg-slate-100 text-slate-600 font-bold rounded-full text-[9px] text-center">
                    {{ $teacherProfile['academic_rank'] }}
                </span>
            @endif
        </div>

        <!-- Basic Info Grid -->
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-xs">
            <div>
                <span class="text-slate-400 font-bold block mb-0.5">ชื่อ - นามสกุล</span>
                <span class="font-extrabold text-slate-800 text-sm">
                    {{ ($teacherProfile['prefix'] ?? '') . ($teacherProfile['first_name'] ?? '') . ' ' . ($teacherProfile['last_name'] ?? '') }}
                </span>
            </div>

            <div>
                <span class="text-slate-400 font-bold block mb-0.5">เลขประจำตัวประชาชน</span>
                <span class="font-bold text-slate-700">
                    @if(!empty($teacherProfile['personalid']))
                        {{ substr($teacherProfile['personalid'], 0, 3) }}-xxxx-{{ substr($teacherProfile['personalid'], -4) }}
                    @else
                        -
                    @endif
                </span>
            </div>

            <div>
                <span class="text-slate-400 font-bold block mb-0.5">สังกัดสถานศึกษา</span>
                <span class="font-bold text-slate-700">{{ $teacherProfile['school_name'] ?? '-' }}</span>
                @if(!empty($teacherProfile['school_network']))
                    <span class="block text-[10px] text-emerald-600 font-semibold mt-0.5">เครือข่าย: {{ $teacherProfile['school_network'] }}</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 font-bold block mb-0.5">กลุ่มสาระ / สาขาที่บรรจุ</span>
                <span class="font-bold text-slate-700">{{ $teacherProfile['recruitment_subject'] ?? '-' ?: '-' }}</span>
            </div>

            <div>
                <span class="text-slate-400 font-bold block mb-0.5">ปีเกิด (พ.ศ.) / อายุ</span>
                <span class="font-bold text-slate-700">
                    พ.ศ. {{ $teacherProfile['birth_year_be'] ?? '-' }} (อายุ {{ $teacherProfile['age'] ?? '-' }} ปี)
                </span>
            </div>

            <div>
                <span class="text-slate-400 font-bold block mb-0.5">วันที่บรรจุแต่งตั้ง</span>
                <span class="font-bold text-slate-700">
                    @if(!empty($teacherProfile['appointed_date']))
                        {{ date('d/m/', strtotime($teacherProfile['appointed_date'])) . (date('Y', strtotime($teacherProfile['appointed_date'])) + 543) }}
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Education Degrees -->
    @if(!empty($teacherProfile['bachelor_major']) || !empty($teacherProfile['master_major']) || !empty($teacherProfile['doctoral_major']))
        <div class="border-t border-slate-100 pt-5">
            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-graduation-cap text-emerald-500"></i> ประวัติการศึกษา
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                @if(!empty($teacherProfile['bachelor_major']))
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">ปริญญาตรี</span>
                        <span class="font-semibold text-slate-700">{{ $teacherProfile['bachelor_major'] }}</span>
                    </div>
                @endif
                @if(!empty($teacherProfile['master_major']))
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">ปริญญาโท</span>
                        <span class="font-semibold text-slate-700">{{ $teacherProfile['master_major'] }}</span>
                    </div>
                @endif
                @if(!empty($teacherProfile['doctoral_major']))
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">ปริญญาเอก</span>
                        <span class="font-semibold text-slate-700">{{ $teacherProfile['doctoral_major'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Subjects + Languages in 2 columns -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 pt-5 text-xs">

        <!-- Subjects Taught -->
        <div>
            <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-book-open text-emerald-500"></i> วิชาที่ทำการเรียนการสอน
            </h4>
            @if(!empty($teacherProfile['subjects']))
                <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                <th class="p-3">วิชาที่สอน</th>
                                <th class="p-3 text-right">ชั้นปีที่สอน</th>
                                <th class="p-3 text-right">ชม./สัปดาห์</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacherProfile['subjects'] as $sub)
                                @php $s = (array) $sub; @endphp
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                    <td class="p-3 font-semibold text-slate-700">{{ $s['subject_name'] ?? '-' }}</td>
                                    <td class="p-3 text-right text-slate-500">{{ $s['subject_grade'] ?? '-' }}</td>
                                    <td class="p-3 text-right text-slate-500">{{ $s['subject_hours'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-400 italic text-xs">ไม่มีข้อมูลวิชาที่สอน</p>
            @endif
        </div>

        <!-- Language Skills -->
        <div>
            <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-language text-emerald-500"></i> ทักษะภาษาต่างประเทศ
            </h4>
            <div class="space-y-3">
                @forelse($teacherProfile['cefr'] as $cefr)
                    @php $c = (array) $cefr; @endphp
                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-700 block text-xs">ภาษาอังกฤษ (CEFR)</span>
                            <span class="text-[10px] text-slate-400">
                                {{ $c['source'] === 'obec' ? 'สพฐ.' : 'อื่นๆ' }}
                                @if(!empty($c['issuer'])) · {{ $c['issuer'] }} @endif
                            </span>
                        </div>
                        <span class="px-3 py-1.5 bg-emerald-500 text-white font-extrabold rounded-xl text-xs shadow-sm">
                            {{ $c['cefr_level'] ?? '-' }}
                        </span>
                    </div>
                @empty
                @endforelse

                @forelse($teacherProfile['hsk'] as $hsk)
                    @php $h = (array) $hsk; @endphp
                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-700 block text-xs">ภาษาจีน (HSK)</span>
                            <span class="text-[10px] text-slate-400">
                                {{ $h['source'] ?? '' }}
                                @if(!empty($h['issuer'])) · {{ $h['issuer'] }} @endif
                            </span>
                        </div>
                        <span class="px-3 py-1.5 bg-sky-500 text-white font-extrabold rounded-xl text-xs shadow-sm">
                            ระดับ {{ $h['hsk_level'] ?? '-' }}
                        </span>
                    </div>
                @empty
                @endforelse

                @if(empty($teacherProfile['cefr']) && empty($teacherProfile['hsk']))
                    <p class="text-slate-400 italic text-xs">ไม่มีข้อมูลทักษะภาษาต่างประเทศ</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
