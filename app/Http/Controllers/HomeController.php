<?php

namespace App\Http\Controllers;

use App\Models\investments;
use App\Models\message;
use App\Models\Plans;
use App\Models\User;
use App\Models\withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class HomeController extends Controller
{

    public function index()
    {
        return view('frontend.pages.home');
    }

    //Home redirect

    public function Home()
    {
        $userType = Auth::user()->user_type;

        if ($userType === '2ru') {

            $users = User::where('user_type', 0)->count();

            $admins = User::where('user_type', '2ru')->count();

            $investment = Investments::all()->count();

            $pending_investment = Investments::where('status', 0)->count();

            $done_investment = Investments::where('status', 1)->count();

            $withdrawal = withdrawal::all()->count();

            $pending_withdrawal = withdrawal::where('status', 0)->count();

            $done_withdrawal = withdrawal::where('status', 1)->count();

            return view('backend.home', compact('users', 'admins', 'investment', 'pending_investment', 'done_investment', 'withdrawal', 'pending_withdrawal', 'done_withdrawal'));
        } else {

            return redirect()->route('user_dashboard');
        }
    }


    //user dashboard
    public function UserDashboard()
    {
        $investment = Investments::where('investor', Auth::user()->id)->where('status', 1)->orderBy('created_at', 'desc')->get();

        $investments = Investments::where('investor', Auth::user()->id)->orderBy('created_at', 'desc')->get();

        $balance = User::where('id', Auth::user()->id)->first();

        $deposits = Investments::where('investor', Auth::user()->id)->sum('amount');

        $earnings = Investments::where('investor', Auth::user()->id)->sum('daily_income');

        $pending_Investment = Investments::where('investor', Auth::user()->id)->where('status', 0)->sum('amount');

        $withdraw = withdrawal::where('user_id', Auth::user()->id)->sum('amount');

        $pending_withdraw = withdrawal::where('user_id', Auth::user()->id)->where('status', 0)->sum('amount');

        return view('frontend.dashboard', compact('investment', 'investments', 'pending_withdraw', 'pending_Investment', 'deposits', 'earnings', 'withdraw', 'balance'));
    }

    //All_plans
    public function All_plans()
    {
        return view('frontend.pages.all_plans');
    }

    //All_plans
    public function investment_plans()
    {
        return view('frontend.dashboard_invest');
    }

    //All_plans
    public function user_support()
    {
        return view('frontend.user-support');
    }

    //deposit-history
    public function deposit_history()
    {
        $data = investments::where('investor', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);

        $investments_num = investments::where('investor', Auth::user()->id)->orderBy('created_at', 'desc')->count();

        return view('frontend.deposit-history', compact('data', 'investments_num'));
    }

    //withdrawal_history
    public function withdrawal_history()
    {

        $data = withdrawal::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);

        $withdrawal_num = withdrawal::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->count();

        return view('frontend.withdrawal_history', compact('data', 'withdrawal_num'));
    }

    //pending_history
    public function pending_history()
    {
        $data = investments::where('investor', Auth::user()->id)->where('status', 0)->orderBy('created_at', 'desc')->paginate(10);

        $investments_num = investments::where('investor', Auth::user()->id)->where('status', 0)->orderBy('created_at', 'desc')->count();

        return view('frontend.pending_history', compact('data', 'investments_num'));
    }

    //confirmed_history
    public function confirmed_history()
    {
        $data = investments::where('investor', Auth::user()->id)->where('status', 2)->orderBy('created_at', 'desc')->paginate(10);

        $investments_num = investments::where('investor', Auth::user()->id)->where('status', 2)->orderBy('created_at', 'desc')->count();

        return view('frontend.confirmed_history', compact('data', 'investments_num'));
    }

    //active_history
    public function active_history()
    {
        $data = investments::where('investor', Auth::user()->id)->where('status', 1)->orderBy('created_at', 'desc')->paginate(10);

        $investments_num = investments::where('investor', Auth::user()->id)->where('status', 1)->orderBy('created_at', 'desc')->count();

        return view('frontend.active_history', compact('data', 'investments_num'));
    }

    //fund_account
    public function fund_account()
    {
        return view('frontend.fund_account');
    }

    //faqs
    public function faqs()
    {
        return view('frontend.pages.questions');
    }

    //privacy
    public function privacy()
    {
        return view('frontend.pages.privacy');
    }

    //terms
    public function terms()
    {
        return view('frontend.pages.terms');
    }

    //About
    public function About()
    {

        return view('frontend.pages.about');
    }

    //Contact
    public function Contact()
    {

        return view('frontend.pages.contact');
    }

    //Invest_now
    public function Invest_now($id)
    {
        $data = Plans::findorfail($id);

        $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);

        return view('frontend.invest', compact('data', 'token'));
    }

    //Invest
    public function Invest(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'method' => 'required'
        ]);


        if ($request->amount < $request->min) {
            Alert::error('Inputed amount is less than minimum amount');
            return redirect()->back()->with('error', 'Inputed amount is less than minimum amount');;
        } elseif ($request->amount > $request->max) {
            Alert::error('Inputed amount is greater than maximum amount');
            return redirect()->back()->with('error', 'Inputed amount is greater than maximum amount');;
        } else {

            $data = Investments::where('investor', Auth::user()->id)->where('status', 0)->count();
            if ($data > 0) {
                Alert::error('Complete Your Pending Transaction');
                return redirect()->route('pending-history')->with('error', 'Complete Your Pending Transaction');
            } else {
                $data  = new investments();



                $data->investor = Auth::user()->id;
                $data->planName = $request->planName;
                $data->min = $request->min;
                $data->max = $request->max;
                $data->RIO = $request->RIO;
                $data->duration = $request->duration;
                $data->status = "0";
                $data->method = $request->method;
                $data->amount = $request->amount;
                $data->token = $request->token;
                $data->day_num = 0;
                $data->daily_income = 0;



                if ($request->RIO) {
                    $value = $request->RIO / 100;
                    $result = $value * $request->amount;
                    $data->daily_percent = $result;
                }


                if ($request->duration) {
                    $value = $request->RIO * $request->duration;
                    $mul_value = $value / 100;
                    $div_value = $mul_value * $request->amount;
                    $data->profit = $div_value;

                    $data->total = $div_value + $request->amount;
                }



                Session::put('details', $request->all());
                Session::save();


                $data->save();
            }

            return redirect()->route('wallet-address');
        }
    }

    //wallet_address
    public function wallet_address()
    {
        $data = Session::get('details');

        $object = json_decode(json_encode($data));

        return view('frontend.wallet', compact('object'));
    }

    //wallet_confirm
    public function wallet_confirm(Request $request)
    {

        return redirect()->route('prove');
    }

    //wallet_confirm for confirm
    public function confirm(Request $request)
    {
        $id = $request->id;

        $object = investments::findorfail($id);

        return view('frontend.prove2', compact('object'));
    }

    //Prove
    public function Prove()
    {
        $data = Session::get('details');

        $object = json_decode(json_encode($data));

        return view('frontend.prove', compact('object'));
    }

    //Prove-confirm
    public function Prove_confirm(Request $request)
    {
        $token = $request->token;

        $data =  investments::where('token', $token)->first();

        $data->note = $request->note;

        if ($request->image) {
            $imageName = time() . '_prove_' . $request->image->getClientOriginalExtension();
            $request->image->move('backend/uploads', $imageName);
            $data->prove_img = $imageName;
        }



        $data->save();

        Alert::success('Your payment have been submited for review');
        return redirect()->route('user_dashboard')->with('success', 'Your payment have been submited for review');
    }

    //All User Investments
    public function Investments()
    {

        $data = Investments::where('investor', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);

        return view('frontend.investments', compact('data'));
    }

    //pending test
    public function pending($id)
    {

        $data = Investments::where('investor', Auth::user()->id)->where('status', 0)->count();

        if ($data > 0) {
            $object = investments::findorfail($id);

            return view('frontend.wallet2', compact('object'));
        }
    }

    //withdraw page
    public function withdraw()
    {
        return view('frontend.withdraw');
    }

    //withdrawal funtion
    public function withdrawal(Request $request)
    {
        $data = investments::where('status', 0)->where('investor', Auth::user()->id)->first();

        if ($data) {

            Alert::error('You have a pending investment, please try again later.');
            return redirect()->back()->with('error', 'You have a pending investment, please try again later.');
        } else {

            $user = User::findorfail($request->id);

            $request->validate([
                'amount' => 'required',
                'wallet_address' => 'required',
                'currency' => 'required'
            ]);

            $expenses = $request->amount;

            $balance = $user->balance;

            if ($expenses > $balance) {
                Alert::error('Amount is greater than your balance');
                return redirect()->back()->with('error', 'Amount is greater than your balance');
            } else {

                $data = new withdrawal();

                $result = $balance - $expenses;

                $user->balance = $result;

                $data->currency = $request->currency;
                $data->amount = $request->amount;
                $data->address = $request->wallet_address;
                $data->status = 0;
                $data->user_id = Auth::user()->id;


                // $user->save();
                $data->save();

                Alert::success('Your withdrawal have been submited for review');
                return redirect()->route('withdrawal-history')->with('success', 'Your withdrawal have been submited for review');
            }
        }
    }

    //All User withdrawals
    public function withdrawals()
    {
        $data = withdrawal::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);

        return view('frontend.withdrawals', compact('data'));
    }

    public function message(Request $request)
    {

        $data = new message();

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->content = $request->content;

        $data->save();

        Alert::success('Message Sent');
        return redirect()->back()->with('success', 'Message Sent');
    }

    //All User refferals
    public function Refferals()
    {
        $data = User::where('refferred_id', Auth::user()->referral_id)->orderBy('created_at', 'desc')->paginate(10);

        $number = User::where('refferred_id', Auth::user()->referral_id)->count();

        return view('frontend.refferals', compact('data', 'number'));
    }

    
    public function Direction()
    {
        return view('frontend.pages.direction');
    }
}
