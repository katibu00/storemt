@extends('layouts.app')
@section('PageTitle', 'Subscription')
@section('content')
    <section id="content">
        <div class="content-wrap">
            <div class="container">

                <div class="fancy-title title-border title-center">
                    <h3>Choose a Plan</h3>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="grid-inner">
                            <div class="card rounded-6 overflow-hidden">
                                <div class="card-body p-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#578316"
                                        viewBox="0 0 256 256">
                                        <path
                                            d="M63.81,192.19c-47.89-79.81,16-159.62,151.64-151.64C223.43,176.23,143.62,240.08,63.81,192.19Z"
                                            opacity="0.2"></path>
                                        <path
                                            d="M223.45,40.07a8,8,0,0,0-7.52-7.52C139.8,28.08,78.82,51,52.82,94a87.09,87.09,0,0,0-12.76,49c.57,15.92,5.21,32,13.79,47.85l-19.51,19.5a8,8,0,0,0,11.32,11.32l19.5-19.51C81,210.73,97.09,215.37,113,215.94q1.67.06,3.33.06A86.93,86.93,0,0,0,162,203.18C205,177.18,227.93,116.21,223.45,40.07ZM153.75,189.5c-22.75,13.78-49.68,14-76.71.77l88.63-88.62a8,8,0,0,0-11.32-11.32L65.73,179c-13.19-27-13-54,.77-76.71,22.09-36.47,74.6-56.44,141.31-54.06C210.2,114.89,190.22,167.41,153.75,189.5Z">
                                        </path>
                                    </svg>
                                    <h3 class="fs-6 fw-semibold text-uppercase mb-0 mt-4">Personal</h3>
                                    <h4 class="display-5 fw-bold my-3">$49</h4>
                                    <p class="fw-light">Best for Individual Explorers who wants all the Features at an
                                        Affordable Price.
                                    </p>
                                    <a href="{{ route('subscription.confirm', ['plan' => 'basic']) }}" class="button button-border button-rounded button-large h-bg-dark mx-0 fw-bold d-block">Get Started</a>

                                    <h5 class="fs-6 fw-bold mt-4 mb-3 pt-1">What's Included:</h5>
                                    <ul class="iconlist iconlist-sm fw-medium text-muted mb-0">
                                        <li><i class="bi-check-circle-fill text-success"></i><span>220+ Block
                                                Templates</span></li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Ultimate Form
                                                Processor</span></li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>100+ Home Demos</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>1500+ UI Elements</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="grid-inner">
                            <div class="card rounded-6 overflow-hidden">
                                <div class="card-body p-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#578316"
                                        viewBox="0 0 256 256">
                                        <path
                                            d="M138.54,141.46C106.62,88.25,149.18,35.05,239.63,40.37,245,130.82,191.75,173.39,138.54,141.46ZM16.26,80.26c-3.8,64.61,34.21,95,72.21,72.21C111.27,114.47,80.87,76.46,16.26,80.26Z"
                                            opacity="0.2"></path>
                                        <path
                                            d="M247.63,39.89a8,8,0,0,0-7.52-7.52c-51.76-3-93.32,12.74-111.18,42.22-11.8,19.48-11.78,43.16-.16,65.74a71.37,71.37,0,0,0-14.17,26.95L98.33,151c7.82-16.33,7.52-33.36-1-47.49C84.09,81.73,53.62,70,15.79,72.27a8,8,0,0,0-7.52,7.52c-2.23,37.83,9.46,68.3,31.25,81.5A45.82,45.82,0,0,0,63.44,168,54.58,54.58,0,0,0,87,162.33l25,25V216a8,8,0,0,0,16,0V186.51a55.61,55.61,0,0,1,12.27-35,73.91,73.91,0,0,0,33.31,8.4,60.9,60.9,0,0,0,31.83-8.86C234.89,133.21,250.67,91.65,247.63,39.89ZM86.06,138.74l-24.41-24.4a8,8,0,0,0-11.31,11.31l24.41,24.41c-9.61,3.18-18.93,2.39-26.94-2.46C32.47,138.31,23.79,116.32,24,88c28.31-.25,50.31,8.47,59.6,23.81C88.45,119.82,89.24,129.14,86.06,138.74Zm111.06-1.36c-13.4,8.11-29.15,8.73-45.15,2l53.69-53.7a8,8,0,0,0-11.31-11.32L140.65,128c-6.76-16-6.15-31.76,2-45.15,13.94-23,47-35.8,89.33-34.83C232.94,90.34,220.14,123.44,197.12,137.38Z">
                                        </path>
                                    </svg>
                                    <h3 class="fs-6 fw-semibold text-uppercase mb-0 mt-4">Team</h3>
                                    <h4 class="display-5 fw-bold my-3">$129</h4>
                                    <p class="fw-light">Best for Team Players who like to work together and get things done.
                                    </p>
                                    <a href="#"
                                        class="button button-border button-rounded button-large h-bg-dark mx-0 fw-bold d-block">Get
                                        Started</a>
                                    <h5 class="fs-6 fw-bold mt-4 mb-3 pt-1">What's Included:</h5>
                                    <ul class="iconlist iconlist-sm fw-medium text-muted mb-0">
                                        <li><i class="bi-plus-circle-fill text-contrast-1000"></i><span>Everything in
                                                Personal
                                                Plan</span></li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>70+ Niche Demos</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>One-Page Module</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>100+ Shortcodes</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>1350+ Templates</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Google Maps
                                                Support</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="grid-inner">
                            <div class="card rounded-6 overflow-hidden">
                                <div class="card-body p-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#578316"
                                        viewBox="0 0 256 256">
                                        <path
                                            d="M232,127.82a64,64,0,0,1-99.52,53.41,8,8,0,0,0-9,0A64,64,0,1,1,61.25,69.86a8,8,0,0,0,4-4.17,68,68,0,0,1,125.44,0,8,8,0,0,0,4,4.17A64,64,0,0,1,232,127.82Z"
                                            opacity="0.2"></path>
                                        <path
                                            d="M198.1,62.6a76,76,0,0,0-140.2,0A72.29,72.29,0,0,0,16,127.8C15.89,166.62,47.36,199,86.14,200A71.68,71.68,0,0,0,120,192.49V232a8,8,0,0,0,16,0V192.49A71.45,71.45,0,0,0,168,200l1.86,0c38.78-1,70.25-33.36,70.14-72.18A72.26,72.26,0,0,0,198.1,62.6ZM169.45,184a55.7,55.7,0,0,1-32.52-9.4q-.47-.3-.93-.57V132.94l43.58-21.78a8,8,0,1,0-7.16-14.32L136,115.06V88a8,8,0,0,0-16,0v51.06L83.58,120.84a8,8,0,1,0-7.16,14.32L120,156.94V174c-.31.18-.62.37-.92.57A55.73,55.73,0,0,1,86.55,184a56,56,0,0,1-22-106.86,15.9,15.9,0,0,0,8.05-8.33,60,60,0,0,1,110.7,0,15.9,15.9,0,0,0,8.05,8.33,56,56,0,0,1-22,106.86Z">
                                        </path>
                                    </svg>
                                    <h3 class="fs-6 fw-semibold text-uppercase mb-0 mt-4">Agency</h3>
                                    <h4 class="display-5 fw-bold my-3">$299</h4>
                                    <p class="fw-light">Best for Large Groups who want to have all the superpowers at a
                                        simple price.
                                    </p>
                                    <a href="#"
                                        class="button button-border button-rounded button-large h-bg-dark mx-0 fw-bold d-block">Get
                                        Started</a>
                                    <h5 class="fs-6 fw-bold mt-4 mb-3 pt-1">What's Included:</h5>
                                    <ul class="iconlist iconlist-sm fw-medium text-muted mb-0">
                                        <li><i class="bi-plus-circle-fill text-contrast-1000"></i><span>Everything in Team
                                                Plan</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Website Builder
                                                Module</span></li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>eCommerce Module</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>APIs &amp;
                                                Webhooks</span></li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Multi-Use License</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Priority Support</span>
                                        </li>
                                        <li><i class="bi-check-circle-fill text-success"></i><span>Github Access</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
