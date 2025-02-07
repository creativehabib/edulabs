<?php

namespace Modules\LMS\Repositories\Payment;

use Modules\LMS\Classes\Cart;
use Omnipay\Omnipay;

class PaypalRepository extends PaymentRepository
{
    protected $gateway;

    protected $methodName = 'paypal';

    public function __construct()
    {
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(parent::geMethodInfo()->publishable_key ?? '');
        $this->gateway->setSecret(parent::geMethodInfo()->secret_key ?? '');
        $this->gateway->setTestMode(parent::geMethodInfo()->payment_mode == 0 ? true : false);
    }

    /**
     * makePayment
     *
     * @param  mixed  $request
     */
    public function makePayment($request)
    {
        try {
            $data = [
                'amount' => Cart::totalPrice(),
                'currency' => 'USD',
                'returnUrl' => url('/paypal/success'),
                'cancelUrl' => url(path: '/paypal/cancel'),
            ];

            return parent::send($data);
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }

    /**
     * success
     *
     * @param  mixed  $request
     */
    public function success($request)
    {
        try {
            if ($request->input('paymentId') && $request->input('PayerID')) {
                $transaction = $this->gateway->completePurchase(
                    [
                        'payer_id' => $request->input('PayerID'),
                        'transactionReference' => $request->input('paymentId'),
                    ]
                );
                $response = $transaction->send();
                if ($response->isSuccessful()) {
                    parent::purchaseCourses();
                    return [
                        'status' => 'success',
                        'url' => route('transaction.success'),
                    ];
                }
            }
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }
}
