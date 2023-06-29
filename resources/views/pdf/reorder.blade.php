<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Store Name</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .details {
            margin-bottom: 20px;
        }
        
        .details-table {
            width: 100%;
        }
        
        .details-table td {
            vertical-align: top;
            width: 50%;
        }
        
        .details-table td.right-column {
            text-align: right;
        }
        
        .details-table td p {
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="title">{{ auth()->user()->business->name }}</div>
    
    <table class="details-table">
        <tr>
            <td>
                <p>Branch: {{ auth()->user()->branch->name }}</p>
                <p>Phone: {{ auth()->user()->branch->phone }}</p>
                <p>Email: {{ auth()->user()->branch->email }}</p>
                <p>Website: {{ auth()->user()->branch->website }}</p>
            </td>
            <td class="right-column">
                <p>Order Number: {{ $records[0]->reorder_no }}</p>
                <p>Supplier Name: {{ $records[0]->supplier->name }}</p>
                <p>Date Issued: {{ $records[0]->created_at->format('Y-m-d') }}</p>
            </td>
        </tr>
    </table>
    
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Ordered Quantity</th>
                {{-- <th>Old Price</th> --}}
                {{-- <th>Price Change</th> --}}
                <th>New Price</th>
                <th>Sub total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $reorder)
                <tr>
                    <td>{{ $reorder->product->name }}</td>
                    <td>{{ $reorder->quantity }}</td>
                    {{-- <td>{{ $reorder->buying_price }}</td> --}}
                    {{-- <td><input type="checkbox"></td> --}}
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
