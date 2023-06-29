@extends('layouts.app')
@section('PageTitle', 'Inventories')
@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">

                <div class="card">
                    <!-- Default panel contents -->
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <h5 class="card-title">Products</h5>
                            </div>
                            <div class="col-md-9 text-right">
                                <div class="row align-items-center justify-content-end">
                                    @if ($has_branches == 1)
                                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-md-0">
                                            <select class="form-select" id="branch_id">
                                                <option>-- Select Branch --</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-md-0">
                                        <select class="form-select" id="sort_by">
                                            <option value="all">All</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                            <option value="well_stocked">Well Stocked</option>
                                            <option value="getting_low">Getting Low</option>
                                            <option value="below_alert">Below Alert Level</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-md-0">
                                        <input type="text" class="form-control" id="search-input" placeholder="Search">
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6">
                                        <button class="btn btn-primary btn-block" data-bs-toggle="modal"
                                            data-bs-target="#addProductModal">Add New Product</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body">

                    </div>

                    <!-- Table -->

                    <div class="table-data">
                        @include('products.table')
                    </div>

                </div>

            </div>
        </div>
    </section>




    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-products-form">
                    <div class="modal-body">
                        @if(auth()->user()->business->has_branches == 1)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Branch</label>
                                   <select class="form-select" name="branch_id">
                                    <option value="">--select branch--</option>
                                    @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                   </select>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- Add more rows dynamically -->
                        <div id="productRowsContainer">
                            <!-- Initial row -->
                            <div class="row product-row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="productName" name="productName[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="buyingPrice" class="form-label">Buy Price</label>
                                        <input type="number" step="any" class="form-control" id="buyingPrice"
                                            name="buyingPrice[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="sellingPrice" class="form-label">Sell Price</label>
                                        <input type="number" step="any" class="form-control" name="sellingPrice[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" step="any" class="form-control" name="quantity[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="alertLevel" class="form-label">Alert Level</label>
                                        <input type="number" step="any" class="form-control" name="alertLevel[]">
                                    </div>
                                </div>
                                <div class="col-md-1 align-self-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success" id="addRowBtn">+ Rows</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-primary">Save Products</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('js')

    {{-- dynamic add row --}}
    <script>
        // Function to add more rows dynamically
        document.getElementById('addRowBtn').addEventListener('click', function() {
            var row = document.createElement('div');
            row.classList.add('row', 'product-row');

            var cols = '';
            cols +=
                '<div class="col-md-3"><div class="mb-3"><label for="sellingPrice" class="form-label">Product Name</label><input type="text" class="form-control" name="productName[]"></div></div>';
            cols +=
                '<div class="col-md-2"><div class="mb-3"><label for="sellingPrice" class="form-label">Buy Price</label><input type="number" step="any" class="form-control" name="buyingPrice[]"></div></div>';
            cols +=
                '<div class="col-md-2"><div class="mb-3"><label for="sellingPrice" class="form-label">Sell Price</label><input type="number" step="any" class="form-control" name="sellingPrice[]"></div></div>';
            cols +=
                '<div class="col-md-2"><div class="mb-3"><label for="quantity" class="form-label">Quantity</label><input type="number" step="any" class="form-control" name="quantity[]"></div></div>';
            cols +=
                '<div class="col-md-2"><div class="mb-3"><label for="alertLevel" class="form-label">Alert Level</label><input type="number" step="any" class="form-control" name="alertLevel[]"></div></div>';
            cols +=
                '<div class="col-md-1 align-self-center"><button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button></div>';

            row.innerHTML = cols;

            document.getElementById('productRowsContainer').appendChild(row);
        });

        // Function to remove a row
        document.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('remove-row-btn')) {
                var row = event.target.closest('.product-row');
                row.remove();
            }
        });
    </script>

    {{-- submit form --}}
    <script>
        $(document).ready(function() {
            $('#add-products-form').submit(function(event) {
                event.preventDefault(); 

                var saveBtn = $('#saveBtn');
                saveBtn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                    );
                saveBtn.prop('disabled', true);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });     
                          
                var formData = new FormData(this);

                $.ajax({
                    url: '{{ route('products.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#add-products-form')[0].reset();
                        var modal = $('#addProductModal');
                        modal.modal('hide');
                        toastr.success(response.message, 'Success');
                        $('.table').load(location.href+' .table');

                    },
                    error: function(xhr, status, error) {
                        // Error: Handle the error here
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        // Hide the loading spinner and enable the button
                        saveBtn.html('Save Product');
                        saveBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>



<script>
    $('#search-input').on('input', function() {
        
            var searchQuery = $(this).val(); 

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });  
            $.ajax({
            url: '{{ route("search-products") }}',
            method: 'POST',
            data: { search: searchQuery },
            success: function(response) {
                $('.table-data').html(response);
                if (response.status == 404) {
                    $('.table-data').html('<p class="text-danger text-center">No Data Matched the Query</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    });
</script>

<script>
$(document).ready(function() {
    $('#sort_by').change(function() {
        
        var filterValue = $(this).val();

        $.ajax({
            url: '{{ route('products.filter') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                filter: filterValue
            },
            success: function(response) {
             
                $('.table-data').html(response);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).on('click', '.delete', function () {
    var productId = $(this).data('id');
    var productName = $(this).data('product-name');

    Swal.fire({
        title: 'Delete '+productName+'?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel',
        reverseButtons: true
    }).then(function (result) {
        if (result.isConfirmed) {
            // User confirmed the delete action
            deleteProduct(productId);
        }
    });
});

function deleteProduct(productId) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }); 

    $.ajax({
        url: '{{ route('products.delete') }}',
        type: 'POST',
        dataType: 'json',
        data: {
            'productID': productId,
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('Deleted!', 'The product has been deleted.', 'success').then(function () {
                $('.table-data').html(response);

                });
            } else {
                Swal.fire('Error!', 'An error occurred while deleting the product.', 'error');
            }
        },
        error: function () {
            Swal.fire('Error!', 'An error occurred while making the request.', 'error');
        }
    });
}
</script>


<script>
    $(document).ready(function() {
        $('.toggle-status').click(function(event) {
            event.preventDefault();
            var productId = $(this).data('id');
            var productName = $(this).data('product-name');
            var status = $(this).data('product-status');
            var action = (status == 1) ? 'inactivate' : 'activate';
            var message = (status == 1) ? 'Are you sure you want to inactivate the product: ' + productName + '?' : 'Are you sure you want to activate the product: ' + productName + '?';
// alert(message); return;
            swal({
                title: 'Delete '+productName+'?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel',
                reverseButtons: true
            }).then((confirm) => {
                if (confirm) {

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });                     
                        
                        $.ajax({
                        url: '{{ route('products.toggle-status') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: productId,
                        },
                        success: function(response) {
                            swal('Success', response.message, 'success').then(() => {
                                // Reload the page or perform any other desired action
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            swal('Error', xhr.responseJSON.message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>


@endsection
