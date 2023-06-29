<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function settingsIndex(){
        return view('client.settings');
    }



    public function settingsSave(Request $request)
    {
        $business = auth()->user()->business;
        $branch = auth()->user()->branch;
        // dd($request->all());
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:100', 
            'business_name' => 'required|string',
            'business_username' => 'required|string|unique:businesses,username,' . $business->id,
            'main_branch_name' => 'required|string',
            'main_branch_address' => 'required|string',
            'main_branch_phone' => 'required|string',
            'main_branch_email' => 'nullable|email|unique:branches,email,' . $branch->id,
        ]);
        
        // Update business attributes
        $business->name = $request->input('business_name');
        $business->username = $request->input('business_username');
        $business->has_branches = $request->has('has_multiple_branches') ? 1 : 0;
    
        // Upload and save the logo
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFileName = time() . '_' . $logoFile->getClientOriginalExtension();
            $businessUsername = $business->username; 
            $logoFile->move(public_path('uploads/' . $businessUsername), $logoFileName);
            $business->logo = 'uploads/' . $businessUsername . '/' . $logoFileName;
        }
    
        // Save the business
        $business->save();
    
        // Update branch attributes
        $branch->name = $request->input('main_branch_name');
        $branch->address = $request->input('main_branch_address');
        $branch->phone = $request->input('main_branch_phone');
        $branch->email = $request->input('main_branch_email');
        $branch->description = 'main';
    
        // Save the branch
        $branch->save();
    
        Toastr::success('Business updated successfully');
        return redirect()->route('business.settings');
    }

    



}
