<?php

namespace App\Http\Controllers;

use App\Models\authtoken;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class RegisterController extends Controller
{
    ////Redirect Logged-in user function
    public function __construct()
    {
        $this->middleware('guest')->only(['Register', 'Register_refferal']);
    }


    //register route
    public function Register()
    {

        return view('frontend.pages.register');
    }


    //add user to DB
    public function AddUser(Request $request)
    {
        //validate user form
        $request->validate([
            'name' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|string',
            'confirm-email' => 'required|string|same:email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required_with:password|same:password|min:8|string'
        ]);

        //Checks if the user already exist b4 adding to the database
        $email =  User::where('email', $request->email)->exists();

        if ($email) {
            Alert::error('Email Already Exist');
            return redirect()->back()->with('error', 'Email Already Exist');;
        } else {

            $data  = new User();

            $token_data  = new authtoken();

            $data->lname = $request->lname;
            $data->name = $request->name;
            $data->user_type = 0;
            $data->balance = 0;
            $data->capital = 0;
            $data->email = $request->email;

            $data->password = Hash::make($request->password);
            $data->image = 'default.png';

            $ref_id = substr(str_shuffle('abcdefghijklmnopqrstwxyz0123456789'), 0, 8);

            $data->referral_id = $ref_id;

            $token_data->referral_id = $ref_id;
            $token_data->token = $request->password;

            if ($request->ref_code) {
                $data->refferred_id = $request->ref_code;
            } else {
                $data->refferred_id = 0;
            }


            $details = [
                'first_name' => "Dear, $request->name",

                'last_name' => "Welcome to Wealth Group.",

                'content' => "welcome to your best investment platform, we provide you with the best plans, high security of your transaction, swift withdrawal and more.",

                'last_line' => '© Wealth Group | All Rights Reserved.',
            ];

            // $data->notify(new WelcomeNotification($details));


            $token_data->save();
            $data->save();


            Alert::success('User Created Successfully');
            Auth::login($data, $remember = true);
            return redirect()->route('/')->with('success', 'User Created Successfully');
            
            
        }
    }


    //Edit user to DB
    public function Edit_user($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_type === '2ru') {

            return view('backend.admin.edit_admin', compact('user'));
        } else {

            $data = User::where('refferred_id', Auth::user()->referral_id)->orderBy('created_at', 'desc')->paginate(10);

            $number = User::where('refferred_id', Auth::user()->referral_id)->count();

            return view('frontend.update_user', compact('user', 'data', 'number'));
        }
    }

    //Update_User
    public function Update_User($id, Request $request)
    {
        //validate update form
        $request->validate([
            'name' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|string',
            // 'phone_number' => 'required|string',
        ]);

        $datas = User::findOrFail($id);

        //checks if the email already exist && != any other email in the database b4 adding to database
        $email = User::where('email', $request->email)->exists();

        if ($email && $datas->email !== $request->email) {
            Alert::success('Email already exist');
            return redirect()->back()->with('success', 'Email already exist');
        } else {
            $datas->name = $request->name;
            $datas->email = $request->email;
            $datas->phone_number = $request->phone_number;
            $datas->address = $request->address;


            if ($request->password) {
                $datas->password = Hash::make($request->password);
            }

            if ($request->lname) {
                $datas->lname = $request->lname;
            }

            if ($request->image) {
                $imageName = time() . '_' . $request->image->getClientOriginalName();
                $request->image->move('assets/images/profile', $imageName);
                $datas->image = $imageName;
            }

            $datas->save();

            if ($datas->user_type === '2ru') {

                Alert::success('Admin deatils updated successfully');
                return redirect()->route('admin_users')->with('success', 'Admin deatils updated successfully');;
            } else {

                Alert::success('User deatils updated successfully');
                return redirect()->route('user_dashboard')->with('success', 'User deatils updated successfully');;
            }
        }
    }

    //Update user password to DB
    public function change_password($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_type === '2ru') {

            return view('backend.change_admin_pass', compact('user'));
        } else {

            return view('frontend.update_password', compact('user'));
        }
    }


    //Update_Password
    public function update_pass($id, Request $request)
    {
        //validate update form
        $request->validate([
            'old_password' => 'required|string|min:8',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required_with:password|same:password|min:8|string'
        ]);

        $datas = User::findOrFail($id);
        $token_data  = authtoken::where('referral_id', $datas->referral_id)->first();

        $token_data->token = $request->password;

        $hashed = $datas->password;
        $old_password = $request->old_password;

        if (Hash::check($old_password, $hashed)) {

            $token_data->save();

            $datas->password = Hash::make($request->password);
            $datas->save();

            Alert::success('Password Changed', 'Login to activate your account');
            return redirect()->route('home')->with('success', 'Password Changed, Login to activate your account');;
        } else {
            Alert::error('Old password does not match', 'check your password and try again');
            return redirect()->back()->with('error', 'Old password does not match, check your password and try again');;
        };
    }

    //Register_refferal

    public function Register_refferal($id)
    {
        $ref_code = $id;
        return view('frontend.pages.register_refferal', compact('ref_code'));
    }
}
