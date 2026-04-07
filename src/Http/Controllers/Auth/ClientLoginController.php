<?php

namespace Systha\Core\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Systha\Core\Models\Contact;
use Systha\Core\Models\VendorTemplate;

class ClientLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $vendor;
    protected $template;
    protected $menus;
    protected $redirectTo = '/login-dashboard';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $h = request()->getHttpHost();
        $host = $h;
        if (strpos($h, 'www.') !== false) {
            $indexof = strpos($h, 'www.') + 4;
            $host = substr($h, $indexof, strlen($h) - 1);
        }

        $temp = VendorTemplate::where('template_host', $host)->where('is_active', 1)->where('is_deleted', 0)->first();
        if (!$temp) {
            return redirect('/admin');
        }
        $this->template = $temp;
        $this->vendor = $temp->vendor;
        $this->vendor->address;
        $this->vendor->contact;
        // $this->middleware('vendor.guest:vendor')->except('logout');
    }

    public function clientLogin(Request $request)
    {
        // Validate login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Logout existing session (if any)
        if (Auth::guard('webContact')->check()) {
            Auth::guard('webContact')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Attempt login
        $credentials = $request->only('email', 'password');
        $credentials["contact_type"] = "customer";
        $credentials["table_name"] = "clients";

        // dd($credentials);
        if (Auth::guard('webContact')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect("/page/dashboard");
        }else{
            return response(["error"=>"Invalid credentials"],422);
        }

        // Login failed
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->only('email'));
    }

    public function login(Request $request)
    {

        if ($this->attemptLogin($request)) {

            return $this->sendLoginResponse($request);
        }
        $this->validateLoginReq($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            return $this->sendLoginResponse($request);
        }


        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt the login process
     *
     * @return user guard
     */
    public function attemptLogin(Request $request)
    {

        $this->validateLoginReq($request);

        // $user = Client::where('is_deleted', 0);
        $user = Contact::where(['email' => $request->email, 'is_deleted' => 0])->first();
        if (!$user->where('email', $request->email)->where('is_deleted', 0)->first()) {
            response()->json(['errors' => ['email' => 'Email doesn\'t exist']], 422)->send();
            exit();
        } else {
            $user = $user->where('email', $request->email)->where('is_deleted', 0)->first();
        }
        // dd($user);

        if (!Hash::check($request->password, $user->password)) {
            response()->json(['errors' => ['password' => 'Password incorrect!']], 422)->send();
            exit();
        }

        return Auth::guard('webContact')->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    public function validateLoginReq(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'required' => 'required*'
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        // $this->clearLoginAttempts($request);
        // dd(Auth::guard('contact'));
        // $contact = auth('webContact')->user()->user();
        // dd($contact);
        return $this->authenticated($request, Auth::guard('webContact')->user()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    public function authenticated($request, $user)
    {
        // dd('user',$user);
        // if(isset($request->process) && $request->process == "setup") {
        // if($user->is_setup == false && $user->client_type == 1) {
        //     return response()->json(['setup' => 1,'vendorClient'=>1], 200);
        // }
        // }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('webContact')->logout();

        $request->session()->invalidate();
        redirect('/page/login');

        // return $this->loggedOut($request) ?: redirect('/page/login');
    }

}
