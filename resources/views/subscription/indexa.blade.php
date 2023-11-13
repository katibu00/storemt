@extends('layouts.app')
@section('PageTitle', 'Subscription')
@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Subscription Details</div>

                            <div class="card-body">
                                @if ($business->subscription_status === 'trial')
                                    {{-- Display information for businesses that are on trial --}}
                                    @if(now() <= $business->subscription_end_date)
                                        {{-- Trial is still ongoing --}}
                                        <div class="alert alert-info" role="alert">
                                            <strong>Your trial is still active.</strong> It will end on {{ $business->subscription_end_date->format('D, d M, Y') }} ({{ $business->subscription_end_date->diffInDays(now()) }} days from now).
                                            {{-- Countdown progress bar indicating time left for trial --}}
                                            <div class="progress mb-4">
                                                @php
                                                    $daysLeft = $business->subscription_end_date->diffInDays(now());
                                                    $totalDays = $business->subscription_start_date->diffInDays($business->subscription_end_date);
                                                    $progressPercentage = ($totalDays - $daysLeft) / $totalDays * 100;
                                                @endphp
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progressPercentage }}%;" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Trial has ended --}}
                                        <div class="alert alert-warning" role="alert">
                                            <strong>Your trial has ended.</strong> Subscribe now to unlock more features and benefits.
                                            {{-- Subscribe Now button --}}
                                            <a href="{{ route('subscription.choose-plan') }}" class="btn btn-primary mt-3">Subscribe Now</a>
                                        </div>
                                    @endif
                                @elseif ($business->subscription_status === 'active')
                                    {{-- Display subscription details for active subscribers --}}
                                    <h4 class="mb-4">Current Plan: {{ ucfirst($business->subscriptionPlan->name) ?? 'Not subscribed' }}</h4>

                                    {{-- Subscription period details --}}
                                    <p><strong>Start Date:</strong> {{ $business->subscription_start_date->format('Y-m-d') }}</p>
                                    <p>
                                        <strong>End Date:</strong>
                                        <span class="fw-bold text-success">
                                            {{ $business->subscription_end_date->format('Y-m-d') }}
                                            ({{ $business->subscription_end_date->diffInDays(now()) }} days from now)
                                        </span>
                                    </p>

                                    {{-- Countdown progress bar indicating time left --}}
                                    <div class="progress mb-4">
                                        @php
                                            $daysLeft = $business->subscription_end_date->diffInDays(now());
                                            $totalDays = $business->subscription_start_date->diffInDays($business->subscription_end_date);
                                            $progressPercentage = ($totalDays - $daysLeft) / $totalDays * 100;
                                        @endphp
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progressPercentage }}%;" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>

                                    {{-- Display expiration status --}}
                                    @if(now() > $business->subscription_end_date)
                                        <div class="alert alert-danger" role="alert">
                                            <strong>Your subscription has expired.</strong> Please renew to continue enjoying the benefits.
                                        </div>
                                    @endif

                                    {{-- Upgrade and Pay options --}}
                                    <div class="mt-4">
                                        <a href="{{ route('subscription.upgrade') }}" class="btn btn-primary">Upgrade Plan</a>
                                        <form action="{{ route('subscription.pay') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Pay for Current Plan</button>
                                        </form>
                                    </div>
                                @else
                                    {{-- Display information for businesses that are not subscribed --}}
                                    <a href="{{ route('subscription.choose-plan') }}" class="btn btn-primary">Subscribe Now</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
