@if ($paymentMethod == 'paypal')
    <input type="hidden" name="payment_method" value="paypal" />
@elseif($paymentMethod == 'stripe')
    <div class="grid grid-cols-2 gap-x-3 gap-y-4">
        <input type="hidden" name="payment_method" value="stripe" />

        <div class="col-span-full lg:col-auto">
            <label class="form-label" for="card-number">{{ translate('Card Number') }} <span
                    class="text-danger">*</span></label>
            <div class="relative">
                <input type="text" id="card-number" name="card_number" placeholder="143 323 454 5325"
                    id="card_number" oninput="numberOnly(this.id)" maxlength="16" class="form-input">
                <div class="absolute top-1/2 -translate-y-1/2 right-5 rtl:right-auto rtl:left-5">
                    <img src="{{ asset('lms/frontend/assets/images/payment-method/master-card.webp') }}"
                        alt="master card" class="w-10">
                </div>
            </div>
            <span class="text-danger error-text card_number_err"></span>
        </div>

        <div class="col-span-full lg:col-auto">
            <label class="form-label" for="exp-date">{{ translate('Expiry Date') }} <span
                    class="text-danger">*</span></label>
            <input type="text" name="expire" id="expire"
                placeholder="{{ translate('mm') }}/{{ translate('yy') }}" oninput="numberOnly(this.id);" id="expiry"
                maxlength="5" class="form-input">
            <span class="text-danger error-text expire_err"></span>
        </div>
        <div class="col-span-full">
            <label class="form-label" for="cvc">{{ translate('CVC') }}/{{ translate('CVV') }}</label>
            <input type="text" placeholder="3455" name="cvv" id="cvc" oninput="numberOnly(this.id);"
                maxlength="4" class="form-input">

            <span class="text-danger error-text cvv_err"></span>
        </div>
    </div>
@endif
