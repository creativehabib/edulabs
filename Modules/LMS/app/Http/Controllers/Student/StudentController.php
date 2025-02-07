<?php

namespace Modules\LMS\Http\Controllers\Student;

use Modules\LMS\Enums\ExamType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\LMS\Models\Certificate\UserCertificate;
use Modules\LMS\Repositories\Auth\UserRepository;
use Modules\LMS\Repositories\Student\StudentRepository;
use Modules\LMS\Repositories\Certificate\CertificateRepository;

class StudentController extends Controller
{
    public function __construct(
        protected StudentRepository $student,
        protected CertificateRepository $certificate
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $data = $this->student->dashboardReport();
        return view('portal::student.index', compact('data'));
    }

    /**
     *  logout
     */
    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect('/');
    }

    /**
     * certificate
     */
    public function certificate()
    {
        $certificates = UserCertificate::where('user_id', authCheck()->id)->get();
        return view('portal::student.certificate.index', compact('certificates'));
    }

    /**
     *  notification
     */
    public function notification()
    {
        return view('portal::student.notification.index');
    }

    /**
     *  allCourse
     */
    public function allCourse()
    {
        $enrollments = $this->student->courseEnrolled(10);
        return view('portal::student.course.index', compact('enrollments'));
    }

    /**
     * purchaseCourse
     */
    public function purchaseCourse()
    {
        $purchases = $this->student->purchaseCourses();
        return view('portal::student.course.purchase', compact('purchases'));
    }

    /**
     * purchaseCourse
     */
    public function bundleCourse()
    {
        $bundlesPurchases = $this->student->bundlePurchases();
        return view('portal::student.course.bundle', compact('bundlesPurchases'));
    }

    public function quizResult()
    {
        $userQuizzes = $this->student->getUserExamType(type: ExamType::QUIZ);
        return view('portal::student.quiz.quiz-result-list', compact('userQuizzes'));
    }

    public function assignmentList()
    {

        $assignments = $this->student->getUserExamType(type: ExamType::ASSIGNMENT);

        return view('portal::student.assignment.index', compact('assignments'));
    }

    public function certificateGenerate($id)
    {
        $this->certificate->requestCertificate($id);
        return  back();
    }

    public function certificateDownload($id)
    {
        $certificate = UserCertificate::where(['user_id' => authCheck()->id, 'id' => $id])->first();
        return view('portal::certificate.download', compact('certificate'));
    }


    public function wishlists()
    {
        $response =  UserRepository::wishlist();
        $wishlists = $response['data'] ?? [];
        return view('portal::student.wishlist.index', compact('wishlists'));
    }


    public function removeWishlist($id)
    {
        $response =  UserRepository::removeWishlist($id);

        $response['url'] = route('student.wishlist');
        return  response()->json($response);
    }
}
