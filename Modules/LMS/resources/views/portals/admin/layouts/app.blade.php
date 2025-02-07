@php
    $backendSetting = get_theme_option(key: 'backend_general') ?? null;
    $languages = get_all_language();
@endphp
<!DOCTYPE html>
<html lang="en" class="group" data-sidebar-size="lg">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ isset($backendSetting['app_name']) ? $backendSetting['app_name'] . ' -' : null }} {{ $title ?? null }}
    </title>
    <meta name="robots" content="noindex, follow">
    <meta name="description" content="web development agency">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    @php
        $backendLogo = get_theme_option(key: 'backend_logo') ?? null;
    @endphp
    @if (isset($backendLogo['favicon']) &&
            fileExists($folder = 'lms/theme-options', $fileName = $backendLogo['favicon']) == true &&
            $backendLogo['favicon'] !== '')
        <link rel="shortcut icon" type="image/x-icon"
            href="{{ asset('storage/lms/theme-options/' . $backendLogo['favicon']) }}">
        </div>
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('lms/frontend/assets/images/favicon.ico') }}">
    @endif
    <!-- Style CSS -->
    <link rel="stylesheet" href="{{ asset('lms/assets/css/vendor/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lms/assets/css/vendor/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lms/assets/css/vendor/summernote.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lms/assets/css/vendor/select/select2.min.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('lms/assets/css/output.min.css?v=' . asset_version('lms/assets/css/output.min.css')) }}" />

    @stack('css')
</head>

