<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BusinessesController extends Controller
{
    public function index()
    {
        $businesses = Business::all();
        $subsplans = SubscriptionPlan::where('name', '!=', 'trial')->get();
        return view('super_admin.businesses.index', compact('businesses', 'subsplans'));
    }

    public function create()
    {
        return view('super_admin.businesses.create');
    }

    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:100',
            'business_name' => 'required|string',
            'business_username' => 'required|string|unique:businesses,username',
            'has_multiple_branches' => 'nullable|boolean',
            'main_branch_name' => 'required|string',
            'main_branch_address' => 'required|string',
            'main_branch_phone' => 'required|string',
            'main_branch_email' => 'nullable|email|unique:branches,email',
            'proprietor_name' => 'required|string',
            'proprietor_phone' => 'required|string',
            'proprietor_email' => 'required|email|unique:users,email',
        ]);

        // Create a new Business instance
        $business = new Business();
        $business->name = $request->input('business_name');
        $business->username = $request->input('business_username');
        $business->has_branches = $request->has('has_multiple_branches');
        $business->registered_by = auth()->user()->id;

        // Upload and save the logo
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFileName = time() . '_' . $logoFile->getClientOriginalExtension();
            $businessUsername = $business->username;
            $logoFile->move(public_path('uploads/' . $businessUsername), $logoFileName);
            $business->logo = 'uploads/' . $businessUsername . '/' . $logoFileName;
        }

        $trialPlan = SubscriptionPlan::where('name', 'Trial')->first();

        $business->subscription_start_date = Carbon::now();
        $business->subscription_end_date = Carbon::now()->addDays(30);
        $business->subscription_plan_id = $trialPlan->id;

        $business->save();

        $branch = new Branch();
        $branch->name = $request->input('main_branch_name');
        $branch->address = $request->input('main_branch_address');
        $branch->phone = $request->input('main_branch_phone');
        $branch->email = $request->input('main_branch_email');
        $branch->description = 'main';

        $business->branches()->save($branch);

        // Create a new User instance
        $user = new User();
        $user->name = $request->input('proprietor_name');
        $user->business_id = $business->id;
        $user->branch_id = $branch->id;
        $user->usertype = 'admin';
        $user->phone = $request->input('proprietor_phone');
        $user->email = $request->input('proprietor_email');
        $user->password = Hash::make('123456');

        $branch->manager()->associate($user);

        $user->save();

        $branch->manager_id = $user->id;
        $branch->save();

        $business->admin_id = $user->id;
        $business->save();

        Toastr::success('Business created successfully');
        return redirect()->route('business.index');
    }

    public function manualFundingSubmit(Request $request, $id)
    {
        // Validate the form data
        $request->validate([
            'plan_id' => 'required',
            'months' => 'required|integer|min:1',
        ]);

        $startDate = Carbon::now();
        $endDate = $startDate->addMonths($request->input('months'));

        $billingCycle = 'monthly';
        if ($request->input('months') == 3) {
            $billingCycle = 'quarterly';
        } elseif ($request->input('months') == 12) {
            $billingCycle = 'yearly';
        }

        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addMonths($request->months)->subDay(); // Subtract one day to make it the last day of the previous month

        Business::where('id', $id)->update([
            'subscription_status' => 'active',
            'subscription_start_date' => $startDate,
            'subscription_end_date' => $endDate,
            'subscription_plan_id' => $request->plan_id,
            'billing_cycle' => $billingCycle,
        ]);

        return redirect()->route('business.index')->with('success', 'Manual funding successful!');
    }

    public function edit($id)
    {
        $business = Business::findOrFail($id);
        return view('super_admin.businesses.edit', compact('business'));
    }

   

    public function update(Request $request, $id)
    {
        // Validate the form data
        $request->validate([
            // 'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:100',
            // 'business_name' => 'required|string',
            // 'business_username' => 'required|string|unique:businesses,username,' . $id,
            // 'has_multiple_branches' => 'nullable|boolean',
            // 'main_branch_name' => 'required|string',
            // 'main_branch_address' => 'required|string',
            // 'main_branch_phone' => 'required|string',
            // 'proprietor_name' => 'required|string',
            // 'proprietor_phone' => 'required|string',
//             'main_branch_email' => 'nullable|email|unique:branches,email,' . $id . ',id',
// 'proprietor_email' => 'required|email|unique:users,email,' . $id . ',id',

        ]);
    
        // Find the business to update
        $business = Business::findOrFail($id);
    
        // Update business details
        $business->name = $request->input('business_name');
        $business->username = $request->input('business_username');
        $business->has_branches = $request->has('has_multiple_branches');
    
        // Upload and save the logo if provided
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFileName = time() . '_' . $logoFile->getClientOriginalExtension();
            $businessUsername = $business->username;
            $logoFile->move(public_path('uploads/' . $businessUsername), $logoFileName);
            $business->logo = 'uploads/' . $businessUsername . '/' . $logoFileName;
        }
    
        $business->save();
    
        // Update main branch details
        $mainBranch = $business->mainBranch;
        $mainBranch->name = $request->input('main_branch_name');
        $mainBranch->address = $request->input('main_branch_address');
        $mainBranch->phone = $request->input('main_branch_phone');
        $mainBranch->email = $request->input('main_branch_email');
        $mainBranch->save();
    
        // Update proprietor details
        $proprietor = $business->admin;
        $proprietor->name = $request->input('proprietor_name');
        $proprietor->phone = $request->input('proprietor_phone');
        $proprietor->email = $request->input('proprietor_email');
        $proprietor->save();
    
        Toastr::success('Business updated successfully');
        return redirect()->route('business.index');
    }
    


}
