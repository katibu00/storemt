<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\User;
use Brian2694\Toastr\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function cashier(Request $request)
    {
        $branch_id = auth()->user()->branch_id;
        $business_id = auth()->user()->business_id;
        
        $todaySales = Sale::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNotIn('product_id', [1093, 1012])->whereDate('created_at', today())->get();
        $todayReturns = Returns::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $todayExpenses = Expense::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $creditPayments = Payment::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $estimates = Estimate::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $purchases = Purchase::select('product_id', 'quantity')->where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        

        $data['totalDiscounts'] = $todaySales->sum('discount');
        //sales
        $data['grossSales'] = $todaySales->sum(function ($sale) {
            return $sale->price * $sale->quantity;
        });
        $data['totalDiscount'] = $todaySales->sum('discount');
        $data['posSales'] = $todaySales->where('payment_method', 'pos')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['cashSales'] = $todaySales->where('payment_method', 'cash')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['transferSales'] = $todaySales->where('payment_method', 'transfer')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['creditSales'] = $todaySales->where('payment_method', 'credit')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['grossProfit'] = $todaySales->sum(function ($sale) {
            return (($sale->price - $sale->product->buying_price) * $sale->quantity);
        });
        $data['uniqueSalesCount'] = @$todaySales->unique('receipt_no')->count();
        $data['totalItemsSold'] = $todaySales->sum('quantity');
        //returns
        $data['totalReturn'] = $todayReturns->sum(function ($return) {
            return ($return->price * $return->quantity) - $return->discount;
        });
        $data['cashReturns'] = $todayReturns->where('payment_method', 'cash')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['posReturns'] = $todayReturns->where('payment_method', 'pos')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['transferReturns'] = $todayReturns->where('payment_method', 'transfer')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['returnDiscounts'] = $todayReturns->sum('discount');

        $data['returnProfit'] = $todayReturns->sum(function ($return) {
            return (($return->price - $return->product->buying_price) * $return->quantity);
        });

        //Expenses
        $data['totalExpenses'] = $todayExpenses->sum('amount');
        $data['cashExpenses'] = $todayExpenses->where('payment_method', 'cash')->sum('amount');
        $data['posExpenses'] = $todayExpenses->where('payment_method', 'pos')->sum('amount');
        $data['transferExpenses'] = $todayExpenses->where('payment_method', 'transfer')->sum('amount');
        //credit Payments
        $data['totalCreditPayments'] = $creditPayments->sum('payment_amount');
        $data['cashCreditPayments'] = $creditPayments->where('payment_method', 'cash')->sum('payment_amount');
        $data['posCreditPayments'] = $creditPayments->where('payment_method', 'POS')->sum('payment_amount');
        $data['transferCreditPayments'] = $creditPayments->where('payment_method', 'transfer')->sum('payment_amount');
        //estimates
        $data['totalEstimate'] = $estimates->sum(function ($estimate) {
            return ($estimate->price * $estimate->quantity) - $estimate->discount;
        });
        //purchases
        $data['totalPurchases'] = $purchases->sum(function ($purchase) {
            return $purchase['product']['buying_price'] * $purchase->quantity;
        });
        $stocks = Product::where('business_id', $business_id)->where('branch_id', $branch_id)
            ->where('quantity', '<=', 'critical_level')
            ->get();
        $data['lows'] = count($stocks);
        $data['total_stock'] = Product::select('id')->where('business_id', $business_id)->where('branch_id', $branch_id)->count();

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(6);

        $salesData = Sale::where('branch_id', $branch_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('date(created_at) as date, sum(price * quantity - discount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data['dates'] = $salesData->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->shortDayName;
        });

        $data['revenues'] = $salesData->pluck('revenue');

        $salesData = Sale::where('business_id', $business_id)
            ->where('branch_id', $branch_id)
            ->whereNotIn('product_id', [1093, 1012])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'DESC')
            ->take(10)
            ->get();

        $data['labels'] = $salesData->pluck('product.name');
        $data['values'] = $salesData->pluck('total_quantity');

        $salesByTime = DB::table('sales')
            ->select(DB::raw('HOUR(created_at) AS hour'), DB::raw('SUM(price*quantity - discount) AS amount'))
            ->whereDate('created_at', Carbon::today())
            ->where('branch_id', $branch_id)
            ->where('business_id', $business_id)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy(DB::raw('HOUR(created_at)'))
            ->get();

        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        // Prepare chart data
        foreach ($salesByTime as $sale) {
            $hour = Carbon::createFromFormat('H', $sale->hour)->format('ga');
            $chartData['labels'][] = $hour;
            $chartData['data'][] = $sale->amount;
        }

        $data['chartData'] = $chartData;


        return view('home.cashier', $data);

    }

    public function change_branch(Request $request)
    {

        if ($request->branch_id == '') {
            return redirect()->back();
            Toastr::error("Branch is not selected");
        }
        $user = User::find(auth()->user()->id);
        $user->branch_id = $request->branch_id;
        $user->update();
        return redirect()->route('admin.home');
    }

    public function admin(Request $request)
    {
        $business_id = auth()->user()->business_id;
        $branch_id = auth()->user()->branch_id;
        $data['branches'] = Branch::where('business_id',$business_id)->get();

        if(isset($request->date))
        {
            $todaySales = Sale::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNotIn('product_id', [1093, 1012])->whereDate('created_at', $request->date)->get();
            $todayReturns = Returns::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', $request->date)->get();
            $todayExpenses = Expense::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', $request->date)->get();
            $creditPayments = Payment::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNull('payment_type')->whereDate('created_at', $request->date)->get();
            $estimates = Estimate::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', $request->date)->get();
            $purchases = Purchase::select('product_id', 'quantity')->where('branch_id', $branch_id)->where('business_id', $business_id)->whereDate('created_at', $request->date)->get();
            $data['date'] = $request->date;
        }else
        {
            $todaySales = Sale::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNotIn('product_id', [1093, 1012])->whereDate('created_at', today())->get();
            $todayReturns = Returns::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $todayExpenses = Expense::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $creditPayments = Payment::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNull('payment_type')->whereDate('created_at', today())->get();
            $estimates = Estimate::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $purchases = Purchase::select('product_id', 'quantity')->where('branch_id', $branch_id)->where('business_id', $business_id)->whereDate('created_at', today())->get();
        }
        $data['deposits'] = Payment::select('payment_amount')->where('business_id', $business_id)->where('branch_id', $branch_id)->where('payment_type', 'deposit')->sum('payment_amount');

        $data['totalDiscounts'] = $todaySales->sum('discount');
        //sales 
        $data['grossSales'] = $todaySales->sum(function($sale) {
            return $sale->price * $sale->quantity;
        });
        $data['totalDiscount'] = $todaySales->sum('discount');
        $data['posSales'] = $todaySales->where('payment_method', 'pos')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['cashSales'] = $todaySales->where('payment_method', 'cash')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['transferSales'] = $todaySales->where('payment_method', 'transfer')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['creditSales'] = $todaySales->where('payment_method', 'credit')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['grossProfit'] = $todaySales->sum(function($sale) {
            return (($sale->price - $sale->product->buying_price) * $sale->quantity);
        });
        $data['uniqueSalesCount'] = @$todaySales->unique('receipt_no')->count();
        $data['totalItemsSold'] = $todaySales->sum('quantity');
        //returns
        $data['totalReturn'] = $todayReturns->sum(function($return) {
            return ($return->price * $return->quantity) - $return->discount;
        });
        $data['cashReturns'] = $todayReturns->where('payment_method', 'cash')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['posReturns'] = $todayReturns->where('payment_method', 'pos')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['transferReturns'] = $todayReturns->where('payment_method', 'transfer')->reduce(function ($total, $return) {
            $total += ($return->price * $return->quantity) - $return->discount;
            return $total;
        }, 0);
        $data['returnDiscounts'] = $todayReturns->sum('discount');

        $data['returnProfit'] = $todayReturns->sum(function($return) {
            return (($return->price - $return->product->buying_price) * $return->quantity);
        });
       
        //Expenses
        $data['totalExpenses'] = $todayExpenses->sum('amount');
        $data['cashExpenses'] = $todayExpenses->where('payment_method', 'cash')->sum('amount');
        $data['posExpenses'] = $todayExpenses->where('payment_method', 'pos')->sum('amount');
        $data['transferExpenses'] = $todayExpenses->where('payment_method', 'transfer')->sum('amount');
        //credit Payments
        $data['totalCreditPayments'] = $creditPayments->sum('payment_amount');
        $data['cashCreditPayments'] = $creditPayments->where('payment_method', 'cash')->sum('payment_amount');
        $data['posCreditPayments'] = $creditPayments->where('payment_method', 'POS')->sum('payment_amount');
        $data['transferCreditPayments'] = $creditPayments->where('payment_method', 'transfer')->sum('payment_amount');
        //estimates
        $data['totalEstimate'] = $estimates->sum(function($estimate) {
            return ($estimate->price * $estimate->quantity) - $estimate->discount;
        });
        //purchases
        $data['totalPurchases'] = $purchases->sum(function($purchase) {
            return $purchase['product']['buying_price'] * $purchase->quantity;
        });
        $stocks = Product::where('branch_id', $branch_id)
               ->where('quantity', '<=', 'critical_level')
               ->get();
        $data['lows'] = count($stocks);
        $data['total_stock'] = Product::select('id')->where('branch_id', $branch_id)->count();




        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(6);
        
        $salesData = Sale::where('branch_id', $branch_id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->selectRaw('date(created_at) as date, sum(price * quantity - discount) as revenue')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();
                        
        $data['dates'] = $salesData->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->shortDayName;
        });
        
        $data['revenues'] = $salesData->pluck('revenue');

        $salesData = Sale::where('branch_id', $branch_id)
                    ->whereNotIn('product_id', [1093, 1012])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('product_id')
                    ->orderBy('total_quantity', 'DESC')
                    ->take(10)
                    ->get();
                   
        $data['labels'] = $salesData->pluck('product.name');
        $data['values'] = $salesData->pluck('total_quantity');



        $salesByTime = DB::table('sales')
        ->select(DB::raw('HOUR(created_at) AS hour'), DB::raw('SUM(price*quantity - discount) AS amount'))
        ->whereDate('created_at', Carbon::today())
        ->where('branch_id', $branch_id)
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy(DB::raw('HOUR(created_at)'))
        ->get();

    $chartData = [
        'labels' => [],
        'data' => [],
    ];

    // Prepare chart data
    foreach ($salesByTime as $sale) {
        $hour = Carbon::createFromFormat('H', $sale->hour)->format('ga');
        $chartData['labels'][] = $hour;
        $chartData['data'][] = $sale->amount;
    }

    $data['chartData'] = $chartData;


    //////////////


    $yesterday = Carbon::yesterday();

    $salesByBranch = DB::table('sales')
        ->join('branches', 'sales.branch_id', '=', 'branches.id')
        ->select('branches.name', DB::raw('SUM(price * quantity - discount) AS revenue'))
        ->whereDate('sales.created_at', $yesterday)
        ->groupBy('sales.branch_id')
        ->get();

    $pieChartData = [
        'labels' => [],
        'data' => [],
        'backgroundColor' => [],
    ];

    // Prepare chart data
    foreach ($salesByBranch as $sale) {
        $pieChartData['labels'][] = $sale->name;
        $pieChartData['data'][] = $sale->revenue;
        $pieChartData['backgroundColor'][] = '#' . substr(md5(rand()), 0, 6); // Generate random color for each branch
    }


    $data['pieChartData'] = $pieChartData;


        return view('home.admin', $data);

    }

    // public function super()
    // {
    //     return view('home.super');
    // }



    public function super()
    {
        // Get count of registered businesses
        $registeredBusinesses = Business::count();
    
        // Get count of registered users
        $registeredUsers = User::count();
    
        // Get the date range for the last 10 days
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now();
    
        // Calculate the login count for today
        $loginCountToday = LoginLog::whereDate('login_at', Carbon::today())->count();
    
        // Calculate the sales count for today
        $salesCountToday = Sale::whereDate('created_at', Carbon::today())->count();
    
        // Get all registered businesses
        $businesses = Business::all();
    
        // Get yesterday's date
        $yesterday = Carbon::today();
    
        // Loop through the businesses to gather the required data
  
        
        foreach ($businesses as $business) {
            // Retrieve the count and last upload date of products for the business
            $business->productsCount = $business->products()->count();
            $lastProductUploadDate = $business->products()
                ->latest('created_at')
                ->value('created_at');
            $business->lastProductUploadDate = $lastProductUploadDate ? Carbon::parse($lastProductUploadDate)->diffForHumans() : null;
        
            // Retrieve the count and last sales record date for the business
            $business->salesCount = $business->sales()->count();
            $lastSalesRecordDate = $business->sales()
                ->latest('created_at')
                ->value('created_at');
            $business->lastSalesRecordDate = $lastSalesRecordDate ? Carbon::parse($lastSalesRecordDate)->diffForHumans() : null;
        
            // Check if the business has logged in within the specified date ranges
            $business->loggedIn = $business->loginLogs()
                ->where('login_at', '>=', $yesterday)
                ->exists();
            $business->loginCountLast10Days = $business->loginLogs()
                ->whereBetween('login_at', [$startDate, $endDate])
                ->count();
            $business->salesCountLast10Days = $business->sales()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }
        
    
        // Pass the data to the view
        return view('home.super', compact('businesses', 'registeredBusinesses', 'registeredUsers', 'loginCountToday', 'salesCountToday'));
    }
    
    
    
    


}