<body class="bg-body-light dark:bg-dark-body group-data-[theme-width=box]:container group-data-[theme-width=box]:max-w-screen-3xl xl:group-data-[theme-width=box]:px-4">
    <div id="preloader" class="dark:!bg-dark-body">
        <div id="status">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
            </div>
        </div>
    </div>

    <!-- START AI CONTENT GENERATE -->
    <div class="fixed top-1/4 right-0 rtl:right-auto rtl:left-0 translate-x-[98px] rtl:-translate-x-[98px] hover:translate-x-0 z-backdrop duration-200">
        <button type="button" class="ai-content-modal-btn px-3.5 py-2 flex-center gap-3.5 bg-primary dark:bg-dark-icon text-white duration-300 rounded-l-lg rtl:rounded-l-none rtl:rounded-r-lg shadow-md" aria-label="Ai content generate button">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 512 512">
                <path fill="currentColor" fill-rule="evenodd" d="M384 128v256H128V128zm-148.25 64h-24.932l-47.334 128h22.493l8.936-25.023h56.662L260.32 320h23.847zm88.344 0h-22.402v128h22.402zm-101 21.475l22.315 63.858h-44.274zM405.335 320H448v42.667h-42.667zm-256 85.333H192V448h-42.667zm85.333 0h42.666V448h-42.666zM149.333 64H192v42.667h-42.667zM320 405.333h42.667V448H320zM234.667 64h42.666v42.667h-42.666zM320 64h42.667v42.667H320zm85.333 170.667H448v42.666h-42.667zM64 320h42.667v42.667H64zm341.333-170.667H448V192h-42.667zM64 234.667h42.667v42.666H64zm0-85.334h42.667V192H64z" />
            </svg>
            <span>{{ translate('AI Content') }}</span>
        </button>
    </div>
    <div id="ai-modal-generate" class="bg-white rounded-lg !fixed top-0 right-0 m-4 z-modal duration-300 shadow-[0_0_10px_1px_rgba(0,0,0,0.75)] invisible opacity-0 hidden">
        <div class="w-full max-w-screen-md">
            <!-- Modal Header -->
            <div id="ai-content-modal-dragger" class="flex items-center justify-between p-4 border-b cursor-move">
                <div class="card-title text-lg">
                    {{ translate('AI Content') }}
                </div>
                <button type="button" aria-label="Ai content modal close button"
                    class="absolute top-3 end-2.5 text-heading dark:text-white bg-gray-200 rounded-lg size-8 flex-center ai-content-modal-close-btn">
                    <i class="ri-close-line text-inherit"></i>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-4 pt-0 max-h-[80vh] overflow-auto">
                <form action="{{ route('generate.content') }}" method="post" class="form mt-2">
                    @csrf
                    <label class="form-label block">
                        <select name="service_type_id" class="singleSelect">
                            <option disabled selected>{{ translate('Select Type') }}</option>
                            @foreach (ai_service_type() as $aiServiceType)
                                <option value="{{ $aiServiceType->id }}">{{ $aiServiceType->title }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-label block">
                        <input type="text" name="keyword" placeholder="{{ translate('Enter Keyword') }}" class="form-input" />
                        <span class="text-danger error-text keyword_err"></span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="form-label block">
                            <input type="number" name="max_token" placeholder="{{ translate('Max content length') }}" class="form-input" />
                        </label>
                        <label class="form-label block">
                            <select name="language" class="singleSelect">
                                {{-- <option selected disabled>{{ translate('Select Language') }}</option> --}}
                                @foreach ($languages as $key => $language)
                                    <option value="{{ $language->name }}">{{ $language->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text language_err"></span>
                        </label>
                    </div>
                    <label class="form-label block">
                        <span class="form-label inline-block">{{ translate('Output') }}</span>
                        <textarea id="outputContent" class="form-input max-h-[300px]" rows="15"></textarea>
                        <div class="flex items-center justify-end gap-2 mt-1">
                            <button type="button" class="btn b-outline btn-primary-outline btn-sm copytext cursor-pointer">{{ translate('Copy') }}</button>
                        </div>
                    </label>
                    <button type="submit" class="btn b-solid btn-primary-solid dk-theme-card-square">
                        {{ translate('Generate') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- END AI CONTENT GENERATE -->

    <!-- AI CONTENT GENERATE -->

    <x-portal::admin.header />

    <x-portal::admin.sidebar />

    <x-portal::admin.settings-sidebar />

    <!-- Start Main Content -->
    <div
        class="main-content group-data-[sidebar-size=lg]:xl:ml-[calc(theme('spacing.app-menu')_+_16px)] rtl:group-data-[sidebar-size=lg]:xl:ml-0 rtl:group-data-[sidebar-size=lg]:xl:mr-[calc(theme('spacing.app-menu')_+_16px)] group-data-[sidebar-size=sm]:xl:ml-[calc(theme('spacing.app-menu-sm')_+_16px)] rtl:group-data-[sidebar-size=sm]:xl:ml-0 rtl:group-data-[sidebar-size=sm]:xl:mr-[calc(theme('spacing.app-menu-sm')_+_16px)] px-4 group-data-[theme-width=box]:xl:px-0 duration-300">
        {{ $slot }}
    </div>
    <!-- End Main Content -->

    @include('portal::admin.placeholder')
    @auth
        @if (Auth::user()->guard == 'instructor')
            <input type="hidden" id="baseUrl" value="{{ route('instructor.dashboard') }}" />
        @elseif (Auth::user()->guard == 'organization')
            <input type="hidden" id="baseUrl" value="{{ route('organization.dashboard') }}" />
        @else
            <input type="hidden" id="baseUrl" value="{{ route('admin.dashboard') }}" />
        @endif
    @endauth

    <script src="{{ asset('lms/assets/js/vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/flowbite.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/smooth-scrollbar/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/summernote.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/toastr.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/select2.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/flatpickr.min.js') }}"></script>
    <script src="{{ asset('lms/assets/js/vendor/sweetalert2.js') }}"></script>


    <script>
        let baseUrl = $("#baseUrl").val();
        const textAreaPlaceholder = "{{ translate('Write your description here') }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="{{ edulab_asset('lms/assets/js/layout.js') }}"></script>
    <script src="{{ edulab_asset('lms/assets/js/main.js') }}"></script>
    <script src="{{ edulab_asset('lms/assets/js/component/switcher.js') }}"></script>
    <script src="{{ edulab_asset('lms/assets/js/component/modal.js') }}"></script>
    <script src="{{ edulab_asset('lms/assets/js/tab.js') }}"></script>
    <script src="{{ edulab_asset('lms/assets/js/custom.js') }}"></script>
    @stack('js')
</body>

</html>
