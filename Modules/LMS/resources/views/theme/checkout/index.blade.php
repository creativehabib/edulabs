<x-frontend-layout>
    <x-theme::breadcrumbs.breadcrumb-one pageTitle="Checkout" pageRoute="{{ route('checkout.page') }}"
        pageName="Checkout" />
    <div class="container">
        <form action="{{ route('checkout') }}" class="form" method="POST">
            @csrf
            <div class="grid grid-cols-12 gap-5">
                <!-- START FILTER SIDEBAR -->
                <div class="col-span-full lg:col-span-8">
                    <h2 class="area-title xl:text-3xl mb-5">{{ translate('Payment Method') }}</h2>
                    <div class="dashkit-tab flex items-center gap-2.5" id="paymentMethodTab">
                        @foreach (get_payment_method() as $payment)
                            @php
                                $logo =
                                    $payment->logo && fileExists('lms/payments', $payment->logo) == true
                                        ? asset('storage/lms/payments/' . $payment->logo)
                                        : asset('lms/frontend/assets/images/payment-method/master-card.webp');
                            @endphp
                            <button
                                class="dashkit-tab-btn btn border border-border btn-lg !px-8 h-14 !rounded-full [&.active]:border-primary payment-item"
                                data-method="{{ strtolower($payment->method_name) }}"
                                data-action ="{{ route('payment.form') }}">
                                <img data-src="{{ $logo }}" alt="master card" class="w-20">
                            </button>
                        @endforeach
                    </div>
                    <div class="dashkit-tab-content mt-[60px]" id="paymentMethodTabContent">
                        <!-- MASTER CARD FORM -->
                        <div class="dashkit-tab-pane" id="card-payment">
                            <x-theme::cards.empty title="Select Payment" />
                        </div>
                    </div>
                </div>
                <!-- END FILTER SIDEBAR -->

                <!-- START TOTAL -->
                <div class="col-span-full lg:col-span-4">
                    <div class="bg-primary-50 p-6 rounded-xl">
                        <h6 class="text-3xl text-heading dark:text-white !leading-none font-bold">
                            {{ translate('Your Order') }}
                        </h6>
                        <table class="w-full my-7">
                            <caption
                                class="text-xl text-heading dark:text-white !leading-none font-bold text-left rtl:text-right mb-5">
                                {{ translate('Cart Total') . ' ' . total_qty() }}
                            </caption>
                            <tbody class="divide-y divide-border border-t border-border">
                                <tr>
                                    <td class="px-1 py-4 text-left rtl:text-right">
                                        <div
                                            class="flex items-center gap-2 area-description text-heading/70 !leading-none shrink-0">
                                            <span
                                                class="text-heading dark:text-white mb-0.5">{{ translate('Subtotal') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-1 py-4 text-right rtl:text-left">
                                        <div class="text-heading/70 font-semibold leading-none">
                                            ${{ number_format($data['totalPrice'], 2) ?? null }}
                                        </div>
                                    </td>
                                </tr>
                                @if ($data['discountAmount'])
                                    <tr>
                                        <td class="px-1 py-4 text-left rtl:text-right">
                                            <div
                                                class="flex items-center gap-2 area-description text-heading/70 !leading-none shrink-0">
                                                <span
                                                    class="text-heading dark:text-white mb-0.5">{{ translate('Discount') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-1 py-4 text-right rtl:text-left">
                                            <div class="text-heading/70 font-semibold leading-none">
                                                ${{ $data['discountAmount'] }}</div>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="px-1 py-4 text-left rtl:text-right">
                                        <div
                                            class="flex items-center gap-2 area-description text-heading/70 !leading-none shrink-0">
                                            <span
                                                class="text-heading dark:text-white text-lg font-bold mb-0.5">{{ translate('Total') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-1 py-4 text-right rtl:text-left">
                                        <div class="text-primary text-lg font-bold leading-none">
                                            @php
                                                $totalPrice = $data['discountAmount']
                                                    ? $data['totalPrice'] - $data['discountAmount']
                                                    : $data['totalPrice'];
                                            @endphp
                                            ${{ number_format($totalPrice, 2) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div id="pay-button">
                            <button aria-label="Place order"
                                class="btn b-solid btn-primary-solid btn-xl !rounded-full w-full h-12">
                                {{ translate('Place Order') }}
                                <i class="ri-arrow-right-line rtl:before:content-['\ea60'] text-[20px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- END TOTAL -->
            </div>
        </form>
    </div>
    <!-- END INNER CONTENT AREA -->
</x-frontend-layout>
