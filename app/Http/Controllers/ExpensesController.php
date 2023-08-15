<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Returns;
use App\Models\Sale;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $data['expense_cats'] = ExpenseCategory::all();
        if($user->usertype == 'admin')
        {
            $data['dates'] = Expense::select('date')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->distinct('date')->orderBy('date','desc')->paginate(15);
        }
        else
        {
            $data['dates'] = Expense::select('date')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('staff_id',$user->id)->distinct('date')->orderBy('date','desc')->paginate(15);
        }
        return view('expense.index',$data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $dataCount = count($request->expense_category_id);
        if($dataCount != NULL){
            for ($i=0; $i < $dataCount; $i++){

                $todaySales = Sale::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method[$i])->where('product_id','!=',1012)->whereDate('created_at', today())->get();
                $todayReturns = Returns::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method[$i])->whereDate('created_at', today())->get();
                $expenses = Expense::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method[$i])->whereDate('created_at', today())->sum('amount');
                $payments = Payment::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method[$i])->whereDate('created_at', today())->sum('payment_amount');
        
                $sales =  $todaySales->reduce(function ($total, $sale) {
                    $total += ($sale->price * $sale->quantity) - $sale->discount;
                    return $total;
                }, 0);
                $returns =  $todayReturns->reduce(function ($total, $return) {
                    $total += ($return->price * $return->quantity) - $return->discount;
                    return $total;
                }, 0);
        
                $net_amount = ((float)$sales+(float)$payments) - (float)$returns - (float)$expenses;
               
                if((float)$request->amount[$i] > (float)$net_amount)
                {
                    Toastr::error('Low Balance in the Payment Channel.');
                    return redirect()->route('expense.index');
                }

                $data = new Expense();
                $data->business_id = $user->business_id;
                $data->branch_id = $user->branch_id;
                $data->expense_category_id = $request->expense_category_id[$i];
                $data->amount = $request->amount[$i];
                $data->description = $request->description[$i];
                $data->payment_method = $request->payment_method[$i];
                $data->payee_id = $user->id;
                $data->date = $request->date;
                $data->save();
            }
        }

        Toastr::success('Expenses Recorded sucessfully', 'Done');
        return redirect()->route('expense.index');
    }
}
