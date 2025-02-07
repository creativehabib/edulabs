{{-- @foreach ($instructors as $instructor)
    @php
        $user = $instructor?->userable ?? null;
        $profile_img = $user?->profile_img ?? '';
        $imgSrc =
            $profile_img && fileExists('lms/instructors', $profile_img) == true
                ? asset("storage/lms/instructors/{$profile_img}")
                : asset('lms/frontend/assets/images/370x396.svg');

    @endphp
    <div
        class="lg:col-span-6 col-span-full insturctor-card team-thumb-bg rounded-20 hover:bg-[#D9DDFE] group/instructor xl:p-6 p-4">
        <div class="bg-[#FCFCFF] flex-center flex-col text-center rounded-2xl sm:p-6 p-4">
            <div class="size-[84px] rounded-full bg-[#DFE3FE] relative">
                <!-- main thumb -->
                <img data-src="{{ $imgSrc }}" alt="Thumbnail image" class="size-full object-cover rounded-full">
                <!-- verify badge -->
                <img data-src="{{ asset('lms/frontend/assets/images/icons/verify.webp') }}" alt="Verified icon"
                    class="absolute z-20 bottom-1.5 right-0">
            </div>
            <a href="{{ route('users.detail', $instructor->id) }}" aria-label="Instructor full name">
                <h3
                    class="xl:text-28 text-xl font-semibold text-heading dark:text-white hover:text-primary leading-none mt-5 mb-2 duration-200">
                    {{ $user?->first_name . ' ' . $user?->last_name }}</h3>
            </a>

            @php
                $city = $user?->city?->name ?? null;
            @endphp
            <span class="font-medium">{{ $city ? $city . ', ' : '' }} {{ $user?->country?->name }}
            </span>

            <div class="flex divide-x divide-gray-500 *:px-4 first:*:pl-0 last:*:pr-0 xl:mt-8 mt-4">
                <div>
                    <span class="text-lg font-semibold text-heading">{{ $instructor?->courses?->count() ?? 0 }}+</span>
                    <h6 class="text-sm">
                        {{ translate('Courses') }}
                    </h6>
                </div>
                <div class="xl:block hidden">
                    <span class="text-lg font-semibold text-heading">{{ $instructor?->courses?->count() ?? 0 }}+</span>
                    <h6 class="text-sm">
                        {{ translate('Total Students') }}
                    </h6>
                </div>
            </div>
        </div>
    </div>
@endforeach --}}
