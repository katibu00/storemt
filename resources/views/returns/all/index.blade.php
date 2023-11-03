@extends('layouts.app')
@section('PageTitle', 'All Returns')
@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">

                <div class="card">
                    <!-- Default panel contents -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="text-bold fs-20">All Returns</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-data">
                            @include('returns.all.table')
                        </div>
                    </div>
                </div>

                <div class="modal">
                    <div id="print">
                        @include('returns.receipt')
                    </div>
                </div>

            </div>
        </div>
    </section><!-- #content end -->

@endsection

@section('js')

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
                url: "{{ route('refresh-receipt-return') }}",
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
                        toastr.options = {
                            closeButton: false,
                            debug: false,
                            newestOnTop: false,
                            progressBar: false,
                            positionClass: "toast-top-right",
                            preventDuplicates: false,
                            onclick: null,
                            showDuration: "300",
                            hideDuration: "1000",
                            timeOut: "5000",
                            extendedTimeOut: "1000",
                            showEasing: "swing",
                            hideEasing: "linear",
                            showMethod: "fadeIn",
                            hideMethod: "fadeOut",
                        };
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
