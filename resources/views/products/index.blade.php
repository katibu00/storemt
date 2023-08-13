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
    @include('products.addModal')
    @include('products.editModal')
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
                        $('.table').load(location.href + ' .table');
                    },
                    error: function(response) {
                        var errors = response.responseJSON;
                        var errorMessage = errors.message;
                        toastr.error(errorMessage, 'Error');
                    },
                    complete: function() {
                        saveBtn.html('Save Product');
                        saveBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"
        integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>



    <script>
        $('#search-input').on('input', function() {
            var searchQuery = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '{{ route('search-products') }}',
                method: 'POST',
                data: {
                    search: searchQuery
                },
                success: function(response) {
                    $('.table-data').html(response);
                    if (response.status == 404) {
                        $('.table-data').html(
                            '<p class="text-danger text-center">No Data Matched the Query</p>');
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
        $(document).on('click', '.delete', function() {
            var productId = $(this).data('id');
            var productName = $(this).data('product-name');

            Swal.fire({
                title: 'Delete ' + productName + '?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
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
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'The product has been deleted.', 'success').then(function() {
                            $('.table-data').html(response);

                        });
                    } else {
                        Swal.fire('Error!', 'An error occurred while deleting the product.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'An error occurred while making the request.', 'error');
                }
            });
        }
    </script>


<script>
    $(document).ready(function() {
        $('.toggle-status').click(function(event) {
            event.preventDefault();
            
            // Extract data attributes from the clicked element
            var productId = $(this).data('id');
            var productName = $(this).data('product-name');
            var status = $(this).data('product-status');
            var action = (status == 1) ? 'inactivate' : 'activate';
            var message = (status == 1) ? 'Are you sure you want to inactivate the product: ' + productName + '?' :
                                            'Are you sure you want to activate the product: ' + productName + '?';

            // Show a confirmation dialog using SweetAlert
            swal({
                title: 'Toggle Status for ' + productName + '?',
                text: message,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    // Set up CSRF token for AJAX requests
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    // Send an AJAX request to toggle the product status
                    $.ajax({
                        url: '{{ route('products.toggle-status') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: productId,
                        },
                        success: function(response) {
                            // Display success message and reload page
                            swal('Success', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            // Display error message
                            swal('Error', xhr.responseJSON.message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>





<script>
    $(document).on('click', '.edit', function() {
        var productId = $(this).data('id');
        var productName = $(this).data('product-name');
        var buyingPrice = $(this).data('buying-price');
        var sellingPrice = $(this).data('selling-price');
        var quantity = $(this).data('quantity');
        var alertLevel = $(this).data('alert-level');

        // Set values in the edit modal
        $('#edit-product-id').val(productId);
        $('#edit-product-name').val(productName);
        $('#edit-buying-price').val(buyingPrice);
        $('#edit-selling-price').val(sellingPrice);
        $('#edit-quantity').val(quantity);
        $('#edit-alert-level').val(alertLevel);

        // Show the edit modal
        $('#editProductModal').modal('show');
    });

    $('#edit-product-form').submit(function(event) {
        event.preventDefault();

        var productName = $('#edit-product-name').val();
        var productId = $('#edit-product-id').val();
        var buyingPrice = $('#edit-buying-price').val();
        var sellingPrice = $('#edit-selling-price').val();
        var quantity = $('#edit-quantity').val();
        var alertLevel = $('#edit-alert-level').val();

        // Disable the "Save Changes" button and show spinner
        var saveChangesBtn = $('#saveChangesBtn');
        saveChangesBtn.prop('disabled', true);
        saveChangesBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

        // Perform AJAX request to update the product
        $.ajax({
            url: '{{ route('products.update') }}',
            type: 'POST',
            data: {
                productId,
                productName,
                buying_price: buyingPrice,
                selling_price: sellingPrice,
                quantity: quantity,
                alert_level: alertLevel,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Close the edit modal
                $('#editProductModal').modal('hide');

                // Show success message
                toastr.success(response.message, 'Success');

                // Reload the table data
                $('.table-data').load(location.href + ' .table');
            },
            error: function(response) {
                var errors = response.responseJSON;
                var errorMessage = errors.message;
                toastr.error(errorMessage, 'Error');
            },
            complete: function() {
                // Enable the "Save Changes" button and restore its text
                saveChangesBtn.prop('disabled', false);
                saveChangesBtn.html('Save Changes');
            }
        });
    });
</script>



@endsection
