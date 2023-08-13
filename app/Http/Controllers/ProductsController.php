<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{

    public function index(){
        
        $business = Business::where('id',auth()->user()->business_id)->first();
       
        if($business->has_branches == 1)
        {
            $data['products'] = Product::where('branch_id',0)->paginate(10);
            $data['has_branches'] = 1;
            $data['branches'] = Branch::where('business_id',$business->id)->get();
        }else
        {
            $data['products'] = Product::where('branch_id',auth()->user()->branch_id)->paginate(5);
            $data['has_branches'] = 0;
        }

        return view('products.index',$data);
    }

    public function store(Request $request){

        $validatedData = $request->validate([
            'productName.*' => 'required',
            'buyingPrice.*' => 'required|numeric',
            'sellingPrice.*' => 'required|numeric',
            'quantity.*' => 'required|numeric',
            'alertLevel.*' => 'required|numeric',
        ]);
      
        $user = auth()->user();
        if($user->business->has_branches == 1)
        {
            $branchID = $request->branch_id;
        }else
        {
            $branchID = $user->branch_id;
        }

        foreach ($validatedData['productName'] as $index => $productName) {
            $product = new Product();
            $product->business_id = $user->business_id;
            $product->branch_id = $branchID;
            $product->name = $productName;
            $product->buying_price = $validatedData['buyingPrice'][$index];
            $product->selling_price = $validatedData['sellingPrice'][$index];
            $product->quantity = $validatedData['quantity'][$index];
            $product->alert_level = $validatedData['alertLevel'][$index];
            $product->save();
        }

        // Return a response indicating success
        return response()->json(['message' => 'Products added successfully'], 200);

    }

    public function update(Request $request)
    {
        // dd($request->all());
        $product = Product::findOrFail($request->productId);

        $validatedData = $request->validate([
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'quantity' => 'required|integer',
            'alert_level' => 'required|integer',
            'productName' => 'required',
        ]);

        $product->name = $validatedData['productName'];
        $product->buying_price = $validatedData['buying_price'];
        $product->selling_price = $validatedData['selling_price'];
        $product->quantity = $validatedData['quantity'];
        $product->alert_level = $validatedData['alert_level'];
        $product->save();

        return response()->json(['message' => 'Product updated successfully']);
    }
   
    public function delete(Request $request)
    {
        $product = Product::find($request->productID);

        if (!$product) {
            return response()->json(['success' => false], 404);
        }


        $product->delete();

        return response()->json(['success' => true]);
    }

    public function fetchStocks(Request $request)
    {
        $data['products'] = Product::where('branch_id', $request->branch_id)->paginate(25);
        return view('products.table', $data)->render();
      
    }


    public function toggleStatus(Request $request)
    {
        $product = Product::findOrFail($request->id);

        // Toggle the status
        $product->status = $product->status == 1 ? 0 : 1;
        $product->save();

        $message = "Product status toggled successfully.";
        return response()->json(['message' => $message]);
    }
   
    public function filter(Request $request)
    {
        $filter = $request->input('filter');
        $user = auth()->user();
    
        // $perPage = 5; 
        $products = Product::where('business_id', $user->business_id)
            ->where('branch_id', $user->branch_id);
        
        switch ($filter) {
            case 'active':
                $products->where('status', 1);
                break;
            case 'inactive':
                $products->where('status', 0);
                break;
            case 'out_of_stock':
                $products->where('quantity', 0);
                break;
            case 'well_stocked':
                $products->where('quantity', '>', DB::raw('2 * alert_level'));
                break;
            case 'getting_low':
                $products->where('quantity', '<=', DB::raw('2 * alert_level'));
                break;
            case 'below_alert':
                $products->where('quantity', '<=', DB::raw('alert_level'));
                break;
        }
        
        $products = $products->get();
        
    
        // Pass the filtered products to the table view
        $data = [
            'products' => $products
        ];
    
        // Return the rendered table view
        return view('products.table', $data)->render();
    }

    public function Search(Request $request)
    {
        $searchQuery = $request->input('search');
        
        $data['products'] = Product::where('branch_id', auth()->user()->branch_id)->where('name', 'like','%'.$searchQuery.'%')->paginate(25);

        if( $data['products']->count() > 0 )
        {
            return view('products.table', $data)->render();
        }else
        {
            return response()->json([
                'status' => 404,
            ]);
        }
    }


}
