<?php

namespace Modules\LMS\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Modules\LMS\Classes\Cart;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Modules\LMS\Repositories\Cart\CartRepository;
use Modules\LMS\Repositories\Payment\PaypalRepository;
use Modules\LMS\Repositories\Payment\StripeRepository;
use Modules\LMS\Repositories\Purchase\PurchaseRepository;

class CartController extends Controller
{
    public function __construct(
        protected StripeRepository $stripe,
        protected PaypalRepository $paypal,
        protected CartRepository $cart,
        protected PurchaseRepository $enrolled
    ) {}

    /**
     *  addToCart
     *
     * @param  int  $id
     */
    public function addToCart(Request $request)
    {
        $result = $this->cart->addToCart($request);
        return response()->json($result);
    }

    /**
     * removeCart
     *
     * @param  mixed  $id
     */
    public function removeCart(Request $request)
    {
        return $this->cart->removeCart($request->id);
    }

    /**
     * cartCourseList
     */
    public function cartCourseList()
    {
        $data['cartCourses'] = Cart::get();
        $data['totalPrice'] = Cart::totalPrice();
        $data['discountAmount'] = Cart::discountAmount();

        return view('theme::cart.index', compact('data'));
    }

    /**
     * Method applyCoupon
     */
    public function applyCoupon(Request $request)
    {
        if (!authCheck()) {
            return response()->json([
                'status' => 'error',
                'message' => 'please Login',

            ]);
        }

        return $this->cart->applyCoupon($request->coupon_code);
    }

    /**
     * checkoutPage
     */
    public function checkoutPage()
    {

        if (!authCheck()) {
            return redirect()->route('login');
        }

        // Check if the cart is empty, and redirect to home if so.
        if (count(Cart::get()) == 0) {
            return redirect()->route('home.index');
        }

        // Prepare cart data for the checkout view.
        $data = [
            'cartCourses' => Cart::get(),
            'totalPrice' => Cart::totalPrice(),
            'discountAmount' => Cart::discountAmount(),
        ];

        return view('theme::checkout.index', compact('data'));
    }

    /**
     * checkout
     */
    public function checkout(Request $request)
    {

        $response = $this->cart->checkout($request);
        return $response;
    }

    /**
     * Method success
     */
    public function success(Request $request)
    {
        $response = $this->paypal->success($request);
        if ($response['status'] == true) {
            return redirect($response['url']);
        }
    }

    /**
     * paypalCancel
     *
     * @return View
     */
    public function paypalCancel()
    {
        return redirect()->back();
    }

    /**
     * Method transactionSuccess
     *
     * @param  int  $id
     */
    public function transactionSuccess($id = null)
    {
        return view('theme::success.index');
    }

    /**
     *  paymentFormRender
     */
    public function paymentFormRender(Request $request)
    {
        $paymentMethod = $request->payment_method;
        // Render button and form views and add them to the response data.
        $data = [
            'button' => view('theme::payment-form.button', compact('paymentMethod'))->render(),
            'form' => view('theme::payment-form.form-input', compact('paymentMethod'))->render(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'payment' => true,
        ]);
    }

    /**
     *  courseEnrolled
     */
    public function courseEnrolled(Request $request)
    {
        if (!authCheck()) {
            toastr()->error(translate('Please Login'));
            return redirect()->back();
        }
        $response = $this->enrolled->courseEnrolled($request);
        if ($response['status'] !== "success") {
            return response()->json($response);
        }
        toastr()->success(translate('Thank you for Enrolling'));

        if ($request->ajax()) {
            return response()->json(['status' => $response['status'],  'type' => true]);
        }
        return redirect()->back();
    }
}
