<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Returns;
use Illuminate\Support\Str;


class SalesController extends Controller
{
   
    public function index()
    {
        $user = auth()->user();
        // $products = Product::where('branch_id', $user->branch_id)->orderBy('name')->get();
        $customers = User::select('id', 'name')->where('usertype', 'customer')->where('branch_id', $user->branch_id)->where('business_id',$user->business_id)->orderBy('name')->get();

        $latestTransactions = DB::table('sales')
            ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Sales' as type"))
            ->where('business_id',$user->business_id)
            ->where('branch_id', $user->branch_id);

        $latestTransactions->union(
            DB::table('estimates')
                ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Estimates' as type"))
                ->where('business_id',$user->business_id)
                ->where('branch_id', $user->branch_id)
        );

        $latestTransactions->union(
            DB::table('returns')
                ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Returns' as type"))
                ->where('business_id',$user->business_id)
                ->where('branch_id', $user->branch_id)
        );

        $latestTransactions = $latestTransactions
            ->orderBy('created_at', 'desc')
            ->groupBy('transaction_no')
            ->take(3)
            ->get();

        $transactionData = [];

        foreach ($latestTransactions as $transaction) {
            $table = $transaction->type == 'Sales' ? 'sales' : ($transaction->type == 'Returns' ? 'returns' : 'estimates');

            $rows = DB::table($table)
                ->where('branch_id', $user->branch_id)
                ->where('business_id',$user->business_id)
                ->where($transaction->type == 'Sales' ? 'receipt_no' : ($transaction->type == 'Returns' ? 'receipt_no' : 'receipt_no'), $transaction->transaction_no)
                ->get();

            $totalAmount = 0;
            foreach ($rows as $row) {
                $totalAmount += ($row->price * $row->quantity) - $row->discount;
            }

            // Fetch the customer information for this transaction
            $customer = null;
            if ($transaction->type == 'Sales') {
                $sale = DB::table('sales')->where('receipt_no', $transaction->transaction_no)->where('branch_id',$user->branch_id)->where('business_id',$user->business_id)->first();
                // if (!is_null($sale) && is_numeric($sale->customer)) {
                    $customer = User::find($sale->customer_id);
                // }
            }

            $transactionData[] = [
                'transaction_no' => $transaction->transaction_no,
                'type' => $transaction->type,
                'created_at' => $transaction->created_at,
                'totalAmount' => $totalAmount,
                'customer' => $customer,
            ];
        }


        $hasProducts = Product::where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->first();

        if (!$hasProducts) {

            $warningMessage = 'No products have been added for this business. <a href="' . route('products.index') . '">Add products now</a>.';

            session()->flash('warning_message', $warningMessage);
        }

        return view('transactions.index', compact('transactionData', 'customers'));
    }

    public function getProductSuggestions(Request $request)
    {
        $query = $request->input('query');
        $suggestions = Product::where('name', 'like', '%' . $query . '%')
            ->where('business_id', auth()->user()->business_id)
            ->where('branch_id', auth()->user()->branch_id)
            ->limit(20)
            ->get();

        return response()->json($suggestions);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $productIds = $request->input('product_id');
        $quantities = $request->input('quantity');
        $remainingQuantities = $request->input('remaining_quantity');
        $transaction_type = $request->input('transaction_type');

        foreach ($productIds as $key => $productId) {
            
            if (!isset($quantities[$key]) || $quantities[$key] < 0) {
                return response()->json([
                    'status' => 400,
                    'message' => "Row " . ($key + 1) . ": Quantity field is required.",
                ]);
            };

            if($transaction_type == 'sales')
            {
                if ($remainingQuantities[$key] < 1) {
                    return response()->json([
                        'status' => 400,
                        'message' => "Row " . ($key + 1) . ": The product has finished",
                    ]);
                }
                if ($quantities[$key] > $remainingQuantities[$key]) {
                    return response()->json([
                        'status' => 400,
                        'message' => "Row " . ($key + 1) . ":Only ". $remainingQuantities[$key] ." Quantity Remaining",
                    ]);
                }
            };
        }

        $transaction_id = Str::uuid();

        if ($transaction_type == "sales") {
           

            $paymentMethod = $request->input('payment_method');
            $status = null;
            $payment_amount = null;

            $totalPrice = 0;
            foreach ($request->product_id as $index => $productId) {
                $productTotal = ($request->price[$index] * $request->quantity[$index]) - ($request->discount[$index] ?? 0);
                $totalPrice += $productTotal;
            }
            if ($paymentMethod == 'deposit') {

                $deposits = Payment::select('payment_amount')->where('customer_id', $request->customer)->where('payment_type', 'deposit')->sum('payment_amount');
                if ($totalPrice > $deposits) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Deposit Balance is low. Reduce Quantity and Try again',
                    ]);
                }

