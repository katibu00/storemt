<div class="table-responsive text-nowrap">
    <table class="table table-hover" style="width:100%">
        <thead>
            <tr>
                <th scope="col" class="text-center">#</th>
                <th scope="col">Sales ID</th>
                <th scope="col">Date</th>
                <th scope="col">Name</th>
                <th scope="col" class="text-center">Amount (&#8358;)</th>
                <th scope="col" class="text-center">Discount (&#8358;)</th>
                <th scope="col" class="text-center">Discounted Amount (&#8358;)</th>
                <th scope="col">Note</th>
                <th scope="col" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returns as $key => $row )
                @php
                    $total_amount = 0;
                    $total_discount = 0;
                    $saled = App\Models\Returns::select('price','quantity','discount','customer_id','created_at','receipt_no','note')
                                            ->where('business_id', auth()->user()->business_id)
                                            ->where('receipt_no', $row->receipt_no)
                                            ->get();
                    foreach ($saled as $sale) {
                        $total_amount += ($sale->price*$sale->quantity);
                        $total_discount+= $sale->discount;
                    }
                            
                @endphp 
            <tr>
              <td class="text-center">{{ $key + $returns->firstItem() }}</td>

              <th scope="row">{{ $sale->receipt_no }}</th>
              <td>{{ $saled[0]->created_at->format('l, d F') }}</td>
              <td>{{ is_numeric($saled[0]->customer) ? @$saled[0]->customer->name : @$saled[0]->customer_id}}</td>
              <td class="text-center">{{ number_format($total_amount,0) }}</td>
              <td class="text-center">{{ number_format($total_discount,0) }}</td>
              <td class="text-center">{{ number_format($total_amount-$total_discount,0) }}</td>
              <td>{{ $saled[0]->note }}</td>

              <td class="text-center">
                <button type="button" onclick="PrintReceiptContent('{{ $row->receipt_no}}')" class="btn btn-secondary btn-sm"><i class="fa fa-print text-white"></i></button>
            </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
          {{ $returns->links() }}
        </ul>
    </nav>
