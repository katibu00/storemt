<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class BranchesController extends Controller
{
    public function index()
    {
        $business_id = auth()->user()->business_id;

        $data['branches'] = Branch::where('business_id', $business_id)->get();
        $data['staffs'] = User::select('id', 'name')->where('business_id', $business_id)->get();
        return view('branches.index', $data);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->business->has_branches) {
            session()->flash('error', 'This business is not allowed to add new branches. Please contact the admin for assistance.');
            return redirect()->route('branches.index');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'required|integer',
        ]);
        $business_id = auth()->user()->business_id;

        // Create and save the branch
        $branch = new Branch([
            'name' => $request->input('name'),
            'business_id' => $business_id,
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'manager_id' => $request->input('manager_id'),
        ]);

        $branch->save();

        // Flash a success message
        session()->flash('success', 'Branch created successfully');

        return redirect()->route('branches.index');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $staffs = User::select('id', 'name')->where('business_id', $branch->business_id)->get();

        return view('branches.edit', compact('branch', 'staffs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'required|integer',
        ]);

        $branch = Branch::findOrFail($id);

        $branch->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'manager_id' => $request->input('manager_id'),
        ]);

        $staff = User::find($request->input('manager_id'));
        $staff->branch_id = $branch->id;
        $staff->update();

        session()->flash('success', 'Branch updated successfully');

        return redirect()->route('branches.index');
    }

    public function destroy($id)
{
    $branch = Branch::findOrFail($id);

    if ($branch->description === 'main') {
        session()->flash('error', 'You cannot delete the main branch of your business.');
    } else {
        $branch->delete();
        session()->flash('success', 'Branch deleted successfully');
    }

    return redirect()->route('branches.index');
}


}
