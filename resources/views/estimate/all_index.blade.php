@extends('layouts.app')
@section('PageTitle', 'All Estimates')
@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">

                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <h2 class="text-bold fs-20">All Estimates</h2>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Search by Estimate ID or Note">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                            <div class="form-group">
                                <select class="form-select" id="cashier_id">
                                    <option value="">Sort by Cashier</option>
                                    <option value="all">All</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="table-data">
                            @include('estimate.all_table')
                        </div>
                    </div>
                </div>

                <div class="modal fade addModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="">Mark As Sold </h4>
                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <form action="{{ route('estimate.all.store') }}" method="POST">
                                @csrf
                            <div class="modal-body">
                              
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                           <select class="form-select form-select-sm" id="payment_method" name="payment_method" required>
                                                <option value="">--Payment Method--</option>
                                                <option value="cash">Cash</option>
                                                <option value="transfer">Transfer</option>
                                                <option value="pos">POS</option>
                                                <option value="credit">Credit</option>
                                           </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 customer_div d-none">
                                        <div class="mb-1">
                                           <select class="form-select form-select-sm" id="customer" name="customer" required>
                                                <option value="">-- Customer --</option>
                                                @foreach ($customers as $customer)   
                                                    <option value="{{ @$customer->id }}">{{ @$customer->name }}</option>
                                                @endforeach
                                           </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                          Estimate ID:<span id="estimate_no_span"></span>
                                        </div>
                                        <div class="mb-1">
                                          Amount Payable:<span id="payable"></span>
                                        </div>
                                        <div class="mb-1">
                                          Customer Name:<span id="name"></span>
                                        </div>
                                        <div class="mb-1">
                                          Note:<span id="note"></span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="receipt_no" name="receipt_no">
                                <input type="hidden" id="total_amount" name="total_amount">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary ml-2">Mark as Sold</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>


                <div class="modal">
                    <div id="print">
                        @include('estimate.receipt')
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection

@section('js')

    <script>
      
        function handleSearch() {
            var query = $('#searchInput').val();

            $('.pagination').hide();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '{{ route('estimate.all.search') }}',
                method: 'POST',
                data: {
                    query: query
                },
                success: function(response) {
                    // Empty the table
                    $('.table').empty();

                    // Check if the response is empty
                    if ($(response).find('tbody tr').length > 0) {
                        $('.table').html(response);
                    } else {
                        // Display a message if no rows are found
                        $('.table tbody').empty().append(
                            '<tr><td colspan="9" class="text-center">No results found.</td></tr>');
                        toastr.warning('No results found.');
                    }

                },

                error: function(xhr) {
                    // Handle the error response here
                    console.log(xhr.responseText);
                }
            });
        }
        $('#searchInput').on('input', handleSearch);
    </script>



    <script>
        $(document).ready(function() {
            $('#cashier_id').on('change', function() {

                var cashierId = $('#cashier_id').val();
                $.LoadingOverlay("show")
                $('.pagination').hide();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '{{ route('estimate.all.sort') }}',
                    method: 'POST',
                    data: {
                        cashier_id: cashierId,
                    },
                    success: function(response) {
                        $('.table').empty();
                        $.LoadingOverlay("hide")
                        $('.table').html(response);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });
        });
        $(document).on('change', '#payment_method', function() {

            var payment_method = $('#payment_method').val();

            if (payment_method == 'credit') {
                $('.customer_div').removeClass('d-none');
                $('#customer').attr('required', true);
            } else {
                $('.customer_div').addClass('d-none');
                $('#customer').attr('required', false);
            }

        });
        $(document).on('click', '.saleItem', function(e) {
            e.preventDefault();

            $('#receipt_no').html();
            $('#payable').html();
            $('#customer').html();
            $('#note').html();

            let receipt_no = $(this).data('receipt_no');
            let payable = $(this).data('payable');
            let customer = $(this).data('customer');
            let note = $(this).data('note');
            let total_amount = $(this).data('payable');

            $('#estimate_no_span').html(receipt_no);
            $('#receipt_no').val(receipt_no);
            $('#total_amount').val(total_amount);
            $('#payable').html(payable);
            $('#customer').html(customer);
            $('#note').html(note);


        });
    </script>


    <script>
        function PrintReceiptContent(receipt_no) {

            data = {
                'receipt_no': receipt_no,
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: "{{ route('refresh-receipt-estimate') }}",
                data: data,
                success: function(res) {
                    var html = '';
                    var total = 0;
                    var discount = 0;

                    $.each(res.items, function(key, item) {
                        html +=
                            '<tr style="text-align: center">' +
                            '<td style="text-align: left"><span style="font-size: 12px;">' + item
                            .product.name + '</span></td>' +
                            '<td style="font-size: 12px;">' + item.quantity + '</td>' +
                            '<td style="font-size: 12px;">' + item.price.toLocaleString() + '</td>' +
                            '<td style="font-size: 12px;">' + (item.quantity * item.price)
                            .toLocaleString() + '</td>' +
                            '</tr>';
                        total += item.quantity * item.price;

                        // Parse the discount as an integer and add it to the discount variable
                        discount += parseInt(item.discount);
                    });

                    var totalCal = total;

                    $('#receipt_body').html(html);
                    $('.tran_id').html(res.items[0].receipt_no);
                    $('#cashier_name').html(res.items[0].staff.name);

                    if (discount > 0) {
                        $('#salesdiscount').html('₦' + discount.toLocaleString());
                        $("#salesdiscounttr").show();
                        totalCal = total - discount;
                    } else {
                        $("#salesdiscounttr").hide();
                    }
                    $('#total').html('₦' + totalCal.toLocaleString());

                    var data = document.getElementById('print').innerHTML;

                    myReceipt = window.open("", "myWin", "left=150, top=130,width=300, height=400");

                    myReceipt.screenX = 0;
                    myReceipt.screenY = 0;
                    myReceipt.document.write(data);
                    myReceipt.document.title = "Print Receipt";
                    myReceipt.focus();
                    myReceipt.print();
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status === 419) {
                        Command: toastr["error"](
                            "Session expired. please login again."
                        );

                        setTimeout(() => {
                            window.location.replace('{{ route('login') }}');
                        }, 2000);
                    }
                },
            });


            setTimeout(() => {
                // myReceipt.close();
            }, 8000);
        }
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"
        integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

@endsection
