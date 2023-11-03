<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EstimateController extends Controller
{
   
    public function allIndex()
    {
        $user = auth()->user();
        $data['estimates'] = Estimate::select('product_id', 'receipt_no')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->groupBy('receipt_no')->orderBy('created_at', 'desc')->paginate(10);
        $data['customers'] = User::select('id', 'name')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('usertype', 'customer')->orderBy('name')->get();
        $data['staffs'] = User::whereIn('usertype', ['admin', 'cashier'])->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->get();

        return view('estimate.all_index', $data);
    }

  
    public function loadReceipt(Request $request)
    {
        $items = Estimate::with('product','staff')->where('business_id', auth()->user()->business_id)->where('receipt_no', $request->receipt_no)->get();
        return response()->json([
            'status' => 200,
            'items' => $items,
        ]);
    }

    public function allStore(Request $request)
    {
        $estimates = Estimate::where('receipt_no', $request->receipt_no)->get();

        $year = date('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $last = Sale::whereDate('created_at', '=', date('Y-m-d'))->latest()->first();
        if ($last == null) {
            $last_record = '1/0';
        } else {
            $last_record = $last->receipt_no;
        }
        $exploded = explode("/", $last_record);
        $number = $exploded[1] + 1;
        $padded = sprintf("%04d", $number);
        $stored = $year . $month . $day . '/' . $padded;

        $total_amount = 0;

        if ($request->payment_method == 'credit') {
            foreach ($estimates as $estimate) {
                $total_amount += $estimate->price * $estimate->quantity - $estimate->discount;
            }
        }

        foreach ($estimates as $estimate) {

            $product = Product::select('id','quantity','selling_price')->where('id', $estimate->product_id)->first();
            if($product->quantity >= $estimate->quantity)
            {
                $data = new Sale();
                $data->business_id = auth()->user()->business_id;
                $data->branch_id = auth()->user()->branch_id;
                $data->receipt_no = $stored;
                $data->product_id = $estimate->product_id;
                $data->price = $product->selling_price;
                $data->quantity = $estimate->quantity;
                if ($estimate->discount == null) {
                    $data->discount = 0;

                } else {
                    $data->discount = $estimate->discount;
                }
                $data->payment_method = $request->payment_method;
                $data->payment_amount = 0;
                $data->staff_id = auth()->user()->id;
                if ($request->payment_method == 'credit') {
                    $data->customer_id = $request->customer;
                } else {
                    $data->customer_id = null;
                }
                $data->note = null;
                $data->save();
               
                $product->quantity -= $estimate->quantity;
                $product->update();
                $estimate->delete();
            }else
            {
                Toastr::error('Out of Stock occured in one or more items');
            }
        }

        if ($request->payment_method == 'credit') {
            $user = User::select('id', 'balance')->where('id', $request->customer)->first();
            $user->balance +=  $total_amount;
            $user->update();
        } 

        Toastr::success('Estimate has been Marked as Sold sucessfully', 'Done');
        return redirect()->route('estimate.all.index');
    }

    public function allSearch(Request $request)
    {
        $query = $request->input('query');
        $user = auth()->user();

        // Perform the search query on the Sale model
        $data['estimates'] = Estimate::select('product_id', 'receipt_no')
        ->where('business_id', $user->business_id)
        ->where('branch_id', $user->branch_id)
        ->where(function($queryBuilder) use ($query) {
            $queryBuilder->where('receipt_no', 'LIKE', '%' . $query . '%')
                ->orWhere('note', 'LIKE', '%' . $query . '%');
        })
        ->groupBy('receipt_no')
        ->orderBy('created_at', 'desc')
        ->take(100)
        ->get();
    
        $data['customers'] = User::select('id', 'name')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('usertype', 'customer')->orderBy('name')->get();
        $data['staffs'] = User::whereIn('usertype', ['admin', 'cashier'])->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->get();

        return view('estimate.all_table', $data)->render();

    }

    public function filterSales(Request $request)
    {
        $cashierId = $request->input('cashier_id');
        $user = auth()->user();
        $query = Estimate::select('product_id', 'receipt_no')
            ->where('business_id', $user->business_id)
            ->where('branch_id', $user->branch_id);

        if ($cashierId && $cashierId != 'all') {
            $query->where('staff_id', $cashierId);
        }

        $data['estimates'] = $query->groupBy('receipt_no')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

            $data['customers'] = User::select('id', 'name')->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->where('usertype', 'customer')->orderBy('name')->get();
            $data['staffs'] = User::whereIn('usertype', ['admin', 'cashier'])->where('business_id', $user->business_id)->where('branch_id', $user->branch_id)->get();
    
        return view('estimate.all_table', $data)->render();
    }

}
