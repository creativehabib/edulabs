<?php

namespace Modules\LMS\Repositories\Payment;

use Modules\LMS\Classes\Cart;
use Omnipay\Omnipay;

class StripeRepository extends PaymentRepository
{
    protected $gateway;

    protected $methodName = 'stripe';

    public function __construct()
    {
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey(parent::geMethodInfo()->secret_key);
    }

    /**
     * Method makePayment
     *
     * @param  mixed  $request
     */
    public function makePayment($request)
    {
        try {

            $expire = explode('/', $request->expire);

            $formData = [
                'number' => $request->card_number,
                'expiryMonth' => $expire[0],
                'expiryYear' => $expire[1],
                'cvv' => $request->cvv,
            ];
            // $formData = array('number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
            $cardInfo = [
                'amount' => Cart::totalPrice(),
                'currency' => 'USD',
                'card' => $formData,
            ];

            return parent::send($cardInfo);
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => $th->getMessage()];
        }
    }
}
