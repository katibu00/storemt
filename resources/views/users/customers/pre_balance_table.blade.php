<div class="row">
    <div class="col-md-6">
        <!-- Display Previous Balance Here -->
        <h5>Previous Balance: &#8358;{{ number_format( $user->pre_balance, 2) }}</h5>
    </div>
    <div class="col-md-6 text-md-end">
        <!-- Add New Payment Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
            Add New Payment
        </button>
    </div>
</div>
@php
    $pre_payments = App\Models\Payment::where('customer_id', $user->id)->where('payment_type','pre_bal')->where('business_id', auth()->user()->business_id)->latest()->get();
@endphp
<!-- pre_balance_table.blade.php -->
<div class="table-responsive border">
    <table class="table" style="width: 100%; font-size: 12px;">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Date</th>
                <th scope="col">Amount</th>
                <th scope="col">Method</th>
                <th scope="col">Description</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach ($pre_payments as $key => $pre_payment)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $pre_payment->created_at->diffForHumans() }}</td>
                    <td>{{ $pre_payment->payment_amount }}</td>
                    <td>{{ ucfirst($pre_payment->payment_method) }}</td>
                    <td>{{ $pre_payment->note }}</td>
                </tr>
            @endforeach
        </tbody>
       
    </table>
</div>
