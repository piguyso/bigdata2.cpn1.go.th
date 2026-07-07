<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Load Cropper.js from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Logo Upload Section with Crop -->
        <div class="flex items-center gap-6 mb-8" x-data="logoUploader()">
            <div class="relative group cursor-pointer shrink-0" @click="$refs.fileInput.click()">
                <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-emerald-500/20 group-hover:border-emerald-500 transition-all duration-300 shadow-md flex items-center justify-center bg-slate-100 relative">
                    <!-- Current Logo Image -->
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Logo Preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!previewUrl">
                        @if($user->logo)
                            <img src="{{ asset('storage/' . $user->logo) }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            <div class="text-3xl font-bold text-emerald-600 bg-emerald-50 w-full h-full flex items-center justify-center uppercase">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </template>
                    
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-slate-900/60 flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <i class="fa-solid fa-camera text-lg mb-1"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">เปลี่ยนโลโก้</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-bold text-slate-800">โลโก้โปรไฟล์</h4>
                <p class="text-xs text-slate-500 mt-1">แนะนำรูปภาพสี่เหลี่ยมจัตุรัส ขนาดขั้นต่ำ 200x200 พิกเซล (ระบบจะครอบตัดเป็น 1:1 อัตโนมัติ)</p>
                <button type="button" class="mt-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition" @click="$refs.fileInput.click()">
                    เลือกรูปภาพ...
                </button>
            </div>

            <!-- Hidden Inputs -->
            <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="fileSelected($event)">
            <input type="hidden" name="logo_data" x-model="logoData">

            <!-- Cropper Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
                <div class="bg-white rounded-[2rem] max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-crop text-emerald-500"></i> ครอบตัดรูปภาพโลโก้
                        </h3>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="p-6 bg-slate-50 flex justify-center items-center overflow-hidden h-72">
                        <img id="cropperImage" class="max-w-full max-h-full block">
                    </div>
                    
                    <!-- Cropper Controls -->
                    <div class="px-6 py-3 border-t border-slate-100 flex justify-center gap-4 text-slate-500 bg-white">
                        <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="หมุนซ้าย" @click="cropper.rotate(-90)">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="หมุนขวา" @click="cropper.rotate(90)">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                        <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="ซูมเข้า" @click="cropper.zoom(0.1)">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                        <button type="button" class="p-2 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="ซูมออก" @click="cropper.zoom(-0.1)">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition">
                            ยกเลิก
                        </button>
                        <button type="button" @click="cropImage()" class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl font-bold text-sm hover:bg-emerald-600 transition shadow-lg shadow-emerald-100">
                            ตกลง
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function logoUploader() {
                return {
                    showModal: false,
                    previewUrl: null,
                    logoData: '',
                    cropper: null,
                    
                    fileSelected(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.showModal = true;
                            const imageEl = document.getElementById('cropperImage');
                            imageEl.src = e.target.result;
                            
                            // รอรูปโหลดเสร็จจึงสร้างอินสแตนซ์ Cropper
                            setTimeout(() => {
                                if (this.cropper) {
                                    this.cropper.destroy();
                                }
                                this.cropper = new Cropper(imageEl, {
                                    aspectRatio: 1,
                                    viewMode: 1,
                                    background: false,
                                    responsive: true,
                                    checkOrientation: false
                                });
                            }, 100);
                        };
                        reader.readAsDataURL(file);
                    },
                    
                    cropImage() {
                        if (!this.cropper) return;
                        
                        // สร้าง Canvas ขนาด 300x300
                        const canvas = this.cropper.getCroppedCanvas({
                            width: 300,
                            height: 300
                        });
                        
                        this.logoData = canvas.toDataURL('image/jpeg', 0.9);
                        this.previewUrl = this.logoData;
                        this.closeModal();
                    },
                    
                    closeModal() {
                        this.showModal = false;
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                        this.$refs.fileInput.value = '';
                    }
                };
            }
        </script>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