                $user = User::find($request->customer);
                $user->deposit -= $totalPrice;
                $user->update();

            } elseif ($paymentMethod == 'credit') {

                if($request->paid_amount != null && $request->paid_amount > 0)
                {
                    if ($request->partial_payment_method == '') {
                        return response()->json([
                            'status' => 400,
                            'message' => 'Please choose Partial Amount Payment Channel',
                        ]);
                    }
                    $status = 'partial';
                    $payment_amount = $request->paid_amount;
                    
                    $user = User::find($request->customer);
                    $user->balance += ($totalPrice - $payment_amount);
                    $user->update();

                    $payment = new Payment();
                    $payment->payment_method = $request->partial_payment_method;
                    $payment->business_id = auth()->user()->business_id;
                    $payment->branch_id = auth()->user()->branch_id;
                    $payment->payment_amount = $request->paid_amount;
                    $payment->customer_id = $request->customer;
                    $payment->customer_balance = $user->balance;
                    $payment->receipt_nos = $transaction_id;
                    $payment->staff_id = auth()->user()->id;
                    $payment->payment_type = 'credit';
                    $payment->save();

                   


                }else
                {
                    $user = User::find($request->customer);
                    $user->balance += $totalPrice;
                    $user->update();
                }

              

            } 

            foreach ($request->product_id as $index => $productId) {
                $data = new Sale();
                $data->business_id = auth()->user()->business_id;
                $data->branch_id = auth()->user()->branch_id;
                $data->receipt_no = $transaction_id;
                $data->product_id = $productId;
                $data->price = $request->price[$index];
                $data->buying_price = $request->buying_price[$index];
                $data->quantity = $request->quantity[$index];
                $data->discount = $request->discount[$index] ?? 0;
                $data->payment_method = $paymentMethod;
                $data->staff_id = auth()->user()->id;
                $data->customer_id = $request->customer === '0' ? null : $request->customer;
                $data->note = $request->note;

                // Handle labor cost if necessary
                if ($request->input('toggleLabor')) {
                    $data->labor_cost = $request->input('labor_cost');
                }
                $data->payment_amount = $payment_amount;
                $data->status = $status;
                $data->save();

                // Update stock quantity
                $stock = Product::find($productId);
                $stock->quantity -= $request->quantity[$index];
                $stock->save();
            }

            if($paymentMethod == 'multiple')
            {
                if($request->cashAmount != null)
                {
                    $payment = new Payment();
                    $payment->business_id = auth()->user()->business_id;
                    $payment->branch_id = auth()->user()->branch_id;
                    $payment->payment_type = 'multiple';
                    $payment->payment_method = 'cash';
                    $payment->payment_amount = $request->cashAmount;
                    $payment->staff_id = auth()->user()->id;
                    $payment->customer_id = 0;
                    $payment->receipt_nos =  $transaction_id;
                    $payment->save();
                }
                if($request->posAmount != null)
                {
                    $payment = new Payment();
                    $payment->business_id = auth()->user()->business_id;
                    $payment->branch_id = auth()->user()->branch_id;
                    $payment->payment_type = 'multiple';
                    $payment->payment_method = 'pos';
                    $payment->payment_amount = $request->posAmount;
                    $payment->staff_id = auth()->user()->id;
                    $payment->customer_id = 0;
                    $payment->receipt_nos =  $transaction_id;
                    $payment->save();
                }
                if($request->transferAmount != null)
                {
                    $payment = new Payment();
                    $payment->business_id = auth()->user()->business_id;
                    $payment->branch_id = auth()->user()->branch_id;
                    $payment->payment_type = 'multiple';
                    $payment->payment_method = 'transfer';
                    $payment->payment_amount = $request->transferAmount;
                    $payment->staff_id = auth()->user()->id;
                    $payment->customer_id = 0;
                    $payment->receipt_nos =  $transaction_id;
                    $payment->save();
                }
            }

           
            return response()->json([
                'status' => 201,
                'message' => 'Sale has been recorded successfully',
            ]);
        }

        if ($transaction_type == "estimate") {
           

            $productCount = count($request->product_id);
            if ($productCount != null) {
                for ($i = 0; $i < $productCount; $i++) {

                    $data = new Estimate();
                    $data->business_id = auth()->user()->business_id;
                    $data->branch_id = auth()->user()->branch_id;
                    $data->receipt_no = $transaction_id;
                    $data->product_id = $request->product_id[$i];
                    $data->price = $request->price[$i];
                    $data->quantity = $request->quantity[$i];
                    if ($request->discount[$i] == null) {
                        $data->discount = 0;

                    } else {
                        $data->discount = $request->discount[$i];
                    }
                    $data->staff_id = auth()->user()->id;
                    $data->customer_id = $request->customer;
                    $data->note = $request->note;
                    if ($request->input('toggleLabor')) {
                        $data->labor_cost = $request->input('labor_cost');
                    }
                    $data->save();
                }
            }

            return response()->json([
                'status' => 201,
                'message' => 'Estimate has been Saved sucessfully',
            ]);
        }

        if ($transaction_type == "return") {
            $total_price = collect($request->quantity)
                ->map(function ($quantity, $index) use ($request) {
                    return ($quantity * $request->price[$index]) - $request->discount[$index];
                })
                ->sum();
                if($request->payment_method == 'credit' || $request->payment_method == 'deposit')
                {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Incorrect Payment Method Selected.',
                    ]); 
                };
    

            if (!$this->checkBalance($request->payment_method, $total_price)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Low Balance in the Payment Channel.',
                ]);
            };

            $productCount = count($request->product_id);
            if ($productCount != null) {
                for ($i = 0; $i < $productCount; $i++) {

                    $data = new Returns();
                    $data->business_id = auth()->user()->business_id;
                    $data->branch_id = auth()->user()->branch_id;
                    $data->receipt_no = $transaction_id;
                    $data->product_id = $request->product_id[$i];
                    $data->price = $request->price[$i];
                    $data->quantity = $request->quantity[$i];
                    if ($request->discount[$i] == null) {
                        $data->discount = 0;

                    } else {
                        $data->discount = $request->discount[$i];
                    }
                    $data->staff_id = auth()->user()->id;
                    $data->customer_id = $request->customer;
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

    }

    private function checkBalance($paymentMethod, $totalPrice)
    {
        $user = auth()->user();

        $todaySales = Sale::where('branch_id', $user->branch_id)
            ->where('business_id',$user->business_id)
            ->where('payment_method', $paymentMethod)
            ->whereNotIn('product_id', [1093, 1012])
            ->whereDate('created_at', today())
            ->get();

        $todayReturns = Returns::where('branch_id', $user->branch_id)
            ->where('business_id',$user->business_id)
            ->where('payment_method', $paymentMethod)
            ->whereDate('created_at', today())
            ->get();

        $expenses = Expense::where('branch_id', $user->branch_id)
            ->where('business_id',$user->business_id)
            ->where('payment_method', $paymentMethod)
            ->whereDate('created_at', today())
            ->sum('amount');

        $creditRepayments = Payment::where('branch_id', $user->branch_id)
            ->where('business_id',$user->business_id)
            ->where('payment_method', $paymentMethod)
            ->where('payment_type', 'credit')
            ->whereDate('created_at', today())
            ->sum('payment_amount');

        $deposits = Payment::where('branch_id', $user->branch_id)
            ->where('business_id',$user->business_id)
            ->where('payment_method', $paymentMethod)
            ->where('payment_type', 'deposit')
            ->whereDate('created_at', today())
            ->sum('payment_amount');

       

        $totalSales = $todaySales->sum(function ($sale) {
            return ($sale->price * $sale->quantity) - $sale->discount;
        });

        $totalReturns = $todayReturns->sum(function ($return) {
            return ($return->price * $return->quantity) - $return->discount;
        });

        $netAmount = $totalSales + $deposits + $creditRepayments - ($totalReturns + $expenses);

       

        return ($totalPrice <= $netAmount);
    }
   
    public function fetchBalanceOrDeposit(Request $request)
    {
        $userId = $request->input('user_id');
        $paymentMethod = $request->input('payment_method');

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $balanceOrDeposit = 0;

        if ($paymentMethod === 'credit') {
            $balanceOrDeposit = $user->balance;
        } elseif ($paymentMethod === 'deposit') {
            $balanceOrDeposit = $user->deposit;
        }

        return response()->json(['balance_or_deposit' => $balanceOrDeposit], 200);
    }

    public function refresh(Request $request)
    {
        $user = auth()->user();

        $latestTransactions = DB::table('sales')
            ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Sales' as type"))
            ->where('business_id',$user->business_id)
            ->where('branch_id', $user->branch_id);
            

        $latestTransactions->union(
            DB::table('estimates')
                ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Estimates' as type"))
                ->where('business_id',$user->business_id)
                ->where('branch_id', $user->branch_id)
        );

        $latestTransactions->union(
            DB::table('returns')
                ->select('receipt_no as transaction_no', 'created_at', DB::raw("'Returns' as type"))
                ->where('business_id',$user->business_id)
                ->where('branch_id', $user->branch_id)
        );

        $latestTransactions = $latestTransactions
            ->orderBy('created_at', 'desc')
            ->groupBy('transaction_no')
            ->take(3)
            ->get();

        $transactionData = [];

        foreach ($latestTransactions as $transaction) {
            $table = $transaction->type == 'Sales' ? 'sales' : ($transaction->type == 'Returns' ? 'returns' : 'estimates');

            $rows = DB::table($table)
                ->where('business_id',$user->business_id)
                ->where('branch_id', $user->branch_id)
                ->where($transaction->type == 'Sales' ? 'receipt_no' : ($transaction->type == 'Returns' ? 'receipt_no' : 'receipt_no'), $transaction->transaction_no)
                ->get();

            $totalAmount = 0;
            foreach ($rows as $row) {
                $totalAmount += ($row->price * $row->quantity) - $row->discount;
            }

            $customer = null;
            if ($transaction->type == 'Sales') {
                $sale = DB::table('sales')->where('business_id',$user->business_id)->where('branch_id',$user->branch_id)->where('receipt_no', $transaction->transaction_no)->first();
                if (!is_null($sale) && is_numeric($sale->customer_id)) {
                    $customer = User::find($sale->customer_id);
                }
            }

            $transactionData[] = [
                'transaction_no' => $transaction->transaction_no,
                'type' => $transaction->type,
                'created_at' => $transaction->created_at,
                'totalAmount' => $totalAmount,
                'customer' => $customer,
            ];
        }

        return view('transactions.recent_transactions_table', compact('transactionData'))->render();
    }
    
    public function loadReceipt(Request $request)
    {
        $user = auth()->user();
        $transactionType = $request->transaction_type;
        $transactionNo = $request->receipt_no;
        $items = [];

        if ($transactionType === 'Sales') {
            $items = Sale::with('product','staff:name,id')
                ->where('receipt_no', $transactionNo)
                ->where('business_id',$user->business_id)
                ->where('branch_id',$user->branch_id)
                ->get();

        } elseif ($transactionType === 'Returns') {
            $items = Returns::with('product','staff:name,id')
                ->where('receipt_no', $transactionNo)
                ->where('business_id',$user->business_id)
                ->where('branch_id',$user->branch_id)
                ->get();
        } elseif ($transactionType === 'Estimates') {
            $items = Estimate::with('product','staff:name,id')
                ->where('receipt_no', $transactionNo)
                ->where('business_id',$user->business_id)
                ->where('branch_id',$user->branch_id)
                ->get();
        }
      

        $customer_name = '';

        $customer_id = $items[0]->customer_id;
        if($customer_id == null || $customer_id == 0)
        {
            $customer_name = 'Walk-in Customer';
        }else
        {
            $customer = User::select('name')->where('id',$customer_id)->first();
            $customer_name = $customer->name;
        }
      
        return response()->json([
            'status' => 200,
            'items' => $items,
            'customer_name' => $customer_name,
        ]);

    }

    public function allIndex()
    {
        $data['sales'] = Sale::select('product_id', 'receipt_no')->where('business_id', auth()->user()->business_id)->where('branch_id', auth()->user()->branch_id)->groupBy('receipt_no')->orderBy('created_at', 'desc')->paginate(10);
        $data['staffs'] = User::whereIn('usertype', ['admin', 'cashier'])->where('business_id', auth()->user()->business_id)->where('branch_id', auth()->user()->branch_id)->get();

        return view('sales.all_index', $data);
    }

    public function allSearch(Request $request)
    {
        $query = $request->input('query');

        $data['sales'] = Sale::select('product_id', 'receipt_no')
            ->where('branch_id', auth()->user()->branch_id)
            ->where('business_id', auth()->user()->business_id)
            ->where('receipt_no', 'LIKE', '%' . $query . '%')
            ->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return view('sales.all_table', $data)->render();

    }

    public function filterSales(Request $request)
    {
        $staffId = $request->input('staff_id');
        $transactionType = $request->input('transaction_type');

        $query = Sale::select('product_id', 'receipt_no')
            ->where('branch_id', auth()->user()->branch_id)
            ->where('business_id', auth()->user()->business_id);

        if ($staffId && $staffId != 'all') {
            $query->where('staff_id', $staffId);
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

    public function markAwaitingPickup(Request $request)
    {
        $receiptNo = $request->receiptNo;

        $sales = Sale::where('receipt_no', $receiptNo)->where('business_id', auth()->user()->business_id)->get()->where('branch_id', auth()->user()->branch_id)
        ;

        foreach ($sales as $sale) {
            $sale->collected = 0;
            $sale->save();

            $stock = Product::find($sale->product_id);
            $stock->pending_pickups += $sale->quantity;
            $stock->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Items marked as awaiting pickup successfully.',
        ]);
    }

    public function markDeliver(Request $request)
    {
        $receiptNo = $request->receiptNo;

        $sales = Sale::where('receipt_no', $receiptNo)->where('business_id', auth()->user()->business_id)
            ->where('branch_id', auth()->user()->branch_id)
            ->where('collected', 0)
            ->get();

        foreach ($sales as $sale) {
            $sale->collected = 1;
            $sale->update();

            $stock = Product::find($sale->product_id);
            $stock->pending_pickups -= $sale->quantity;
            $stock->update();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Sales marked as delivered successfully',
        ]);
    }

}
