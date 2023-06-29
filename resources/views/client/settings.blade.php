@extends('layouts.app')
@section('PageTitle', 'Business Settings')
@section('content')
@php
    $business = auth()->user()->business;
    $branch = auth()->user()->branch;
@endphp
    <section id="content" style="background: rgb(240, 240, 240)">
        <div class="content-wrap">
            <div class="container">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Business Basic Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('business.settings') }}" method="POST" enctype="multipart/form-data"
                            class="form-horizontal">
                            @csrf

                            <!-- Business Information Section -->
                            <h2>Business Information</h2>

                            <div class="form-group row">
                                <label for="logo" class="col-sm-2 col-form-label">Logo (PNG or JPG format only):</label>
                                <div class="col-sm-10">
                                    <img id="logoPreview" src="@if($business->logo != null){{ asset($business->logo) }} @else {{ asset('thumbnail.png') }} @endif"
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
                                    <input type="text" class="form-control" id="business_name" value="{{ $business->name }}" name="business_name">
                                    @error('business_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="business_username" class="col-sm-2 col-form-label">Username:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="business_username"
                                        name="business_username" value="{{ $business->username }}" readonly>
                                    @error('business_username')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <!-- Main Branch Section -->
                            <h2>Main Branch</h2>

                            <div class="form-group row">
                                <label for="main_branch_name" class="col-sm-2 col-form-label">Branch Name:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_name"
                                        name="main_branch_name" value="{{ $branch->name }}">
                                    @error('main_branch_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_address" class="col-sm-2 col-form-label">Address:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="main_branch_address"
                                        name="main_branch_address" value="{{ $branch->address }}">
                                    @error('main_branch_address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_phone" class="col-sm-2 col-form-label">Phone:</label>
                                <div class="col-sm-10">
                                    <input type="tel" class="form-control" id="main_branch_phone"
                                        name="main_branch_phone" value="{{ $branch->phone }}">
                                    @error('main_branch_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="main_branch_email" class="col-sm-2 col-form-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" id="main_branch_email"
                                        name="main_branch_email" value="{{ $branch->email }}">
                                    @error('main_branch_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Add more sections or fields as needed -->

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
