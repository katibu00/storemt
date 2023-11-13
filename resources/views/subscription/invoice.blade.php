<!-- invoice.blade.php -->

@extends('layouts.app')

@section('PageTitle', 'Invoice')

@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Invoice</div>

                            <div class="card-body">
                                <h3>Invoice Details</h3>
                                <p><strong>Selected Plan:</strong> {{ ucfirst($selectedPlan->name) }}</p>
                                <p><strong>Billing Cycle:</strong> {{ ucfirst($billingCycle) }}</p>
                                <p><strong>Amount to Pay:</strong> {{ number_format(calculateAmountToPay($selectedPlan, $billingCycle), 0) }}</p>
                                <!-- Add other details as needed -->

                                <h3>Bank Transfer Information</h3>
                                <p><strong>Bank Name:</strong> Your Bank Name</p>
                                <p><strong>Account Holder:</strong> Your Name</p>
                                <p><strong>Account Number:</strong> Your Account Number</p>

                                <div class="mt-4">
                                    {{-- <a href="{{ route('subscription.download-invoice') }}" class="btn btn-primary">Download Invoice</a> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@php
    // Calculate the amount to pay based on the selected plan and billing cycle
    function calculateAmountToPay($selectedPlan, $billingCycle) {
        switch ($billingCycle) {
            case 'monthly':
                return $selectedPlan->monthly_price;
            case 'quarterly':
                return $selectedPlan->quarterly_price;
            case 'yearly':
                return $selectedPlan->yearly_price;
            default:
                return 0;
        }
    }
@endphp
