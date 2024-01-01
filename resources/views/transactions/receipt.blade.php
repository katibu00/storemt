<!DOCTYPE html>
<html>
<head>
    <style>
        /* Reset default browser styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styles */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        /* Header styles */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
           text-align: center;
        }

        .business-name {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .contact-details {
            font-size: 9px;
            margin-top: 3px;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            padding: 5px;
            text-align: left;
            border-bottom: 1px solid #000;
            font-size: 12px;
        }

        th {
            font-weight: bold;
        }
    </style>
</head>
@php
$user = auth()->user();
@endphp
<body>
    <div class="header">
        <div class="logo">
            <img src="/{{ $user->business->logo }}" width="60" height="60" />
        </div>
       
        <div class="business-name">{{ $user->business->name }} @if($user->business->has_branches == 1) - {{ $user->branch->name }} Branch @endif</div>
        
        <div class="contact-details">
            Address: {{  $user->branch->address }}<br>
            Phone: {{  $user->branch->phone }}<br>
            Email: {{  $user->branch->email }}<br>
            Website: {{  $user->business->website }}
        </div>
        <div style="font-size: 12px; margin-top: 10px;">Ref ID: <span class="tran_id"></span></div>
        <div style="font-size: 13px; margin-top: 5px;margin-bottom: 0px;">Cashier: <span id="cashier_name"></span></div> 
        <div style="font-size: 13px; margin-top: 5px;margin-bottom: 0px;">Customer: <span id="customer_name"></span></div> 
        <div style="font-size: 13px; margin-top: 5px;margin-bottom: 15px;">Paid By: <span id="paid_by"></span></div> 

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="receipt_body">
        </tbody>
        <tfoot>

            <tr id="salesdiscounttr" style="display:none;">
                <td colspan="3" style="text-align: right;">Discount:</td>
                <td id="salesdiscount"></td>
            </tr>
            
            <tr>
                <td colspan="3" style="text-align: right;">Total:</td>
                <td id="total"></td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: center; margin-bottom: 15px;">*** Thank you! ***</div>
    <p>.</p><br>
    <p>.</p><br>
</body>
</html>
