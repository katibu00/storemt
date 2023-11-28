@extends('layouts.app')

@section('PageTitle', 'Edit Business')

@section('content')
    <section id="content" style="background: rgb(240, 240, 240)">
        <div class="content-wrap">
            <div class="container">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Edit Business: {{ $business->name }}</h5>
                        <a href="{{ route('business.index') }}" class="btn btn-primary">Business List</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('business.update', ['id' => $business->id]) }}" method="POST"
                            enctype="multipart/form-data" class="form-horizontal">
                            @csrf
                            @method('PUT')

                            <h2>Business Information</h2>

                            <div class="form-group row">
                                <label for="logo" class="col-sm-2 col-form-label">Logo (PNG or JPG format only):</label>
                                <div class="col-sm-10">
                                    <img id="logoPreview" src="{{ asset($business->logo) }}"
                                        alt="Business Logo" width="150" height="150">
                                    <input type="file" class="form-control-file" id="logo" name="logo"
                                        accept=".png, .jpg, .jpeg" onchange="previewLogo(event)">
                                    <small class="form-text text-muted">Please upload a PNG or JPG file format for the
                                        logo.</small>
                                    @error('logo')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="business_name" class="col-sm-2 col-form-label">Business Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="business_name" name="business_name"
                                        value="{{ old('business_name', $business->name) }}">
                                    @error('business_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="business_username" class="col-sm-2 col-form-label">Username:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="business_username"
                                        name="business_username" value="{{ old('business_username', $business->username) }}">
                                    @error('business_username')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-10">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="has_multiple_branches"
                                            name="has_multiple_branches" {{ old('has_multiple_branches', $business->has_branches) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_multiple_branches">Has Multiple
                                            Branches?</label>
                                    </div>
                                </div>
                            </div>

                            <h2>Main Branch</h2>

                            <div class="form-group row">
                                <label for="main_branch_name" class="col-sm-2 col-form-label">Branch Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_name"
                                        name="main_branch_name" value="{{ old('main_branch_name', $business->mainBranch->name) }}">
                                    @error('main_branch_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_address" class="col-sm-2 col-form-label">Address:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_address"
                                        name="main_branch_address" value="{{ old('main_branch_address', $business->mainBranch->address) }}">
                                    @error('main_branch_address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_phone" class="col-sm-2 col-form-label">Phone:</label>
                                <div class="col-sm-10">
                                    <input type="tel" class="form-control" id="main_branch_phone"
                                        name="main_branch_phone" value="{{ old('main_branch_phone', $business->mainBranch->phone) }}">
                                    @error('main_branch_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_email" class="col-sm-2 col-form-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" id="main_branch_email"
                                        name="main_branch_email" value="{{ old('main_branch_email', $business->mainBranch->email) }}">
                                    @error('main_branch_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <h2>Proprietor Section</h2>

                            <div class="form-group row">
                                <label for="proprietor_name" class="col-sm-2 col-form-label">Full Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_name"
                                        name="proprietor_name" value="{{ old('proprietor_name', $business->admin->name) }}">
                                    @error('proprietor_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="proprietor_phone" class="col-sm-2 col-form-label">Phone Number:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_phone"
                                        name="proprietor_phone" value="{{ old('proprietor_phone', $business->admin->phone) }}">
                                    @error('proprietor_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="proprietor_email" class="col-sm-2 col-form-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_email"
                                        name="proprietor_email" value="{{ old('proprietor_email', $business->admin->email) }}">
                                    @error('proprietor_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary">Update Business</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function previewLogo(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var logoPreview = document.getElementById('logoPreview');
                logoPreview.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection
