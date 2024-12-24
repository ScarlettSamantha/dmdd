@extends('layout')

@section('title','Dashboard')

@section('content')
<div class="dashboard-container p-4 lg:p-4">
    <div class="flex w-full flex-col lg:flex-row">
        <div class="card bg-base-300 rounded-box grid h-40 flex-grow place-items-center">content</div>
        <div class="divider divider-primary lg:divider-horizontal"></div>
        <div class="card bg-base-300 rounded-box grid h-40 flex-grow place-items-center">content</div>
        <div class="divider divider-primary lg:divider-horizontal"></div>
        <div class="card bg-base-300 rounded-box grid h-40 flex-grow place-items-center">content</div>
    </div>
    <div class="divider divider-primary "></div>
    <div class="flex w-full flex-col lg:flex-row library-menu-container">
        <div class="w-1/10 sticky top-4 library-menu">
            <ul class="menu bg-base-200 rounded-box shadow-md">
                <li>
                    <a class="tooltip tooltip-right" data-tip="Home">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </a>
                </li>
                <div class="divider divider-primary"></div>
                <li>
                    <a class="tooltip tooltip-right" data-tip="Details">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                </li>
                <div class="divider divider-primary"></div>
                <li class="indicator">
                    <span class="indicator-item badge badge-secondary"><span class="indicator-text">99</span></span>
                    <a class="tooltip tooltip-right" data-tip="Stats">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </a>
                   
                </li>
            </ul>
        </div>
        <!-- Content Area -->
        <div class="flex-grow content-container">
            <div class="flex-grow bg-base-300 rounded-box h-auto library-container">@include('partials.dashboard.library-table')</div>
        </div>
    </div>
</div>
@endsection
