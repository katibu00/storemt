<div class="table-responsive text-nowrap">
    <table class="table table-hover" style="width:100%">
        <thead>
            <tr>
                <th scope="col" class="text-center">#</th>
                <th scope="col">Estimate ID</th>
                <th scope="col">Date</th>
                <th scope="col">Name</th>
                <th scope="col">Note</th>
                <th scope="col" class="text-center">Amount (&#8358;)</th>
                <th scope="col" class="text-center">Discount (&#8358;)</th>
                <th scope="col" class="text-center">Discounted Amount (&#8358;)</th>
                <th scope="col" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($estimates as $key => $row )
                @php
                    $total_amount = 0;
                    $total_discount = 0;
                    $estimated = App\Models\Estimate::select('price','quantity','discount','customer_id','created_at','receipt_no','note')
                                            ->where('business_id', auth()->user()->business_id)
                                            ->where('branch_id', auth()->user()->branch_id)
                                            ->where('receipt_no', $row->receipt_no)
                                            ->get();
                    foreach ($estimated as $estimate) {
                        $total_amount += ($estimate->price*$estimate->quantity);
                        $total_discount+= $estimate->discount;
                    }         
                @endphp 
            <tr>

              @if ($estimates instanceof Illuminate\Pagination\LengthAwarePaginator)
                    <td class="text-center">{{ $key + $estimates->firstItem() }}</td>
                @else
                    <td class="text-center">{{ $key + 1 }}</td>
                @endif

              <th scope="row">{{ $estimate->created_at }}</th>
              <td>{{ $estimated[0]->created_at->format('l, d F') }}</td>
              <td>
                @if ($estimated[0]->customer == 0)
                    Walk-in Customer
                @elseif (is_numeric($estimated[0]->customer))
                    {{ @$estimated[0]->buyer->first_name }}
                @endif
            </td>
            <td>{{ $estimate->note }}</td>
              <td class="text-center">{{ number_format($total_amount,0) }}</td>
              <td class="text-center">{{ number_format($total_discount,0) }}</td>
              <td class="text-center">{{ number_format($total_amount-$total_discount,0) }}</td>
              <td>
                <button type="button" onclick="PrintReceiptContent('{{ $row->receipt_no}}')" class="btn btn-secondary btn-sm"><i class="fa fa-print"></i></button>
                <button type="button" class="btn btn-success btn-sm saleItem" data-note="{{ $estimate->note }}" data-name="{{ $estimate->customer }}" data-receipt_no="{{ $estimate->receipt_no  }}" data-payable="{{ $total_amount-$total_discount }}"><i class="fas fa-shopping-cart" data-bs-toggle="modal" data-bs-target=".addModal"></i></button>
              </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            @if ($estimates instanceof Illuminate\Pagination\LengthAwarePaginator && $estimates->hasPages())
                {{ $estimates->links() }}
            @endif
        </ul>
    </nav>
    
