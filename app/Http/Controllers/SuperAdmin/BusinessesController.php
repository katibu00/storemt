<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BusinessesController extends Controller
{
    public function index()
    {
        $businesses = Business::all();

        return view('super_admin.businesses.index', compact('businesses'));
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
        
        

        // Save the business
        $business->save();

        // Create a new Branch instance
        $branch = new Branch();
        $branch->name = $request->input('main_branch_name');
        $branch->address = $request->input('main_branch_address');
        $branch->phone = $request->input('main_branch_phone');
        $branch->email = $request->input('main_branch_email');
        $branch->description = 'main';

        // Save the branch
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



}
