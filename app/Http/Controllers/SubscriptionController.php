<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{

    public function show()
    {
        // Retrieve current user's business details, including the subscription information
        $business = auth()->user()->business;

        // Retrieve the subscription plans from the database
        $subscriptionPlans = SubscriptionPlan::all();

        // Other logic to retrieve current plan details

        return view('subscription.indexa', [
            'business' => $business,
            'subscriptionPlans' => $subscriptionPlans,
        ]);
    }

    // SubscriptionController.php

    public function choosePlan()
    {
        // Fetch your subscription plans from the database
        $subscriptionPlans = SubscriptionPlan::all();

        return view('subscription.choose-plan', compact('subscriptionPlans'));
    }

    public function confirmSubscription($plan)
    {
        // Fetch the selected plan from the database or session
        $selectedPlan = SubscriptionPlan::where('name', $plan)->first();

        // Pass the plan details to the view
        return view('subscription.confirmation', compact('selectedPlan'));
    }



    public function proceedToPayment(Request $request)
    {
        // Retrieve the selected plan, billing cycle, and payment gateway from the form submission
        $selectedPlan = SubscriptionPlan::where('name', $request->input('selectedPlanName'))->first();
        $billingCycle = $request->input('billingCycle');
        $paymentGateway = $request->input('paymentGateway');

        // Validate the selected plan, billing cycle, and payment gateway
        // Add additional validation rules based on your requirements

        // Perform further actions based on the selected payment gateway
        if ($paymentGateway === 'bankTransfer') {
            // If the payment gateway is bank transfer, generate the invoice and display the invoice page
            return view('subscription.invoice', [
                'selectedPlan' => $selectedPlan,
                'billingCycle' => $billingCycle,
                // Add other necessary details for the invoice view
            ]);
        } else {
            // Handle other payment gateways (add logic as needed)
            return redirect()->back()->with('error', 'Invalid payment gateway selected.');
        }
    }


}
