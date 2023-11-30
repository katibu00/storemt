@extends('layouts.app')
@section('PageTitle', 'Super Admin Home')
@section('content')

<section id="content" style="background: rgb(240, 240, 240)">
    <div class="content-wrap">
        <div class="container">
            <div class="dashboard">
                <h2>Business Activities Overview</h2>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Registered Businesses</h5>
                                <p class="card-text">{{ $registeredBusinesses }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Registered Users</h5>
                                <p class="card-text">{{ $registeredUsers }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Login Count Today</h5>
                                <p class="card-text">{{ $loginCountToday }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Sales Count Today</h5>
                                <p class="card-text">{{ $salesCountToday }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Business Name</th>
                            <th>Total Products</th>
                            <th>Gross Sales Yesterday</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($insights as $insight)
                            <tr>
                                <td>{{ $insight['business']->name }}</td>
                                <td>{{ $insight['totalProducts'] }}</td>
                                <td>{{ $insight['grossSalesYesterday'] }}</td>
                                <td>
                                    @if($insight['businessIsActive'])
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
