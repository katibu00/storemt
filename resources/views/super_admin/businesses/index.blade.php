@extends('layouts.app')
@section('PageTitle', 'Super Admin Home')
@section('content')
    <section id="content" style="background: rgb(240, 240, 240)">
        <div class="content-wrap">
            <div class="container">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Business List</h5>
                    <a href="{{ route('business.create') }}" class="btn btn-primary">Add New Business</a>
                  </div>
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
                          <th>Logo</th>
                          <th>Address</th>
                          <th>Phone</th>
                          <th>Email</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- Loop through businesses and populate the table rows -->
                        @foreach($businesses as $business)
                        <tr>
                          <td>{{ $business->id }}</td>
                          <td>{{ $business->name }}</td>
                          <td>{{ $business->logo }}</td>
                          <td>{{ $business->address }}</td>
                          <td>{{ $business->phone }}</td>
                          <td>{{ $business->email }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
        </div>
    </section>
@endsection