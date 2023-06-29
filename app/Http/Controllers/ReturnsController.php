<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Returns;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data['products'] = Product::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->orderBy('name')->get();
        $data['recents'] = Returns::select('product_id', 'return_no')->whereDate('created_at', Carbon::today())->where('staff_id', auth()->user()->id)->groupBy('return_no')->orderBy('created_at', 'desc')->take(4)->get();
        return view('returns.index', $data);
    }
    public function allIndex()
    {
        $data['returns'] = Returns::select('product_id','return_no')->where('business_id',auth()->user()->business_id)->where('branch_id',auth()->user()->branch_id)->groupBy('return_no')->orderBy('created_at','desc')->paginate(10);
        return view('returns.all.index', $data);
    }

    public function store(Request $request)
    {

        $total_price = 0; 
        // dd($request->all());

        $productCount = count($request->product_id);
        if ($productCount != null) {
            for ($i = 0; $i < $productCount; $i++) 
            {
                $total_price += ($request->quantity[$i] * $request->price[$i]) - $request->discount[$i];
            }
            
        }
        
        $user = auth()->user();
        $todaySales = Sale::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method)->where('product_id','!=',1012)->whereDate('created_at', today())->get();
        $todayReturns = Returns::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method)->whereDate('created_at', today())->get();
        $expenses = Expense::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method)->whereDate('created_at', today())->sum('amount');
        $payments = Payment::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('payment_method', $request->payment_method)->whereDate('created_at', today())->sum('payment_amount');

        $sales =  $todaySales->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $returns =  $todayReturns->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);

        $net_amount = ((float)$sales+(float)$payments) - (float)$returns - (float)$expenses;
       
        if((float)$total_price > (float)$net_amount)
        {
            return response()->json([
                'status' => 400,
                'message' => 'Low Balance in the Payment Channel.',
            ]);
        }

        $year = date('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $last = Returns::where('business_id', $user->business_id)->whereDate('created_at', '=', date('Y-m-d'))->latest()->first();
        if ($last == null) {
            $last_record = '1/0';
        } else {
            $last_record = $last->return_no;
        }
        $exploded = explode("/", $last_record);
        $number = $exploded[1] + 1;
        $padded = sprintf("%04d", $number);
        $stored = $year . $month . $day . '/' . $padded;

        $productCount = count($request->product_id);
        if ($productCount != null) {
            for ($i = 0; $i < $productCount; $i++) {

                $data = new Returns();
                $data->business_id = $user->business_id;
                $data->branch_id = $user->branch_id;
                $data->return_no = $stored;
                $data->product_id = $request->product_id[$i];
                $data->price = $request->price[$i];
                $data->quantity = $request->quantity[$i];
                if($request->discount[$i] == null){
                    $data->discount = 0;

                }else{
                    $data->discount = $request->discount[$i];
                }  
                $data->staff_id = auth()->user()->id;
                $data->customer_id = $request->customer_name;
                $data->note = $request->note;
                $data->payment_method = $request->payment_method;
                $data->save();

                $data = Product::find($request->product_id[$i]);
                $data->quantity += $request->quantity[$i];
                $data->update();

            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Return has been saved sucessfully',
        ]);

    }

    public function refresh(Request $request)
    {
        $data['recents'] = Returns::select('product_id','return_no')->where('business_id', auth()->user()->business_id)->whereDate('created_at', Carbon::today())->where('staff_id',auth()->user()->id)->groupBy('return_no')->orderBy('created_at','desc')->take(4)->get();
        return view('returns.recent_sales_table', $data)->render();
    }
    public function loadReceipt(Request $request)
    {
        $items = Returns::with('product')->where('business_id', auth()->user()->business_id)->where('return_no', $request->return_no)->get();
        return response()->json([
            'status' => 200,
            'items' => $items,
        ]);
    }
}
