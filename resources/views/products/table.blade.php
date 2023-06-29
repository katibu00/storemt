<div class="table-responsive">
    @if ($products->isEmpty() && !Request::filled('filter'))
        <div class="container">
            <div class="alert alert-info text-center">
                <strong>No products available!</strong> You have not uploaded any products.
            </div>
            <div class="text-center my-3">
                <a class="btn btn-primary mx-2" href="#">Add Products</a>
            </div>
        </div>
    @elseif ($products->isEmpty())
        <div class="container">
            <div class="alert alert-info text-center">
                <strong>No matching products found!</strong> The selected filter does not match any products.
            </div>
        </div>
    @else
        <table class="table table-hover text-nowrap" style="width: 100%">
            <thead>
                <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col">Product Name</th>
                    <th scope="col" class="text-center">Cost Price (&#8358;)</th>
                    <th scope="col" class="text-center">Retail Price (&#8358;)</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-center">Alert Level</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $key => $product)
                    <tr>
                        <td class="text-center">
                            @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                {{ $key + $products->firstItem() }}
                            @else
                                {{ $key + 1 }}
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td class="text-center">{{ number_format($product->buying_price, 0) }}</td>
                        <td class="text-center">{{ number_format($product->selling_price, 0) }}</td>
                        <td class="text-center">{{ number_format($product->quantity, 0) }}</td>
                        <td class="text-center">{{ number_format($product->critical_level, 0) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $product->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->status ? 'Active' : 'Inactive' }}
                            </span>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                    id="actionDropdown{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false"> Actions
                                </button>
                                <div class="dropdown-menu" aria-labelledby="actionDropdown{{ $product->id }}">
                                    <a class="dropdown-item" href="">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a class="dropdown-item delete" href="#" data-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                    <a class="dropdown-item toggle-status" href="#" data-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-status="{{ $product->status }}">
                                        @if ($product->status == 1)
                                            <i class="fa fa-toggle-off"></i> Inactivate
                                        @else
                                            <i class="fa fa-toggle-on"></i> Activate
                                        @endif
                                    </a>
                                    
                                </div>
                            </div>
                        </td>
                        
                            
                    </tr>
                @endforeach
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $products->links() }}
                @endif
            </ul>
        </nav>
    @endif
</div>
