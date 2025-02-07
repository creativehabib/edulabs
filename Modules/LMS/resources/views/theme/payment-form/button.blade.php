@if ($paymentMethod == 'paypal')
    <button type="submit" aria-label="Place an order" data-spinning-button class="btn b-solid btn-primary-solid btn-xl !rounded-full w-full h-12">
        {{ translate('Place Order') }}
        <i class="ri-arrow-right-line rtl:before:content-['\ea60']"></i>
    </button>
@elseif($paymentMethod == 'stripe')
    <button type="submit" aria-label="Place an order" class="btn b-solid btn-primary-solid btn-xl !rounded-full w-full h-12"> 
        {{ translate('Place Order') }}
        <i class="ri-arrow-right-line rtl:before:content-['\ea60']"></i>
    </button>
@endif
