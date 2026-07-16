@extends('faculty.layout.app')

@section('page_title', 'My Profile')

@section('content')
@php
    $avatarLabel = trim(implode(' ', array_filter([
        $user->first_name ?? null,
        $user->last_name ?? null,
    ]))) ?: ($user->name ?? 'Faculty');
    $avatarSrc = $user->avatar_url;
    $phoneValue = old('phone_number', $user->phone_number ?? $faculty->phone_number);
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 flex items-center gap-3">
            <span class="iconify text-lg" data-icon="mdi:check-circle"></span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">
            <p class="font-semibold mb-2">Please fix the following:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="brand-gradient px-6 py-8 md:px-8">
            <div class="flex flex-col sm:flex-row items-center gap-5">
                <img id="profileAvatarPreview" src="{{ $avatarSrc }}" alt="Profile" class="w-20 h-20 rounded-2xl border-4 border-white/30 shadow-lg object-cover bg-white/10">
                <div class="text-center sm:text-left text-white">
                    <h3 class="text-2xl font-bold tracking-tight">{{ $avatarLabel }}</h3>
                    <p class="text-sm text-white/80 mt-1">{{ $user->email }}</p>
                    <p class="text-[11px] uppercase tracking-widest text-white/60 mt-2">Faculty Account</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('faculty.profile.update') }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-1">Profile Picture</h4>
                <p class="text-xs text-slate-400 mb-4">Upload a square photo (JPG, PNG, WEBP, or GIF — max 2MB).</p>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <img id="profileAvatarThumb" src="{{ $avatarSrc }}" alt="Avatar" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 bg-white shrink-0">
                    <div class="flex-1 min-w-0 space-y-3">
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                            class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-brand file:text-white file:text-xs file:font-bold hover:file:bg-brand-dark file:cursor-pointer">
                        @if($user->avatar)
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 cursor-pointer">
                                <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-300 text-brand focus:ring-brand/30">
                                Remove current photo
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-1">Update Information</h4>
                <p class="text-xs text-slate-400 mb-5">Edit your profile fields below, then save.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="first_name" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="mdi:account"></span>
                                First Name
                            </span>
                        </label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                            required>
                    </div>

                    <div>
                        <label for="middle_name" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="mdi:account-outline"></span>
                                Middle Name
                            </span>
                        </label>
                        <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $user->middle_name) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition">
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="mdi:account-box"></span>
                                Last Name
                            </span>
                        </label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                            required>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="mdi:email-outline"></span>
                                Email
                            </span>
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                            required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="phone_number" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="mdi:phone-outline"></span>
                                Phone Number
                            </span>
                        </label>
                        <input id="phone_number" name="phone_number" type="text" value="{{ $phoneValue }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                            placeholder="e.g. 09171234567">
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('faculty.dashboard') }}"
                    class="w-full sm:w-auto h-11 px-5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition flex items-center justify-center gap-2">
                    Cancel
                </a>
                <button type="submit"
                    class="w-full sm:w-auto h-11 px-6 brand-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-brand/20 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                    <span class="iconify" data-icon="mdi:content-save-outline"></span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('avatar')?.addEventListener('change', function (e) {
    const file = e.target.files?.[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    const preview = document.getElementById('profileAvatarPreview');
    const thumb = document.getElementById('profileAvatarThumb');
    if (preview) preview.src = url;
    if (thumb) thumb.src = url;
});
</script>
@endpush
