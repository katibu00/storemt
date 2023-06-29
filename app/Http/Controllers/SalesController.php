<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data['products'] = Product::where('business_id', $user->business_id)
            ->where('branch_id', $user->branch_id)
            ->orderBy('name')->get();
        $data['recents'] = Sale::select('product_id', 'receipt_no')
            ->whereDate('created_at', Carbon::today())
            ->where('staff_id', $user->id)
            ->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
        $data['sold_items'] = [];
        return view('sales.index', $data);

    }
    public function creditIndex()
    {
        $user = auth()->user();
        $data['products'] = Product::where('business_id', $user->business_id)
            ->where('branch_id', $user->branch_id)
            ->orderBy('name')->get();

        $data['recents'] = Sale::select('product_id', 'receipt_no', 'customer_id')
            ->whereDate('created_at', Carbon::today())
            ->where('staff_id', auth()->user()->id)
            ->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
        $data['sold_items'] = [];
        $data['customers'] = User::select('id', 'name')
            ->where('usertype', 'customer')
            ->where('business_id', $user->business_id)
            ->where('branch_id', $user->branch_id)
            ->orderBy('name')->get();

        return view('sales.credit.index', $data);

    }
    public function fetchBalance(Request $request)
    {

        $user = User::select('balance')->where('id', $request->customer_id)->first();
        $deposits = Payment::select('payment_amount')
            ->where('customer_id', $request->customer_id)
            ->where('payment_type', 'deposit')
            ->sum('payment_amount');
        if ($user) {
            return response()->json([
                'status' => 200,
                'balance' => $user->balance,
                'deposits' => $deposits,
            ]);
        } else {
            return response()->json([
                'status' => 404,
            ]);
        }

    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $year = date('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $last = Sale::where('business_id', $user->business_id)->whereDate('created_at', '=', date('Y-m-d'))->latest()->first();
        if ($last == null) {
            $last_record = '1/0';
        } else {
            $last_record = $last->receipt_no;
        }
        $exploded = explode("/", $last_record);
        $number = $exploded[1] + 1;
        $padded = sprintf("%04d", $number);
        $stored = $year . $month . $day . '/' . $padded;
        $productCount = count($request->product_id);
        if ($productCount != null) {
            for ($i = 0; $i < $productCount; $i++) {

                $data = new Sale();
                $data->business_id = $user->business_id;
                $data->branch_id = $user->branch_id;
                $data->receipt_no = $stored;
                $data->product_id = $request->product_id[$i];
                $data->price = $request->price[$i];
                $data->quantity = $request->quantity[$i];
                if ($request->discount[$i] == null) {
                    $data->discount = 0;

                } else {
                    $data->discount = $request->discount[$i];
                }
                $data->payment_method = $request->payment_method;
                $data->staff_id = auth()->user()->id;
                $data->customer_id = $request->customer_name;
                $data->note = $request->note;

                $data->save();

                $data = Product::find($request->product_id[$i]);
                $data->quantity -= $request->quantity[$i];
                $data->update();

            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Sale has been recorded sucessfully',
        ]);

    }
    public function creditStore(Request $request)
    {
        if ($request->payment_method == 'deposit') {

            $total_price = 0;
            $productCount = count($request->product_id);
            if ($productCount != null) {
                for ($i = 0; $i < $productCount; $i++) {
                    $total_price += ($request->price[$i] * $request->quantity[$i]) - $request->discount[$i];
                }
            }
            $deposits = Payment::select('payment_amount')->where('customer_id', $request->customer_id)->where('payment_type', 'deposit')->sum('payment_amount');
            if ($total_price > $deposits) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Deposit Balance is low. Reduce Quantity and Try again',
                ]);
            }

            $year = date('Y');
            $month = Carbon::now()->format('m');
            $day = Carbon::now()->format('d');
            $last = Sale::where('business_id', auth()->user()->business_id)->whereDate('created_at', '=', date('Y-m-d'))->latest()->first();
            if ($last == null) {
                $last_record = '1/0';
            } else {
                $last_record = $last->receipt_no;
            }
            $exploded = explode("/", $last_record);
            $number = $exploded[1] + 1;
            $padded = sprintf("%04d", $number);
            $stored = $year . $month . $day . '/' . $padded;
            $total_price = 0;
            $discount = 0;
            $productCount = count($request->product_id);
            if ($productCount != null) {
                for ($i = 0; $i < $productCount; $i++) {

                    $data = new Sale();
                    $data->business_id = auth()->user()->business_id;
                    $data->branch_id = auth()->user()->branch_id;
                    $data->receipt_no = $stored;
                    $data->product_id = $request->product_id[$i];
                    $data->price = $request->price[$i];
                    $data->quantity = $request->quantity[$i];
                    if ($request->discount[$i] == null) {
                        $discount = 0;

                    } else {
                        $discount = $request->discount[$i];
                    }
                    $data->discount = $discount;
                    $data->payment_method = 'deposit';
                    $data->staff_id = auth()->user()->id;
                    $data->customer_id = $request->customer_id;
                    $data->note = $request->note;
                    $data->save();

                    $data = Product::find($request->product_id[$i]);
                    $data->quantity -= $request->quantity[$i];
                    $data->update();

                    $total_price += ($request->price[$i] * $request->quantity[$i]) - $discount;

                }
            }

            $customer_id = request()->input('customer_id');
            Payment::where('customer_id', $customer_id)->update(['payment_type' => 'used']);
            if ($total_price - $deposits != 0) {
                $balance = Payment::where('customer_id', $customer_id)->where('payment_type', 'used')->latest()->first();
                $balance->payment_type = 'deposit';
                $balance->payment_amount = $deposits - $total_price;
                $balance->update();
            }

            return response()->json([
                'status' => 201,
                'message' => 'Deposit Sale has been recorded sucessfully',
            ]);

        } else {

            $year = date('Y');
            $month = Carbon::now()->format('m');
            $day = Carbon::now()->format('d');
            $last = Sale::where('business_id', auth()->user()->business_id)->whereDate('created_at', '=', date('Y-m-d'))->latest()->first();
            if ($last == null) {
                $last_record = '1/0';
            } else {
                $last_record = $last->receipt_no;
            }
            $exploded = explode("/", $last_record);
            $number = $exploded[1] + 1;
            $padded = sprintf("%04d", $number);
            $stored = $year . $month . $day . '/' . $padded;
            $total_price = 0;
            $discount = 0;
            $productCount = count($request->product_id);
            if ($productCount != null) {
                for ($i = 0; $i < $productCount; $i++) {

                    $data = new Sale();
                    $data->business_id = auth()->user()->business_id;
                    $data->branch_id = auth()->user()->branch_id;
                    $data->receipt_no = $stored;
                    $data->product_id = $request->product_id[$i];
                    $data->price = $request->price[$i];
                    $data->quantity = $request->quantity[$i];
                    if ($request->discount[$i] == null) {
                        $discount = 0;

                    } else {
                        $discount = $request->discount[$i];
                    }
                    $data->discount = $discount;
                    $data->payment_method = 'credit';
                    $data->staff_id = auth()->user()->id;
                    $data->customer_id = $request->customer_id;
                    $data->note = $request->note;
                    $data->save();

                    $data = Product::find($request->product_id[$i]);
                    $data->quantity -= $request->quantity[$i];
                    $data->update();

                    $total_price += ($request->price[$i] * $request->quantity[$i]) - $discount;

                }
            }

            $user = User::find($request->customer_id);
            $user->balance += $total_price;
            $user->update();

            return response()->json([
                'status' => 201,
                'message' => 'Credit Sale has been recorded sucessfully',
            ]);
        }

    }

    public function refresh(Request $request)
    {
        $data['recents'] = Sale::select('product_id', 'receipt_no')->where('business_id', auth()->user()->business_id)->whereDate('created_at', Carbon::today())->where('staff_id', auth()->user()->id)->groupBy('receipt_no')->orderBy('created_at', 'desc')->take(4)->get();
        return view('sales.recent_sales_table', $data)->render();
    }
    public function loadReceipt(Request $request)
    {
        $items = Sale::with('product')->where('business_id', auth()->user()->business_id)->where('receipt_no', $request->receipt_no)->get();
        return response()->json([
            'status' => 200,
            'items' => $items,
        ]);
    }

    public function allIndex()
    {
        $data['sales'] = Sale::select('product_id', 'receipt_no')->where('branch_id', auth()->user()->branch_id)->groupBy('receipt_no')->orderBy('created_at', 'desc')->paginate(10);
        $data['staffs'] = User::whereIn('usertype', ['admin', 'cashier'])->where('branch_id', auth()->user()->branch_id)->get();

        return view('sales.all_index', $data);
    }

    public function allSearch(Request $request)
    {
        $query = $request->input('query');

        // Perform the search query on the Sale model
        $data['sales'] = Sale::select('product_id', 'receipt_no')
            ->where('branch_id', auth()->user()->branch_id)
            ->where('receipt_no', 'LIKE', '%' . $query . '%')
            ->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        // // Return the search results as JSON
        // return response()->json($sales);

        return view('sales.all_table', $data)->render();

    }

    public function filterSales(Request $request)
    {
        $cashierId = $request->input('cashier_id');
        $transactionType = $request->input('transaction_type');

        $query = Sale::select('product_id', 'receipt_no')
            ->where('branch_id', auth()->user()->branch_id);

        if ($cashierId && $cashierId != 'all') {
            $query->where('staff_id', $cashierId);
        }

        if ($transactionType && $transactionType != 'all') {
            $query->where('payment_method', $transactionType);
        }

        $data['sales'] = $query->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return view('sales.all_table', $data)->render();
    }

}
