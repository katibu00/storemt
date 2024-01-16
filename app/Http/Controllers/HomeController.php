<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\FundTransfer;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function cashier(Request $request)
    {
        $branch_id = auth()->user()->branch_id;

        $todaySales = Sale::where('branch_id', $branch_id)->whereNotIn('product_id', [1093, 1012])->whereDate('created_at', today())->get();
        $todayReturns = Returns::where('branch_id', $branch_id)->whereNull('channel')->whereDate('created_at', today())->get();
        $todayExpenses = Expense::where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $creditPayments = Payment::where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $estimates = Estimate::where('branch_id', $branch_id)->whereDate('created_at', today())->get();
        $purchases = Purchase::select('product_id', 'quantity')->where('branch_id', $branch_id)->whereDate('created_at', today())->get();

        $data['totalDiscounts'] = $todaySales->sum('discount');
        //sales
        $data['grossSales'] = $todaySales->sum(function ($sale) {
            return $sale->price * $sale->quantity;
        });
        $data['totalDiscount'] = $todaySales->sum('discount');
        
        $uniquePosReceipts = [];

        $data['posSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'pos' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniquePosReceipts) {
                if ($sale->payment_method == 'multiple') {
                    if (!in_array($sale->receipt_no, $uniquePosReceipts)) {
                        $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                            ->where('branch_id', $branch_id)
                            ->where('payment_type', 'multiple')
                            ->where('payment_method', 'pos')
                            ->value('payment_amount');

                        $total += $paymentAmount ?? 0;

                        $uniquePosReceipts[] = $sale->receipt_no;
                    }
                } else {
                    $total += ($sale->price * $sale->quantity) - $sale->discount;
                }

                return $total;
            }, 0);

       
        $uniqueReceipts = [];

        $data['cashSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'cash' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniqueReceipts) {
                if ($sale->payment_method == 'multiple') {
                    // Check if the receipt number is not already in the uniqueReceipts array
                    if (!in_array($sale->receipt_no, $uniqueReceipts)) {
                        $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                            ->where('branch_id', $branch_id)
                            ->where('payment_type', 'multiple')
                            ->where('payment_method', 'cash')
                            ->value('payment_amount');

                        $total += $paymentAmount ?? 0;

                        // Add the receipt number to the uniqueReceipts array to ensure uniqueness
                        $uniqueReceipts[] = $sale->receipt_no;
                    }
                } else {
                    $total += ($sale->price * $sale->quantity) - $sale->discount;
                }

                return $total;
            }, 0);
       
        $uniqueTransferReceipts = [];

        $data['transferSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'transfer' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniqueTransferReceipts) {
            if ($sale->payment_method == 'multiple') {
                // Check if the receipt number is not already in the uniqueTransferReceipts array
                if (!in_array($sale->receipt_no, $uniqueTransferReceipts)) {
                    $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                        ->where('branch_id', $branch_id)
                        ->where('payment_type', 'multiple')
                        ->where('payment_method', 'transfer')
                        ->value('payment_amount');

                    $total += $paymentAmount ?? 0;

                    // Add the receipt number to the uniqueTransferReceipts array to ensure uniqueness
                    $uniqueTransferReceipts[] = $sale->receipt_no;
                }
            } else {
                $total += ($sale->price * $sale->quantity) - $sale->discount;
            }

            return $total;
        }, 0);

        
        $data['creditSales'] = $todaySales->where('payment_method', 'credit')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['depositSales'] = $todaySales->where('payment_method', 'deposit')->reduce(function ($total, $sale) {
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
            return ($return->price * $return->quantity);
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
        $data['uncollectedSales'] = Sale::where('branch_id', $branch_id)
            ->where('collected', 0)
            ->groupBy('receipt_no')
            ->get();

        //Expenses
        $data['totalExpenses'] = $todayExpenses->sum('amount');
        $data['cashExpenses'] = $todayExpenses->where('payment_method', 'cash')->sum('amount');
        $data['posExpenses'] = $todayExpenses->where('payment_method', 'pos')->sum('amount');
        $data['transferExpenses'] = $todayExpenses->where('payment_method', 'transfer')->sum('amount');
        //credit Payments
        $data['totalCreditPayments'] = $creditPayments->where('payment_type', 'credit')->sum('payment_amount');
        $data['cashCreditPayments'] = $creditPayments->where('payment_method', 'cash')->where('payment_type', 'credit')->sum('payment_amount');
        $data['posCreditPayments'] = $creditPayments->where('payment_method', 'POS')->where('payment_type', 'credit')->sum('payment_amount');
        $data['transferCreditPayments'] = $creditPayments->where('payment_method', 'transfer')->where('payment_type', 'credit')->sum('payment_amount');
        //deposits
        $data['totalDepositPayments'] = $creditPayments->where('payment_type', 'deposit')->sum('payment_amount');
        $data['cashDepositPayments'] = $creditPayments->where('payment_method', 'cash')->where('payment_type', 'deposit')->sum('payment_amount');
        $data['posDepositPayments'] = $creditPayments->where('payment_method', 'POS')->where('payment_type', 'deposit')->sum('payment_amount');
        $data['transferDepositPayments'] = $creditPayments->where('payment_method', 'transfer')->where('payment_type', 'deposit')->sum('payment_amount');
        //estimates
        $data['totalEstimate'] = $estimates->sum(function ($estimate) {
            return ($estimate->price * $estimate->quantity) - $estimate->discount;
        });
        //purchases
        $data['totalPurchases'] = $purchases->sum(function ($purchase) {
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

        $data['cashFundTransfer'] = $data['transferFundTransfer'] = $data['posFundTransfer'] = 0;

        $cashTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'cash')
                  ->orWhere('to_account', 'cash');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        
        // Get transfer transfers created today
        $transferTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'transfer')
                  ->orWhere('to_account', 'transfer');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        
        // Get pos transfers created today
        $posTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'pos')
                  ->orWhere('to_account', 'pos');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        

        // Adjust the account balances based on funds transfers
        $data['cashFundTransfer'] += $cashTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'cash' ? -$transfer->amount : ($transfer->to_account === 'cash' ? $transfer->amount : 0);
        });

        $data['transferFundTransfer'] += $transferTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'transfer' ? -$transfer->amount : ($transfer->to_account === 'transfer' ? $transfer->amount : 0);
        });

        $data['posFundTransfer'] += $posTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'pos' ? -$transfer->amount : ($transfer->to_account === 'pos' ? $transfer->amount : 0);
        });

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
        $data['branches'] = Branch::where('business_id', $business_id)->get();

        if (isset($request->end_date)) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);

            if ($endDate->isFuture()) {
                Toastr::error('End date cannot be in the future');
                return redirect()->route('admin.home');
            }

            $todaySales = Sale::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
            $todayReturns = Returns::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNull('channel')->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
            $todayExpenses = Expense::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
            $creditPayments = Payment::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
            $purchases = Purchase::select('product_id', 'quantity')->where('branch_id', $branch_id)->where('business_id', $business_id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
            $estimates = Estimate::where('branch_id', $branch_id)->where('branch_id', $branch_id)->where('business_id', $business_id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();

            $data['start_date'] = $startDate;
            $data['end_date'] = $endDate;
        } else {
            $todaySales = Sale::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNotIn('product_id', [1093, 1012])->whereDate('created_at', today())->get();
            $todayReturns = Returns::where('business_id', $business_id)->where('branch_id', $branch_id)->whereNull('channel')->whereDate('created_at', today())->get();
            $todayExpenses = Expense::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $creditPayments = Payment::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $estimates = Estimate::where('business_id', $business_id)->where('branch_id', $branch_id)->whereDate('created_at', today())->get();
            $purchases = Purchase::select('product_id', 'quantity')->where('branch_id', $branch_id)->where('business_id', $business_id)->whereDate('created_at', today())->get();
        }
        $data['deposits'] = User::select('deposit')->where('business_id', $business_id)->where('branch_id', $branch_id)->sum('deposit');
        $data['pre_balance'] = User::select('pre_balance')->where('business_id', $business_id)->where('branch_id', $branch_id)->sum('pre_balance');

        $data['totalDiscounts'] = $todaySales->sum('discount');
        //sales
        $data['grossSales'] = $todaySales->sum(function ($sale) {
            return $sale->price * $sale->quantity;
        });
        $data['totalDiscount'] = $todaySales->sum('discount');



        $uniquePosReceipts = [];

        $data['posSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'pos' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniquePosReceipts) {
                if ($sale->payment_method == 'multiple') {
                    if (!in_array($sale->receipt_no, $uniquePosReceipts)) {
                        $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                            ->where('branch_id', $branch_id)
                            ->where('payment_type', 'multiple')
                            ->where('payment_method', 'pos')
                            ->value('payment_amount');

                        $total += $paymentAmount ?? 0;

                        $uniquePosReceipts[] = $sale->receipt_no;
                    }
                } else {
                    $total += ($sale->price * $sale->quantity) - $sale->discount;
                }

                return $total;
            }, 0);

       
        $uniqueReceipts = [];

        $data['cashSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'cash' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniqueReceipts) {
                if ($sale->payment_method == 'multiple') {
                    // Check if the receipt number is not already in the uniqueReceipts array
                    if (!in_array($sale->receipt_no, $uniqueReceipts)) {
                        $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                            ->where('branch_id', $branch_id)
                            ->where('payment_type', 'multiple')
                            ->where('payment_method', 'cash')
                            ->value('payment_amount');

                        $total += $paymentAmount ?? 0;

                        // Add the receipt number to the uniqueReceipts array to ensure uniqueness
                        $uniqueReceipts[] = $sale->receipt_no;
                    }
                } else {
                    $total += ($sale->price * $sale->quantity) - $sale->discount;
                }

                return $total;
            }, 0);

       
        $uniqueTransferReceipts = [];

        $data['transferSales'] = $todaySales
            ->filter(function ($sale) {
                return $sale->payment_method == 'transfer' || $sale->payment_method == 'multiple';
            })
            ->reduce(function ($total, $sale) use ($branch_id, &$uniqueTransferReceipts) {
            if ($sale->payment_method == 'multiple') {
                // Check if the receipt number is not already in the uniqueTransferReceipts array
                if (!in_array($sale->receipt_no, $uniqueTransferReceipts)) {
                    $paymentAmount = Payment::where('receipt_nos', $sale->receipt_no)
                        ->where('branch_id', $branch_id)
                        ->where('payment_type', 'multiple')
                        ->where('payment_method', 'transfer')
                        ->value('payment_amount');

                    $total += $paymentAmount ?? 0;

                    // Add the receipt number to the uniqueTransferReceipts array to ensure uniqueness
                    $uniqueTransferReceipts[] = $sale->receipt_no;
                }
            } else {
                $total += ($sale->price * $sale->quantity) - $sale->discount;
            }

            return $total;
        }, 0);

        $data['creditSales'] = $todaySales->where('payment_method', 'credit')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['depositSales'] = $todaySales->where('payment_method', 'deposit')->reduce(function ($total, $sale) {
            $total += ($sale->price * $sale->quantity) - $sale->discount;
            return $total;
        }, 0);
        $data['grossProfit'] = $todaySales->sum(function ($sale) {
            if ($sale->buying_price != 0) {
                return ($sale->price - $sale->buying_price) * $sale->quantity;
            } else {
                return ($sale->price-@$sale->product->buying_price) * $sale->quantity;
            }
        });
        $data['uniqueSalesCount'] = @$todaySales->unique('receipt_no')->count();
        $data['totalItemsSold'] = $todaySales->sum('quantity');
        //returns
        $data['totalReturn'] = $todayReturns->sum(function ($return) {
            return ($return->price * $return->quantity);
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
        $data['totalCreditPayments'] = $creditPayments->where('payment_type', 'credit')->sum('payment_amount');
        $data['cashCreditPayments'] = $creditPayments->where('payment_method', 'cash')->where('payment_type', 'credit')->sum('payment_amount');
        $data['posCreditPayments'] = $creditPayments->where('payment_method', 'POS')->where('payment_type', 'credit')->sum('payment_amount');
        $data['transferCreditPayments'] = $creditPayments->where('payment_method', 'transfer')->where('payment_type', 'credit')->sum('payment_amount');
        //deposits
        $data['totalDepositPayments'] = $creditPayments->where('payment_type', 'deposit')->sum('payment_amount');
        $data['cashDepositPayments'] = $creditPayments->where('payment_method', 'cash')->where('payment_type', 'deposit')->sum('payment_amount');
        $data['posDepositPayments'] = $creditPayments->where('payment_method', 'POS')->where('payment_type', 'deposit')->sum('payment_amount');
        $data['transferDepositPayments'] = $creditPayments->where('payment_method', 'transfer')->where('payment_type', 'deposit')->sum('payment_amount');
        //estimates
        $data['totalEstimate'] = $estimates->sum(function ($estimate) {
            return ($estimate->price * $estimate->quantity) - $estimate->discount;
        });
        //purchases
        $data['totalPurchases'] = $purchases->sum(function ($purchase) {
            return $purchase['product']['buying_price'] * $purchase->quantity;
        });
        $stocks = Product::where('branch_id', $branch_id)
            ->where('business_id', $business_id)
            ->where('quantity', '<=', 'critical_level')
            ->get();
        $data['lows'] = count($stocks);
        $data['total_stock'] = Product::select('id')->where('business_id', $business_id)->where('branch_id', $branch_id)->count();

        $data['uncollectedSales'] = Sale::where('branch_id', $branch_id)
            ->where('business_id', $business_id)
            ->where('collected', 0)
            ->groupBy('receipt_no')
            ->get();

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(6);

        $salesData = Sale::where('branch_id', $branch_id)
            ->where('business_id', $business_id)
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
            ->where('business_id', $business_id)
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

        //////////////

        if (auth()->user()->business->has_branches) {
            $yesterday = Carbon::yesterday();

            $salesByBranch = DB::table('sales')
                ->join('branches', 'sales.branch_id', '=', 'branches.id')
                ->select('branches.name', DB::raw('SUM(sales.price * sales.quantity - sales.discount) AS revenue'))
                ->where('sales.business_id', $business_id)
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
                $pieChartData['backgroundColor'][] = '#' . substr(md5(rand()), 0, 6);
            }

            $data['pieChartData'] = $pieChartData;
        }

        ////////
        $data['cashFundTransfer'] = $data['transferFundTransfer'] = $data['posFundTransfer'] = 0;

        $cashTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'cash')
                  ->orWhere('to_account', 'cash');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        
        // Get transfer transfers created today
        $transferTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'transfer')
                  ->orWhere('to_account', 'transfer');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        
        // Get pos transfers created today
        $posTransfers = FundTransfer::where(function ($query) {
            $query->where('from_account', 'pos')
                  ->orWhere('to_account', 'pos');
        })
        ->whereDate('created_at', Carbon::today())
        ->get();
        

        // Adjust the account balances based on funds transfers
        $data['cashFundTransfer'] += $cashTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'cash' ? -$transfer->amount : ($transfer->to_account === 'cash' ? $transfer->amount : 0);
        });

        $data['transferFundTransfer'] += $transferTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'transfer' ? -$transfer->amount : ($transfer->to_account === 'transfer' ? $transfer->amount : 0);
        });

        $data['posFundTransfer'] += $posTransfers->sum(function ($transfer) {
            return $transfer->from_account === 'pos' ? -$transfer->amount : ($transfer->to_account === 'pos' ? $transfer->amount : 0);
        });

        return view('home.admin', $data);

    }

    public function super()
    {
        $businesses = Business::all();

        $insights = [];

        foreach ($businesses as $business) {
            $insight = [];

            $totalProducts = Product::where('business_id', $business->id)->count();

            $grossSalesYesterday = Sale::where('business_id', $business->id)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum(DB::raw('price * quantity'));

            $lastSalesDate = Carbon::parse($business->lastSalesDate);

            $loginCountLast10Days = LoginLog::where('business_id', $business->id)
                ->whereDate('login_at', '>=', Carbon::today()->subDays(14))
                ->count();

           

            $businessIsActive = $loginCountLast10Days >= 5; 
            $insight['grossSalesYesterday'] = $grossSalesYesterday;
            $insight['businessIsActive'] = $businessIsActive;
            $insight['business'] = $business;
            $insight['totalProducts'] = $totalProducts;
            $insight['lastSalesDate'] = $lastSalesDate;
            $insight['loginCountLast10Days'] = $loginCountLast10Days;

            $insights[] = $insight;
        }

        $registeredBusinesses = Business::count();
        $registeredUsers = User::count();

        $loginCountToday = LoginLog::whereDate('login_at', Carbon::today())->count();

        $salesCountToday = Sale::whereDate('created_at', Carbon::today())->count();

        return view('home.super', compact('insights', 'registeredBusinesses', 'registeredUsers', 'loginCountToday', 'salesCountToday'));
    }

}
