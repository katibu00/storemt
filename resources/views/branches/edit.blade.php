@extends('layouts.app')

@section('PageTitle', 'Edit Branch')

@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">

                <!-- Display validation errors, if any -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Edit Branch Form -->
                <form action="{{ route('branches.update', $branch->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name" class="col-form-label">Branch Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $branch->name) }}">
                    </div>
                    <div class="form-group">
                        <label for="address" class="col-form-label">Branch Address:</label>
                        <textarea class="form-control" id="address" name="address">{{ old('address', $branch->address) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Phone Number:</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}">
                    </div>
                    <div class="form-group">
                        <label for="email" class "col-form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $branch->email) }}">
                    </div>
                    <div class="form-group">
                        <label for="manager_id" class="col-form-label">Manager:</label>
                        <select class="form-select" name="manager_id">
                            <option value=""></option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('manager_id', $branch->manager_id) == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Branch</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
@endsection
