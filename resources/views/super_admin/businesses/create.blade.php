@extends('layouts.app')

@section('PageTitle', 'Create New Business')

@section('content')
    <section id="content" style="background: rgb(240, 240, 240)">
        <div class="content-wrap">
            <div class="container">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Create a New Business</h5>
                        <a href="{{ route('business.create') }}" class="btn btn-primary">Business List</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('business.store') }}" method="POST" enctype="multipart/form-data"
                            class="form-horizontal">
                            @csrf

                            <!-- Business Information Section -->
                            <h2>Business Information</h2>

                            <div class="form-group row">
                                <label for="logo" class="col-sm-2 col-form-label">Logo (PNG or JPG format only):</label>
                                <div class="col-sm-10">
                                    <img id="logoPreview" src="{{ asset('thumbnail.png') }}"
                                        alt="Default Logo" width="150" height="150">
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
                                        value="{{ old('business_name') }}">
                                    @error('business_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="business_username" class="col-sm-2 col-form-label">Username:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="business_username"
                                        name="business_username" value="{{ old('business_username') }}">
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
                                            name="has_multiple_branches" {{ old('has_multiple_branches') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_multiple_branches">Has Multiple
                                            Branches?</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Branch Section -->
                            <h2>Main Branch</h2>

                            <div class="form-group row">
                                <label for="main_branch_name" class="col-sm-2 col-form-label">Branch Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_name"
                                        name="main_branch_name" value="{{ old('main_branch_name') }}">
                                    @error('main_branch_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_address" class="col-sm-2 col-form-label">Address:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_address"
                                        name="main_branch_address" value="{{ old('main_branch_address') }}">
                                    @error('main_branch_address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_phone" class="col-sm-2 col-form-label">Phone:</label>
                                <div class="col-sm-10">
                                    <input type="tel" class="form-control" id="main_branch_phone"
                                        name="main_branch_phone" value="{{ old('main_branch_phone') }}">
                                    @error('main_branch_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_email" class="col-sm-2 col-form-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" id="main_branch_email"
                                        name="main_branch_email" value="{{ old('main_branch_email') }}">
                                    @error('main_branch_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Add more sections or fields as needed -->

                            <!-- Admin Section -->
                            <h2>Proprietor Section</h2>

                            <div class="form-group row">
                                <label for="proprietor_name" class="col-sm-2 col-form-label">Full Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_name"
                                        name="proprietor_name" value="{{ old('proprietor_name') }}">
                                    @error('proprietor_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="proprietor_phone" class="col-sm-2 col-form-label">Phone Number:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_phone"
                                        name="proprietor_phone" value="{{ old('proprietor_phone') }}">
                                    @error('proprietor_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="proprietor_email" class="col-sm-2 col-form-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="proprietor_email"
                                        name="proprietor_email" value="{{ old('proprietor_email') }}">
                                    @error('proprietor_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary">Create Business</button>
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
