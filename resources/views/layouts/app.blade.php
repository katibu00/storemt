@php
$route = Route::current()->getName();
$business = App\Models\Business::where('id', auth()->user()->business_id)->first();
@endphp

<!DOCTYPE html>
<html dir="ltr" lang="en-US">

<head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="ukmisau" />
    <!-- Stylesheets -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="/css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="/style.css" type="text/css" />

    <link rel="stylesheet" href="/css/dark.css" type="text/css" />
    <link rel="stylesheet" href="/css/font-icons.css" type="text/css" />
    <link rel="stylesheet" href="/css/animate.css" type="text/css" />
    <link rel="stylesheet" href="/css/magnific-popup.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css"
        integrity="sha512-Mo79lrQ4UecW8OCcRUZzf0ntfMNgpOFR46Acj2ZtWO8vKhBvD79VCp3VOKSzk6TovLg5evL3Xi3u475Q/jMu4g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/css/custom.css" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/colors.php?color=0275d8" type="text/css" />
    @yield('css')
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
    <!-- Bootstrap Select CSS -->
    <link rel="stylesheet" href="/css/components/bs-select.css" type="text/css" />

    <!-- Document Title -->
    <title>@yield('PageTitle') | {{ @$business->name }}</title>
    <link rel="stylesheet" href="/toastr/toastr.min.css">

</head>

<body class="stretched search-overlay">

    <!-- Document Wrapper -->
    <div id="wrapper" class="clearfix">

        <!-- Header -->
        <header id="header" class="full-header header-size-md" data-mobile-sticky="true">
            <div id="header-wrap">
                <div class="container">
                    <div class="header-row">

                        <!-- Logo -->
                        <div id="logo">
                            <a href="#" class="standard-logo"><img src="/{{ @$business->logo }}"></a>
                            <a href="#" class="retina-logo"><img src="/{{ @$business->logo }}"></a>
                        </div>

                        <div class="header-misc ms-0">

                            <!-- Top Account -->
                            <div class="header-misc-icon">
                                <a href="#" id="notifylink" data-bs-toggle="dropdown" data-bs-offset="0,15"
                                    aria-haspopup="true" aria-expanded="false" data-offset="12,12"><i
                                        class="icon-line-bell notification-badge"></i></a>
                                <div class="dropdown-menu dropdown-menu-end py-0 m-0 overflow-auto"
                                    aria-labelledby="notifylink" style="width: 320px; max-height: 300px">
                                    <span
                                        class="dropdown-header border-bottom border-f5 fw-medium text-uppercase ls1">Notifications</span>
                                    <div class="list-group list-group-flush">

                                        <a href="#" class="d-flex list-group-item">
                                            {{-- <i class="icon-line-check badge-icon bg-success text-white me-3 mt-1"></i> --}}
                                            <div class="media-body">
                                                <h5 class="my-0 fw-normal text-muted"><span class="text-dark fw-bold">No
                                                        New Notification</h5>
                                                {{-- <small class="text-smaller">2 days ago</small> --}}
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Account -->
                            <div class="header-misc-icon profile-image">
                                <a href="#" id="profilelink" data-bs-toggle="dropdown" data-bs-offset="0,15"
                                    aria-haspopup="true" aria-expanded="false" data-offset="12,12"><img
                                        class="rounded-circle" src="/default.png"
                                        alt="{{ auth()->user()->name }}"></a>
                                <div class="dropdown-menu dropdown-menu-end py-0 m-0" aria-labelledby="profilelink">
                                    @if(auth()->user()->usertype == 'admin')
                                    <a class="dropdown-item" href="{{ route('business.settings') }}"><i class="icon-line-cog me-2"></i>Settings</a>
                                    <a class="dropdown-item" href="{{ route('sms.compose') }}"><i class="icon-line-mail me-2"></i>Compose SMS</a>
                                    <a class="dropdown-item" href="{{ route('sms.balance') }}"><i class="icon-line-speech-bubble me-2"></i>SMS Balance</a>
                                    <div class="line m-0"></div>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('logout') }}"><i class="icon-line-log-out me-2"></i>Sign Out</a>
                                </div>
                            </div>

                        </div>

                        <div id="primary-menu-trigger">
                            <svg class="svg-trigger" viewBox="0 0 100 100">
                                <path
                                    d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20">
                                </path>
                                <path d="m 30,50 h 40"></path>
                                <path
                                    d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20">
                                </path>
                            </svg>
                        </div>

                        <nav class="primary-menu">

                            <ul class="menu-container">
                                @if (auth()->user()->usertype == 'admin')
                                @include('layouts.admin')
                                @endif
                               @if (auth()->user()->usertype == 'cashier')
                                @include('layouts.cashier')
                                @endif
                               @if (auth()->user()->usertype == 'super')
                                @include('layouts.super')
                                @endif
                            </ul>

                        </nav>

                    </div>
                </div>
            </div>
            <div class="header-wrap-clone"></div>
        </header>
        <!-- Content -->
        @yield('content')

        <!-- Footer -->
        <footer id="footer" class="border-0" style="background-color: #F5F5F5;">
            <div class="line m-0"></div>
            <div id="copyrights" style="background-color: #FFF; padding: 10px;">
                <div class="container clearfix">
                    <div class="w-100 center m-0">
                        <span style="font-size: 12px;">Copyrights &copy; 2023 All Rights Reserved - {{ @$business->name }}</span>
                    </div>
                </div>
            </div>
        </footer>

    </div>

    <!-- Go To Top -->
    <div id="gotoTop" class="icon-angle-up"></div>

    <!-- JavaScripts -->
    <script src="/js/jquery.js"></script>
    <script src="/js/plugins.min.js"></script>

    <!-- TinyMCE Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js">
    </script>

    <!-- Bootstrap Select Plugin -->
    <script src="/js/components/bs-select.js"></script>

    <!-- Select Splitter Plugin -->
    <script src="/js/components/selectsplitter.js"></script>

    <!-- Footer Scripts -->
    <script src="/js/functions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="/toastr/toastr.min.js"></script>
    {!! Toastr::message() !!}

    @yield('js')
</body>

</html>
