<?php

namespace Modules\LMS\Repositories\Payment;

use Modules\LMS\Classes\Cart;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Enums\PurchaseType;
use Modules\LMS\Models\PaymentMethod;
use Modules\LMS\Models\Courses\Course;
use Modules\LMS\Models\Auth\Instructor;
use Modules\LMS\Models\Auth\Organization;
use Modules\LMS\Models\Purchase\Purchase;
use Modules\LMS\Models\Purchase\PurchaseDetails;
use Modules\LMS\Models\Courses\Bundle\CourseBundle;

abstract class PaymentRepository
{
    protected $gateway;

    protected $methodName;

    /**
     * makePayment
     *
     * @param  array  $array
     */
    abstract public function makePayment($array);

    /**
     * send
     *
     * @param  array  $array
     */
    public function send($array)
    {
        try {
            $response = $this->gateway->purchase($array)->send();
            if ($response->isRedirect()) {
                $response->redirect();
            } elseif ($response->isSuccessful()) {
                $this->purchaseCourses();

                return [
                    'status' => 'success',
                    'url' => route('transaction.success'),

                ];
            } else {
                return $response->getMessage();
            }
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }

    /**
     * purchaseCourses
     *
     * @return void
     */
    public function purchaseCourses()
    {

        DB::beginTransaction();

        try {
            $cart = Cart::get();
            if (! empty($cart['courses'])) {
                $purchase = Purchase::create(
                    [
                        'total_amount' => Cart::totalPrice(),
                        'payment_method' => $this->methodName,
                        'user_id' => authCheck()->id,
                        'type' => 'purchase',
                        'status' => 'success',
                    ]
                );
                foreach ($cart['courses'] as $cart) {
                    $item = '';
                    if ($cart['type'] == 'bundle') {
                        $item = CourseBundle::with('user.userable')->firstWhere('id', $cart['id']);
                    } else {
                        $item = Course::with('coursePrice', 'instructors.userable')->firstWhere('id', $cart['id']);
                    }

                    $purchaseDetail = [
                        'purchase_id' => $purchase->id,
                        'user_id' => authCheck()->id,
                        'course_id' => $cart['type'] == 'course' ? $item->id : null,
                        'bundle_id' => $cart['type'] == 'bundle' ? $item->id : null,
                        'price' => $item?->coursePrice ? ($item->coursePrice->price - $item->coursePrice->platform_fee) : ($item->price - $item->platform_fee),
                        'platform_fee' => $item->coursePrice->platform_fee ??  $item->platform_fee ?? 0,
                        'discount_price' => $cart['discount_price'] - ($item->coursePrice->platform_fee ?? $item->platform_fee) ?? 0,
                        'details' => $item,
                        'type' => PurchaseType::PURCHASE,
                        'purchase_type' => $cart['type'] == 'bundle' ? PurchaseType::BUNDLE : PurchaseType::COURSE,
                    ];

                    self::purchaseDetails($purchaseDetail);

                    if ($cart['type'] == 'course') {
                        $this->profitShareCalculate($item, $cart['discount_price']);
                    }
                    if ($cart['type'] == "bundle") {
                        $amount = $item->price - $item->platform_fee;
                        $userId =  $item?->user?->userable->id;
                        switch ($item?->user?->guard) {
                            case 'instructor':
                                $this->updateUserBalance($amount, $userId);
                                break;
                            case 'organization':
                                $this->orgProfit($amount, $userId);
                                break;
                        }
                    }
                }
            }
            Cart::clear();

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
        }
    }
    public static function purchaseDetails($purchaseDetail)
    {
        PurchaseDetails::create([
            'purchase_number' => strtoupper(orderNumber()),
            'purchase_id' => $purchaseDetail['purchase_id'],
            'user_id' => $purchaseDetail['user_id'],
            'course_id' => $purchaseDetail['course_id'],
            'bundle_id' => $purchaseDetail['bundle_id'],
            'price' => $purchaseDetail['price'],
            'platform_fee' =>  $purchaseDetail['platform_fee'],
            'discount_price' => $purchaseDetail['discount_price'],
            'details' => $purchaseDetail['details'],
            'type' => $purchaseDetail['type'],
            'purchase_type' => $purchaseDetail['purchase_type'],
        ]);
    }

    /**
     * geMethodInfo
     *
     * @param string
     * @return object
     */
    public function geMethodInfo()
    {
        return PaymentMethod::where('method_name', $this->methodName)->first();
    }

    public static function profitShareCalculate($item, $discountPrice)
    {
        $coursePrice = $item->coursePrice ?? null;
        $price =   ($discountPrice ? $discountPrice : $coursePrice->price) - $coursePrice->platform_fee;
        if ($item->organization_id) {
            $totalAmount = 0;
            if ($item->is_multiple_instructor == 1) {
                $totalAmount = self::instructorProfitShare($item->instructors, $price);
            }
            $orgProfit = $price - $totalAmount;
            if ($totalAmount !=  $price) {
                self::orgProfit($orgProfit, $item->organization->userable->id);
            }
        } else {
            if ($item->is_multiple_instructor !== 1) {
                foreach ($item->instructors as $key => $instructor) {
                    if ($key == 0) {
                        $instructorBalance = $price;
                        self::updateUserBalance($instructorBalance, $instructor->userable->id);
                        break;
                    }
                }
            }
            self::instructorProfitShare($item->instructors, $price);
        }
    }


    public static function orgProfit($profitBalance, $orgId)
    {
        $organization =  Organization::where('id', $orgId)->first();
        $organization->user_balance += $profitBalance;
        $organization->update();
    }

    public static function instructorProfitShare($instructors, $price)
    {
        $totalProfitAmount = 0;
        foreach ($instructors as  $instructor) {
            $percentage =  $instructor->pivot->percentage ?? 0;
            $profitBalance = $percentage != 0  ? $percentage / 100 * $price : 0;
            $totalProfitAmount +=  $profitBalance;
            self::updateUserBalance($profitBalance, $instructor->userable->id);
        }
        return $totalProfitAmount;
    }

    public static function updateUserBalance($amount, $userId)
    {
        $instructor =  Instructor::where('id', $userId)->first();
        $instructor->user_balance += $amount;
        $instructor->update();
    }
}
