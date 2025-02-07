<x-dashboard-layout>
    <x-slot:title> {{ translate('Create Method') }} </x-slot:title>
    <!-- BREADCRUMB -->
    <x-portal::admin.breadcrumb back-url="{{ route('payment-method.index') }}"
        title="{{ isset($method) ? 'Edit' : 'Create' }} Payment Method" page-to="Payment Method" />
    <form method="post" class="form"
        action="{{ isset($method) ? route('payment-method.update', $method->id) : route('payment-method.store') }}">
        @csrf
        @if (isset($method))
            @method('PUT')
        @endif
        <div class="card">
            <div>
                <label for="forumTitle" class="form-label"> {{ translate('Secret key') }} <span
                        class="require-field">*</span></label>
                <input type="text" id="forumTitle" placeholder="{{ translate('Secret key') }}" name="secret_key"
                    value="{{ $method->secret_key ?? '' }}" class="form-input">
                <span class="text-danger error-text secret_key_err"></span>
            </div>
            <div class="mt-6">
                <label for="forumDescription" class="form-label"> {{ translate('Publishable Key') }} <span
                        class="require-field">*</span></label>
                <input type="text" id="forumTitle" placeholder="{{ translate('Publishable or Client Id') }}"
                    name="publishable_key" value="{{ $method->publishable_key ?? '' }}" class="form-input">
                <span class="text-danger error-text publishable_key_err"></span>
            </div>
            <div class="mt-6">
                <label for="forumDescription" class="form-label">{{ translate('Method Name') }}</label>
                <input type="text" id="forumTitle" placeholder="{{ translate('Payment Method Name') }}"
                    name="method_name" value="{{ $method->method_name ?? '' }}" class="form-input">
                <span class="text-danger error-text payment_mode_err"></span>
            </div>
            @if (isset($method) && ($method->method_name == 'stripe' || $method->method_name == 'paypal'))
                <div class="mt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="appearance-none peer status-change" name="payment_mode"
                            {{ $method->payment_mode == 1 ? 'checked' : '' }} role="switch">
                        <span class="switcher switcher-primary-solid"></span>


                        {!! $method->payment_mode == 1
                            ? '<span class="form-label m-0">' . translate('Live') . '</span>'
                            : '<span class="form-label m-0">' . translate('Sandbox') . '</span>' !!}
                        </td>
                    </label>
                </div>
            @endif
            <div class="mt-6">
                <label class="form-label"> {{ translate('Logo') }} <span class="require-field">*</span></label>
                <label for="imgage"
                    class="dropzone-wrappe file-container ac-bg text-xs leading-none font-semibold mb-3 cursor-pointer size-[200px] flex flex-col items-center justify-center gap-2.5 border border-dashed border-gray-900 rounded-10 dk-theme-card-square">
                    <input type="file" hidden name="image" id="imgage"
                        class="dropzone dropzone-image img-src peer/file">
                    <span class="flex-center flex-col peer-[.uploaded]/file:hidden">
                        <img src="{{ asset('lms/assets/images/icons/upload-file.svg') }}" alt="file-icon"
                            class="size-8">
                        <div class="text-gray-500 dark:text-dark-text mt-2"> {{ translate('Choose file') }} </div>
                    </span>
                    <span class="text-danger error-text image_err"></span>
                </label>
                <div class="preview-zone dropzone-preview">
                    <div class="box box-solid">
                        <div class="box-body flex items-center gap-2 flex-wrap">
                            @if (isset($method) && fileExists($folder = 'lms/payments', $fileName = $method->logo) == true && $method->logo != '')
                                <div class="img-thumb-wrapper"> <button class="remove">
                                        <i class="ri-close-line text-inherit text-[13px]"></i> </button>
                                    <img class="img-thumb" width="100"
                                        src="{{ asset('storage/lms/payments/' . $method->logo) }}" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card flex justify-end">
            <button type="submit" class="btn b-solid btn-primary-solid cursor-pointer dk-theme-card-square">
                {{ isset($method) ? translate('Update') : translate('Save') }}
            </button>
        </div>
    </form>
</x-dashboard-layout>
