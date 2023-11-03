@extends('layouts.app')
@section('PageTitle', 'Login Logs')
@section('content')

<section id="content">
    <div class="content-wrap">
        <div class="container">
            <h2>Login Logs</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Business Name</th>
                        <th>User Name</th>
                        <th>Time Logged In</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loginLogs as $log)
                        <tr>
                            <td>{{ $log->business->name }}</td>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ $log->login_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $loginLogs->links() }} 

        </div>
    </div>
</section>
@endsection
