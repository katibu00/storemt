<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    function index(){
        $data['branches'] = Branch::where('business_id', auth()->user()->business_id)->get();
       $main = $data['branches']->where('description','main')->first(); 
        $data['purchases'] = Purchase::select('date')->where('branch_id',$main->id)->groupBy('date')->paginate(15);
        return view('purchases.index',$data);
    }

    function create(){
        $data['branches'] = Branch::where('business_id', auth()->user()->business_id)->get();
        $data['products'] = Product::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get();
        return view('purchases.create',$data);
    }
    function shopping_list(){

        $data['branches'] = Branch::where('business_id', auth()->user()->business_id)->get();
        $data['lows'] = [];
        return view('purchases.shopping_list',$data);
    }



    // function store(Request $request){
    //     dd($request->all());
    //     $productCount = count($request->product_id);
    //     if($productCount != NULL){
    //         for ($i=0; $i < $productCount; $i++){
    //             $data = Product::find($request->product_id[$i]);
    //             $data->quantity += $request->quantity[$i];
    //             if($request->buying_price[$i] != '')
    //             {
    //                 $data->buying_price = $request->buying_price[$i];  
    //             }
    //             if($request->selling_price[$i] != '')
    //             {
    //                 $data->selling_price = $request->selling_price[$i];  
    //             }
    //             $data->update();

    //             $data = new Purchase();
    //             $data->branch_id = auth()->user()->branch_id;
    //             $data->business_id = auth()->user()->business_id;
    //             $data->product_id = $request->product_id[$i];
    //             $data->quantity = $request->quantity[$i];
    //             $data->date = $request->date;
    //             $data->save();
    //         }
    //     }
      
    //     return response()->json([
    //         'status' => 201,
    //         'message' => 'Purchases has been added sucessfully',
    //         ]);

    // }




    public function store(Request $request)
    {
        // dd(123);

        // Validate the incoming request
        $request->validate([
            'product.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric|min:1',
            // 'price_changed.*' => 'boolean',
            'new_purchase_price.*' => 'nullable|numeric|min:0',
            'new_selling_price.*' => 'nullable|numeric|min:0',
            'date' => 'required|date', 
        ]);
        try {
            DB::beginTransaction();
            // Loop through each purchase record
            foreach ($request->product as $key => $productId) {
                // Fetch the product
                $product = Product::findOrFail($productId);

                // Set the old values in the purchase table
                $purchase = new Purchase([
                    'business_id' => $product->business_id,
                    'branch_id' => $product->branch_id,
                    'product_id' => $productId,
                    'quantity' => $request->quantity[$key],
                    'old_quantity' => $product->quantity,
                    'old_buying_price' => $product->buying_price,
                    'date' => $request->date,
                ]);
               

                // Check if price has changed
                if (isset($request->price_changed[$key]) && $request->price_changed[$key]) {
                    // Ensure that price_changed is set and true
                
                    // Check if new_purchase_price and new_selling_price are set
                    if (isset($request->new_purchase_price[$key], $request->new_selling_price[$key])) {
                        // Record old prices in the purchases table
                        $purchase->new_buying_price = $request->new_purchase_price[$key];
                        $purchase->old_selling_price = $product->selling_price;
                        $purchase->old_buying_price = $product->buying_price;
                
                        // Update product prices
                        $product->buying_price = $request->new_purchase_price[$key];
                        $product->selling_price = $request->new_selling_price[$key];
                      
                    } 
                }

                $product->quantity += $request->quantity[$key];
                $product->save();

                $purchase->save();
            }

            DB::commit();

            return redirect()->route('purchase.index')->with('success', 'Purchases recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording purchases. ' . $e->getMessage());
        }
    }



    function details($date){

        $data['purchases'] = Purchase::where('business_id', auth()->user()->business_id)->whereDate('date', $date)->get();
        return view('purchases.details',$data);
    }

    
    public function fetchStocks(Request $request)
    {
        $stocks = Product::where('business_id', auth()->user()->business_id)->where('branch_id', $request->branch_id)->get();
        return response()->json([
        'status' => 200,
        'stocks' => $stocks,
        ]);
      
    }

    public function fetchShopList(Request $request)
    {
        $lows = [];
        $stocks = Product::where('business_id', auth()->user()->business_id)->where('branch_id', $request->branch_id)->get();
        foreach($stocks as $stock){

            if($stock->quantity <= $stock->critical_level){
                array_push($lows, $stock);
            }
        }
        $data['lows'] = $lows;
        return view('purchases.shopping_list_table', $data)->render();
    }
    public function fetchPurchases(Request $request)
    {
        $data['purchases'] = Purchase::select('date')->where('business_id', auth()->user()->business_id)->where('branch_id', $request->branch_id)->groupBy('date')->orderBy('created_at','desc')->paginate(15);
        return view('purchases.table', $data)->render();
    }


}
