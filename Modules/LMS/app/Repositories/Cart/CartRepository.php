<?php

namespace Modules\LMS\Repositories\Cart;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\LMS\Classes\Cart;
use Modules\LMS\Enums\DiscountType;
use Modules\LMS\Models\Coupon\Coupon;
use Modules\LMS\Models\Courses\Bundle\CourseBundle;
use Modules\LMS\Models\Courses\Course;
use Modules\LMS\Repositories\Payment\PaypalRepository;
use Modules\LMS\Repositories\Payment\StripeRepository;

class CartRepository
{
    protected static $course = Course::class;

    protected static $courseBundle = CourseBundle::class;

    protected static $coupon = Coupon::class;

    public function __construct(protected StripeRepository $stripe, protected PaypalRepository $paypal) {}

    /**
     *  addToCart
     *
     * @param  int  $id
     * @param  mixed  $request  [type of http request]
     */
    public function addToCart($request)
    {

        try {
            // Validate presence of required fields and check if item already exists in the cart.
            if (empty($request->type) &&  empty($request->id)) {
                return [
                    'status' => 'error',
                    'message' => translate('Invalid item type or ID provided')
                ];
            }

            if (Cart::checkCartExist($request->id)) {
                return [
                    'status' => 'error',
                    'message' => translate('Already in Cart'),
                ];
            }
            // Fetch item details based on type and prepare data for the cart.
            $data = $this->getCartItemData($request->type, $request->id);
            if (!$data) {
                return [
                    'status' => 'error',
                    'message' => translate('Item not found'),
                ];
            }
            Cart::add($data);
            return [
                'status' => 'success',
                'message' => translate('Added to Cart Successfully'),
                'url' => route('cart.page'),
            ];
        } catch (\Throwable $th) {
            // Handle any unexpected errors.
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }

    /**
     * Prepare item data for adding to the cart.
     *
     * @param string $type
     * @param int $id
     * @return array|null
     */
    protected function getCartItemData(string $type, int $id): ?array
    {
        switch ($type) {
            case 'bundle':
                $bundle = static::$courseBundle::with('instructor', 'organization', 'courses')->find($id);
                $userInfo = $bundle?->instructor?->userable ?? $bundle?->organization?->userable ?? null;
                $coursesId  = $bundle?->courses?->pluck('id')->toArray();
                return  [
                    'id' => $bundle->id,
                    'title' => $bundle->title,
                    'slug' => $bundle->slug,
                    'price' => $bundle->price,
                    'type' => 'bundle',
                    'image' => $bundle->thumbnail,
                    'author' => $userInfo->first_name ??  $userInfo->name ?? null,
                    'review' =>  instructorOrgUser_review($coursesId)
                ];

            case 'course':
                $course = static::$course::with('coursePrice', 'instructors')->find($id);
                $authorName = "";
                foreach ($course->instructors as $instructor) {
                    $authorName = $instructor?->userable?->first_name;
                    break;
                }
                $coursePrice = $this->coursePrice($course);
                return  [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'price' => $coursePrice['regular_price'],
                    'discount_price' => $coursePrice['discount_price'],
                    'type' => 'course',
                    'image' => $course->thumbnail,
                    'author' => $authorName,
                    'review' =>   review($course)
                ];

            default:
                return null;
        }
    }
    public static function coursePrice($course)
    {
        $discountPrice = 0;
        $regularPrice = 0;
        if (isset($course?->coursePrice) && $course?->coursePrice?->discount_flag == 1 &&   $course?->coursePrice?->discount_period != '' &&  dateCompare($course?->coursePrice?->discount_period) == true) {
            $discountPrice = dotZeroRemove($course?->coursePrice?->discounted_price ?? 0);
            $regularPrice = dotZeroRemove($course?->coursePrice?->price);
        } else {
            $regularPrice = dotZeroRemove($course?->coursePrice?->price ?? 0);
        }
        return ["discount_price" => $discountPrice, "regular_price" => $regularPrice];
    }

    /**
     * Method checkout
     *
     * @param  mixed  $request
     * @return array
     */
    public function checkout($request)
    {
        if (isset($request->payment_method) && $request->payment_method != '') {
            if ($request->payment_method == 'stripe') {
                $validation = Validator::make(
                    $request->all(),
                    [
                        'card_number' => 'required',
                        'expire' => 'required',
                        'cvv' => 'required',
                    ]
                );
                if ($validation->fails()) {
                    return response()->json(
                        [
                            'status' => 'error',
                            'errors' => $validation->errors(),
                        ]
                    );
                }

                return $this->stripe->makePayment($request);
            } elseif ($request->payment_method == 'paypal') {
                return $this->paypal->makePayment($request);
            }
        }

        return [
            'status' => 'error',
            'message' => 'please select payment method',
        ];
    }

    /**
     *  removeCart
     *
     * @param  int  $id
     */
    public function removeCart($id)
    {
        try {

            $cart = Cart::remove($id);

            if (Cart::totalPrice() == 0) {
                session()->flash('discount_amount');
            }

            if ($cart) {
                return [
                    'status' => 'success',
                    'message' => translate('Remove Successfully'),
                    'coupon' => Cart::discountAmount() ?? false,
                    'coupon_amount' => Cart::discountAmount(),
                    'total_amount' => Cart::totalPrice(),
                    'total_qty' => Cart::cartQty(),
                ];
            }
        } catch (\Throwable $th) {
            //throw $th;
            return [
                'status' => 'error',
                'message' => '!Something Wrong',
            ];
        }
    }

    public function applyCoupon($couponCode)
    {

        $message = [
            'status' => 'error',
            'message' => translate('Provide The Coupon Code',)
        ];
        if ($couponCode) {
            $coupon = static::$coupon::where(['code' => $couponCode, 'status' => 1])->first();
            if (! $coupon) {
                $message = [
                    'status' => 'error',
                    'message' => 'Coupon Not Found',
                ];
            }
            if (($coupon && $coupon->discount_type == DiscountType::PERCENTAGE) && dateCompare($coupon->expiration_date)) {
                return  $this->couponAmount($coupon->max_amount);
            }

            if (($coupon && $coupon->discount_type == DiscountType::FIXED) && dateCompare($coupon->expiration_date)) {
                return  $this->couponAmount($coupon->max_amount);
            }
        }
        return $message;
    }

    public function couponAmount($max_amount)
    {
        if ($max_amount > Cart::totalPrice()) {
            return  [
                'status' => 'error',
                'message' => 'You are not applicable',
            ];
        }
        Session::put('discount_amount', $max_amount);
        return [
            'status' => 'success',
            'message' => translate('Thank Your For Apply Coupon'),
            'coupon' => true,
            'coupon_amount' => Cart::discountAmount(),
            'total_amount' => Cart::totalPrice(),
        ];
    }
}
