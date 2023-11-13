<!-- confirmation.blade.php -->

@extends('layouts.app')

@section('PageTitle', 'Confirm Subscription')

@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Confirm Subscription</div>

                            <div class="card-body">
                                <form action="{{ route('subscription.proceed-to-payment') }}" method="POST">
                                    @csrf
                                    <input type="hidden" value="{{ $selectedPlan->name }}" name="selectedPlanName" />
                                    <h3>Selected Plan: {{ ucfirst($selectedPlan->name) }}</h3>
                                    <p id="amountDisplay">Price: {{ number_format($selectedPlan->monthly_price, 0) }} (Monthly)</p>

                                    {{-- Billing Cycle options --}}
                                    <div class="mt-4">
                                        <label for="billingCycle">Choose Billing Cycle:</label>
                                        <select class="form-select" id="billingCycle" name="billingCycle" onchange="updateAmount()">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>

                                    {{-- Payment Gateway selection --}}
                                    <div class="mt-4">
                                        <label for="paymentGateway">Choose Payment Gateway:</label>
                                        <select class="form-select" id="paymentGateway" name="paymentGateway">
                                            <option value="bankTransfer">Bank Transfer</option>
                                            {{-- Add other payment gateways as needed --}}
                                        </select>
                                    </div>

                                    {{-- Proceed to Payment button --}}
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- JavaScript to update the amount --}}
    <script>
        function updateAmount() {
            var billingCycle = document.getElementById('billingCycle').value;
            var amountDisplay = document.getElementById('amountDisplay');
            var newAmount;

            // Function to format the price with commas
            function formatPrice(price) {
                return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // Update the amount based on the selected billing cycle
            switch (billingCycle) {
                case 'monthly':
                    newAmount = formatPrice('{{ $selectedPlan->monthly_price }}') + ' (Monthly)';
                    break;
                case 'quarterly':
                    newAmount = formatPrice('{{ $selectedPlan->quarterly_price }}') + ' (Quarterly)';
                    break;
                case 'yearly':
                    newAmount = formatPrice('{{ $selectedPlan->yearly_price }}') + ' (Yearly)';
                    break;
                default:
                    newAmount = 'Invalid Billing Cycle';
            }

            // Update the displayed amount
            amountDisplay.innerHTML = 'Price: ' + newAmount;
        }
    </script>
@endsection
