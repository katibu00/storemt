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
                      
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Logo</th>
                                    <th>Username</th>
                                    <th>Admin Phone</th>
                                    <th>Has Multiple Branches</th>
                                    <th>Subscription Status</th>
                                    <th>Subscription Start Date</th>
                                    <th>Subscription End Date</th>
                                    <th>Billing Cycle</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($businesses as $key => $business)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $business->name }}</td>
                                        <td>
                                            @if($business->logo)
                                                <img src="{{ asset($business->logo) }}" alt="Business Logo" width="50" height="50">
                                            @else
                                                No Logo
                                            @endif
                                        </td>
                                        <td>{{ $business->username }}</td>
                                        <td>{{ $business->admin->phone ?? 'N/A' }}</td>
                                        <td>{{ $business->has_branches ? 'Yes' : 'No' }}</td>
                                        <td>{{ ucfirst($business->subscription_status) }}</td>
                                        <td>{{ $business->subscription_start_date ?? 'N/A' }}</td>
                                        <td>{{ $business->subscription_end_date ?? 'N/A' }}</td>
                                        <td>{{ $business->billing_cycle ?? 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                                        id="actionDropdown{{ $business->id }}" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $business->id }}">
                                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                           data-bs-target="#manualFundingModal"
                                                           onclick="setManualFundingBusinessId({{ $business->id }}, '{{ $business->name }}')">
                                                            <i class="fas fa-money-bill"></i> Manual Funding</a></li>
                                                    <li><a class="dropdown-item"
                                                           href="{{ route('business.delete', ['id' => $business->id]) }}"><i
                                                                class="fas fa-trash"></i> Delete Business</a></li>
                                                    <li><a class="dropdown-item"
                                                           href="{{ route('business.suspend', ['id' => $business->id]) }}"><i
                                                                class="fas fa-pause"></i> Suspend Business</a></li>
                                                    <li><a class="dropdown-item"
                                                           href="{{ route('business.edit', ['id' => $business->id]) }}"><i
                                                                class="fas fa-edit"></i> Edit Business</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Manual Funding Modal -->
    <div class="modal fade" id="manualFundingModal" tabindex="-1" aria-labelledby="manualFundingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="manualFundingModalLabel">Manual Funding for Business: <span id="businessName"></span></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="manualFundingForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="plan">Choose Plan</label>
                            <select class="form-select" id="plan" name="plan_id" required>
                              @foreach ($subsplans as $subsplan)
                              <option value="{{ $subsplan->id }}">{{ ucfirst($subsplan->name) }}</option>
                              @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="months">Number of Months</label>
                            <input type="number" class="form-control" id="months" name="months" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
      function setManualFundingBusinessId(businessId, businessName) {
          // Set the business ID in the form action
          var form = document.getElementById('manualFundingForm');
          form.action = '/business/manual-funding-submit/' + businessId;

          // Set the business name in the modal title
          document.getElementById('businessName').innerText = businessName;
      }
  </script>
@endsection
