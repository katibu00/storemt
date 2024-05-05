<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;

        $data['users'] = User::where('business_id', $businessId)
            ->whereNotIn('usertype', ['customer', 'supplier'])
            ->get();
        $data['branches'] = Branch::where('business_id', $businessId)->get();
        return view('users.index', $data);
    }

    public function customersIndex()
    {
        $branchId = auth()->user()->branch_id;
        $businessId = auth()->user()->business_id;

        if (auth()->user()->usertype == 'cashier') {
            if (auth()->user()->business->manage_customers == false) {
                Toastr::error('You do not have persmission', 'Denied');
                return redirect()->route('cashier.home');
            }
        }

        $data['customers'] = User::where('usertype', 'customer')
            ->where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->orderBy('name')->get();
        return view('users.customers.index', $data);
    }

    public function customerStore(Request $request)
    {
        $request->validate([
            'name.*' => 'required|string',
            'phone.*' => 'required|unique:users,phone',
            'pre_balance.*' => 'nullable|numeric',
        ]);

        $businessId = auth()->user()->business_id;
        $branchId = auth()->user()->branch_id;

        $customers = $request->input('name');

        foreach ($customers as $key => $customerName) {
            $customer = new User();
            $customer->business_id = $businessId;
            $customer->branch_id = $branchId;
            $customer->name = $customerName;
            $customer->phone = $request->input('phone')[$key];
            $customer->balance = 0;
            $customer->pre_balance = $request->input('pre_balance')[$key] ?? 0;
            $customer->usertype = 'customer';
            $customer->password = Hash::make(12345678);
            $customer->save();
        }

        $message = 'Customer(s) have been created successfully';

        Toastr::success($message);

        return redirect()->route('customers.index')->with('success', $message);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|max: 20|unique:users,phone',
            'branch_id' => 'nullable|exists:branches,id', 
            'position' => 'required|in:admin,cashier,clerk,security',
            'password' => 'required|string|min:6',
        ]);


        if (auth()->user()->business->has_branches == 1) {
            $branch_id = $request->branch_id;
        } else {
            $branch_id = auth()->user()->branch_id;
        }
        $user = new User();
        $user->business_id = auth()->user()->business_id;
        $user->branch_id = $branch_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->usertype = $request->position;
        $user->password = Hash::make($request->password);
        $user->save();
        Toastr::success('User has been created sucessfully', 'Done');
        return redirect()->route('users.index');
    }

    public function delete(Request $request)
    {
        $user = User::find($request->id);
        if ($user->id == Auth::user()->id) {
            Toastr::error('You cannot delete yourself', 'Warning');
            return redirect()->route('users.index');
        }
        $user->delete();
        Toastr::success('User has been deleted sucessfully', 'Done');
        return redirect()->route('users.index');
    }

    public function edit($id)
    {
        $businessId = auth()->user()->business_id;
        $data['branches'] = Branch::where('business_id', $businessId)->get();
        $data['user'] = User::find($id);
        return view('users.edit', $data);
    }

    public function customerProfile($id)
    {
        $data['user'] = User::select('id', 'name', 'balance', 'deposit', 'pre_balance')->where('id', $id)->first();
        $data['dates'] = Sale::select('product_id', 'receipt_no', 'created_at', 'status')
            ->where('business_id', auth()->user()->business_id)
            ->where('branch_id', auth()->user()->branch_id)
            ->where('payment_method', 'credit')
            ->where(function ($query) use ($id) {
                $query->where('status', '!=', 'paid')
                    ->orWhereNull('status');
            })
            ->where('customer_id', $id)
            ->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->get();
        // dd($data['dat÷es']);
        $data['payments'] = Payment::select('id', 'payment_amount', 'payment_method', 'created_at')->where('business_id', auth()->user()->business_id)->where('branch_id', auth()->user()->branch_id)->where('payment_type', 'credit')->where('customer_id', $id)->orderBy('created_at', 'desc')->get();

        $data['shoppingHistory'] = Sale::where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->toDateString();
            });

        return view('users.customers.profile', $data);
    }

    public function editCustomer($id)
    {
        $data['user'] = User::find($id);
        return view('users.customers.edit', $data);
    }

    public function updateCustomer(Request $request, $id)
    {
        $user = User::find($id);
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->balance = $request->credit;
        $user->pre_balance = $request->pre_balance;
        $user->deposit = $request->deposit;

        $user->update();
        Toastr::success('Customer has been updated sucessfully', 'Done');
        return redirect()->route('customers.index');
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->business->has_branches == 1) {
            $branch_id = $request->branch_id;
        } else {
            $branch_id = auth()->user()->branch_id;
        }

        $user = User::find($id);
        $user->branch_id = $branch_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->usertype = $request->position;
        $user->password = Hash::make($request->password);
        $user->update();
        Toastr::success('User has been updated sucessfully', 'Done');
        return redirect()->route('users.index');
    }

    public function savePayment(Request $request)
    {
        $customer = User::find($request->customer_id);
        $receipt_nos = [];
        $total_amount_paid = 0;
        $businessId = auth()->user()->business_id;
        $branchId = auth()->user()->branch_id;

        if ($request->receipt_no == null) {
            Toastr::error("No Transaction selected");
            return redirect()->back();
        }

        $rowCount = count($request->receipt_no);
        if ($rowCount != null) {
            for ($i = 0; $i < $rowCount; $i++) {

                if ($request->payment_option[$i] == "Full Payment") {

                    if ($request->payment_method == 'deposit') {
                        if ($customer->deposit < $request->full_payment_payable[$i]) {
                            Toastr::error("Customer has no enough deposit balance");
                            return redirect()->back();
                        }
                    }

                    $receiptNo = $request->receipt_no[$i];
                    $sales = DB::table('sales')
                        ->where('receipt_no', $receiptNo)
                        ->where('business_id', $businessId)
                        ->where('branch_id', $branchId)
                        ->get();
                    $total_amount = 0;
                    if ($sales[0]->status) {
                        foreach ($sales as $sale) {
                            $total_amount += $sale->price * $sale->quantity - $sale->discount;
                        }
                        DB::table('sales')
                            ->where('receipt_no', '=', $request->receipt_no[$i])
                            ->where('business_id', $businessId)
                            ->where('branch_id', $branchId)
                            ->update(['status' => 'paid']);

                        if ($request->payment_method == 'deposit') {
                            $customer->deposit -= $request->full_payment_payable[$i];
                            $customer->balance -= $request->full_payment_payable[$i];
                            $customer->update();
                        } else {
                            $customer->balance -= $request->full_payment_payable[$i];
                            $customer->update();
                        }
                        array_push($receipt_nos, $receiptNo);
                        $total_amount_paid += $request->full_payment_payable[$i];

                    } else {
                        DB::table('sales')
                            ->where('receipt_no', '=', $request->receipt_no[$i])
                            ->where('business_id', $businessId)
                            ->where('branch_id', $branchId)
                            ->update(['status' => 'paid']);

                        array_push($receipt_nos, $request->receipt_no[$i]);
                        $total_amount_paid += $request->full_payment_payable[$i];

                        if ($request->payment_method == 'deposit') {
                            $customer->deposit -= $request->full_payment_payable[$i];
                            $customer->balance -= $request->full_payment_payable[$i];
                            $customer->update();
                        } else {
                            $customer->balance -= $request->full_payment_payable[$i];
                            $customer->update();
                        }
                    }

                }
                if ($request->payment_option[$i] == "Partial Payment") {

                    if ($request->payment_method == 'deposit') {
                        if ($customer->deposit < $request->partial_amount[$i]) {
                            Toastr::error("Customer has no enough deposit balance");
                            return redirect()->back();
                        }
                    }

                    try {
                        DB::beginTransaction();

                        $receiptNo = $request->receipt_no[$i];
                        $partialAmount = $request->partial_amount[$i];

                        $sales = DB::table('sales')
                            ->where('receipt_no', $receiptNo)
                            ->where('business_id', $businessId)
                            ->where('branch_id', $branchId)
                            ->get();

                        if ($sales->count() < 1) {
                            Toastr::error("Sale not found for receipt no: $receiptNo");
                            return redirect()->back();
                        }

                        $total_amount = 0;
                        foreach ($sales as $sale) {
                            $total_amount += $sale->quantity * $sale->price - $sale->discount;
                        }

                        $newPaymentAmount = $sales[0]->payment_amount + $partialAmount;

                        if ($newPaymentAmount > $total_amount) {
                            Toastr::error('Amount is greater than the total for the Receipt No: ' . $receiptNo, 'Amount Exceeded');
                            return redirect()->back();
                        }

                        DB::table('sales')
                            ->where('receipt_no', $receiptNo)
                            ->where('business_id', $businessId)
                            ->where('branch_id', $branchId)
                            ->update([
                                'status' => 'partial',
                                'payment_amount' => $newPaymentAmount,
                            ]);

                        DB::commit();

                        if ($request->payment_method == 'deposit') {
                            $customer->deposit -= $request->partial_amount[$i];
                            $customer->balance -= $request->partial_amount[$i];
                            $customer->update();
                        } else {
                            $customer->balance -= $request->partial_amount[$i];
                            $customer->update();
                        }

                        array_push($receipt_nos, $request->receipt_no[$i]);
                        $total_amount_paid += $request->partial_amount[$i];

                    } catch (Exception $e) {
                        DB::rollback();

                    }
                }
            }
        }
        $customer->update();

        if ($total_amount_paid != 0) {
            $record = new Payment();
            $record->payment_method = $request->payment_method;
            $record->business_id = auth()->user()->business_id;
            $record->payment_amount += $total_amount_paid;
            $record->branch_id = auth()->user()->branch_id;
            $record->customer_id = $request->customer_id;
            $record->customer_balance = $customer->balance;
            $record->receipt_nos = implode(',', $receipt_nos);
            $record->staff_id = auth()->user()->id;
            $record->payment_type = 'credit';
            $record->save();

            Toastr::success('Payment has been Recorded sucessfully', 'Done');
            return redirect()->back();

        }

        Toastr::warning('Sales amount is zero. Nothing Recorded', 'Not Recorded');
        return redirect()->back();

    }

    public function saveDeposit(Request $request)
    {
        $record = new Payment();
        $record->business_id = auth()->user()->business_id;
        $record->branch_id = auth()->user()->branch_id;
        $record->payment_method = $request->payment_method;
        $record->payment_amount = $request->amount;
        $record->customer_id = $request->customer_id;
        $record->staff_id = auth()->user()->id;
        $record->payment_type = 'deposit';
        $record->save();

        $customer = User::find($request->customer_id);
        $customer->deposit += $request->amount;
        $customer->save();

        Toastr::success('Deposit has been Recorded sucessfully', 'Done');
        return redirect()->back();

    }

    public function savePreBalance(Request $request)
    {
        $user = User::find($request->customer_id);

        if ($user->pre_balance < 1) {
            Toastr::error('User Previous Balance Record is less than 1', 'Warning');
            return redirect()->back();
        }
        if ($request->paymentAmount > $user->pre_balance) {
            Toastr::error('Previous Balance Payment cannot exceed the user balance', 'Warning');
            return redirect()->back();
        }
        $record = new Payment();
        $record->business_id = auth()->user()->business_id;
        $record->branch_id = auth()->user()->branch_id;
        $record->payment_method = $request->paymentMethod;
        $record->payment_amount += $request->paymentAmount;
        $record->note = $request->paymentDescription;
        $record->customer_id = $request->customer_id;
        $record->staff_id = auth()->user()->id;
        $record->payment_type = 'pre_bal';
        $record->save();

        $user->pre_balance -= $request->paymentAmount;
        $user->save();

        Toastr::success('Previous Balance Payment has been Recorded sucessfully', 'Done');
        return redirect()->back();

    }

    public function updateDeposit(Request $request)
    {
        $deposit = Payment::findOrFail($request->depositId);

        $validatedData = $request->validate([
            'payment_amount' => 'required|numeric',
        ]);

        $customer = User::find($request->customer_id);
        $customer->deposit -= $deposit->payment_amount;
        $customer->save();

        $deposit->payment_amount = $validatedData['payment_amount'];
        $deposit->save();

        $customer->deposit += $validatedData['payment_amount'];
        $customer->save();

        return response()->json(['message' => 'Deposit updated successfully']);
    }

   

    public function loadReceipt(Request $request)
{
    $payment = Payment::find($request->payment_id);
    $receiptNos = explode(',', $payment->receipt_nos);

    $formattedDates = [];

    foreach ($receiptNos as $receiptNo) {
        $sale = Sale::where('receipt_no', $receiptNo)->first();
        if ($sale) {
            $formattedDate = date('d F Y', strtotime($sale->created_at));
            $formattedDates[] = $formattedDate;
        }
    }

    return response()->json([
        'status' => 200,
        'payment' => $payment,
        'dates' => $formattedDates,
        'balance' => $payment->customer_balance ?? 0,
    ]);
}

    public function deleteCustomer(Request $request)
    {
        $user = User::find($request->id);
        $user->delete();
        Payment::where('customer_id', $request->id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Customer deleted succesfully',
        ]);
    }

    public function returnIndex(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $branchId = auth()->user()->branch_id;

        $id = $request->input('id');
        $sale = Sale::where('receipt_no', $id)
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->first();

        $data['sales'] = Sale::select('id', 'product_id', 'price', 'quantity', 'discount', 'status', 'payment_amount', 'customer_id', 'returned_qty')
            ->where('receipt_no', $id)
            ->where('business_id', auth()->user()->business_id)
            ->where('branch_id', $branchId)
            ->where('customer_id', $sale->customer_id)
            ->get();

        return view('users.customers.return', $data);

    }

    public function returnStore(Request $request)
    {
        $fistRow = Sale::select('receipt_no', 'customer_id')->where('id', $request->sale_id[0])->first();

        $businessId = auth()->user()->business_id;
        $branchId = auth()->user()->branch_id;

        $sales = Sale::where('receipt_no', $fistRow->receipt_no)
            ->where('customer_id', $fistRow->customer_id)
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->get();

        $net_amount = 0;

        foreach ($sales as $sale) {
            $net_amount += ($sale->quantity - $sale->returned_qty) * $sale->price - $sale->discount;

        }
        $remaining_balance = $net_amount - ($sales[0]->payment_amount ?? 0);

        $returned_amount = 0;
        $saleIdCount = count($request->sale_id);
        if ($saleIdCount != null) {
            for ($i = 0; $i < $saleIdCount; $i++) {
                $sale = Sale::select('returned_qty', 'quantity', 'price', 'discount')->where('id', $request->sale_id[$i])->first();
                $returned_amount += ($sale->price * $sale->quantity - $sale->discount) * $sale->returned_qty;
            }
        }

        if ($returned_amount > $remaining_balance) {
            Toastr::error('Returned Amount Cannot Exceed Remaining Balance');
            return redirect()->back();

        }

        $productCount = count($request->sale_id);
        if ($productCount != null) {
            for ($i = 0; $i < $productCount; $i++) {

                $sale = Sale::select('id', 'returned_qty', 'quantity')->where('id', $request->sale_id[$i])->first();

                if ($request->returned_qty[$i] != '') {

                    if ($request->returned_qty[$i] <= $sale->quantity) {

                        $sale->returned_qty += $request->returned_qty[$i];
                        $sale->update();

                        $data = new Returns();
                        $data->branch_id = auth()->user()->branch_id;
                        $data->business_id = auth()->user()->business_id;
                        $data->receipt_no = 'R' . $fistRow->receipt_no;
                        $data->product_id = $request->product_id[$i];
                        $data->price = $request->price[$i];
                        $data->quantity = $request->returned_qty[$i];
                        if ($request->discount[$i] == null) {
                            $data->discount = 0;

                        } else {
                            $discount = $request->discount[$i] / $request->quantity[$i] * $request->returned_qty[$i];
                            $data->discount = $discount;
                        }
                        $data->staff_id = auth()->user()->id;
                        $data->customer_id = $sale->customer_id;
                        $data->channel = 'credit';
                        $data->note = $sale->note;
                        $data->save();

                        $data = Product::find($request->product_id[$i]);
                        $data->quantity += $request->returned_qty[$i];
                        $data->update();

                        $user = User::find($request->customer_id);
                        if ($request->discount[$i] == null) {
                            $user->balance -= $request->price[$i] * $request->returned_qty[$i];
                        } else {
                            $user->balance -= $request->price[$i] * $request->returned_qty[$i] - $discount;

                        }
                        $user->update();
                    }
                }
            }
        }
        Toastr::success('Credit Sales was Updated Successfully');
        return redirect()->route('customers.profile', $fistRow->customer_id);

    }

    public function search(Request $request)
    {
        $searchQuery = $request->input('query');

        $data['customers'] = User::where('usertype', 'customer')
            ->where('business_id', auth()->user()->business_id)
            ->where('branch_id', auth()->user()->branch_id)
            ->where(function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', '%' . $searchQuery . '%');
            })
            ->orderBy('name')
            ->get();

        return view('users.customers.table', $data)->render();

    }

}
