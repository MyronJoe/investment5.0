<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\investments;
use App\Models\User;
use App\Models\withdrawal;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    ////Redirect Logged-in user function
    public function __construct()
    {
        $this->middleware('guest')->only(['login']);
    }
    /**
     * Display login page.
     * 
     * @return Renderable
     */
    public function login()
    {
        return view('frontend.pages.login');
    }

    /**
     * Handle account login request
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login_user(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        if (!Auth::validate($credentials)) {
            Alert::error('Invalid Credentials');
            return redirect('login');
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        $email =  User::where('email', $credentials['email'])->first();

        $details = [
            'first_name' => "Dear, $email->name",

            'last_name' => "Welcome to Wealth Group.",

            'account' => "Your login was successful",

            'last_line' => '© Wealth Group | All Rights Reserved.',
        ];

        // $email->notify(new LoginNotification($details));

        Auth::login($user);

        $userType = Auth::user()->user_type;

        if ($userType === '2ru') {

            $users = User::where('user_type', 0)->count();

            $admins = User::where('user_type', '2ru')->count();

            $investment = investments::all()->count();

            $pending_investment = Investments::where('status', 0)->count();

            $done_investment = Investments::where('status', 1)->count();

            $withdrawal = withdrawal::all()->count();

            $pending_withdrawal = withdrawal::where('status', 0)->count();

            $done_withdrawal = withdrawal::where('status', 1)->count();

            return view('backend.home', compact('users', 'admins', 'investment', 'pending_investment', 'done_investment', 'withdrawal', 'pending_withdrawal', 'done_withdrawal'));
        }else{
            return redirect()->route('user_dashboard');
        }

        return $this->authenticated($request, $user);
    }

    /**
     * Handle response after user authenticated
     * 
     * @param Request $request
     * @param Auth $user
     * 
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended();
    }
}
