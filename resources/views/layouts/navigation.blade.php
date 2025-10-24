@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\User;
$usrhandl = Auth::user()->littlelink_name;
@endphp
<!doctype html>
@include("layouts.lang")
<html>
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{{env("APP_NAME")}}</title>

      <script src="{{asset("assets/js/detect-dark-mode.js")}}"></script>
	  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
      
      <base href="{{url()->current()}}" />

	  @include("layouts.analytics")
	  @stack("sidebar-stylesheets")
    @include("layouts.notifications")

    @php
    // Update the "updated_at" timestamp for the currently authenticated user
    if (auth()->check()) {
        $user = auth()->user();
        $user->touch();
    }
    @endphp

      <!-- Favicon -->
      @if(file_exists(base_path("assets/linkstack/images/").findFile("favicon")))
      <link rel="icon" type="image/png" href="{{ asset("assets/linkstack/images/".findFile("favicon")) }}">
      @else
      <link rel="icon" type="image/svg+xml" href="{{ asset("assets/linkstack/images/logo.svg") }}">
      @endif
      
      <!-- Library / Plugin Css Build -->
      <link rel="stylesheet" href="{{asset("assets/css/core/libs.min.css")}}" />
      
      <!-- Aos Animation Css -->
      <link rel="stylesheet" href="{{asset("assets/vendor/aos/dist/aos.css")}}" />
      
      @include("layouts.fonts")
      
      <!-- Hope Ui Design System Css -->
      <link rel="stylesheet" href="{{asset("assets/css/hope-ui.min.css")}}" />
      
      
      <link rel="stylesheet" href="{{asset("assets/css/custom.min.css?v=2.0.0")}}" />
      
      <!-- Dark Css -->
      <link rel="stylesheet" href="{{asset("assets/css/dark.min.css")}}" />
      
      
            @if(file_exists(base_path("assets/dashboard-themes/dashboard.css")))
      <link rel="stylesheet" href="{{asset("assets/dashboard-themes/dashboard.css")}}" />
      @else
      <link rel="stylesheet" href="{{asset("assets/css/customizer.min.css")}}" />
      @endif
      
      <!-- RTL Css -->
      <link rel="stylesheet" href="{{asset("assets/css/rtl.min.css")}}" />
      
	  <meta name="csrf-token" content="{{ csrf_token() }}">
	   <link rel="stylesheet" href="{{ asset("assets/linkstack/css/hover-min.css") }}">
	  <link rel="stylesheet" href="{{ asset("assets/linkstack/css/animate.css") }}">

	  <link rel="stylesheet" href="{{ asset("assets/external-dependencies/bootstrap-icons.css") }}">

  </head>
  <body class="dual-compact">
    <!-- loader Start -->
    <div id="loading">
      <div class="loader simple-loader">
          <div class="loader-body"></div>
      </div>    </div>
    <!-- loader END -->
    
    <aside id="as1" class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all ">
        <div class="sidebar-header d-flex align-items-center justify-content-start" >
            <a href="{{ route("panelIndex") }}" class="navbar-brand">
                
                <!--Logo start-->
                <div class="logo-main">
                @if(file_exists(base_path("assets/linkstack/images/").findFile("avatar")))
                <div class="logo-normal">
                  <img class="img logo" src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" style="width:auto;height:30px;">
              </div>
              <div class="logo-mini">
                <img class="img logo" src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" style="width:auto;height:30px;">
              </div>
                @else
                <div class="logo-normal">
                  <img class="img logo" type="image/svg+xml" src="{{ asset("assets/linkstack/images/logo.svg") }}" width="30px" height="30px">
              </div>
              <div class="logo-mini">
                <img class="img logo" type="image/svg+xml" src="{{ asset("assets/linkstack/images/logo.svg") }}" width="30px" height="30px">
              </div>
                @endif
                </div>
                <!--logo End-->
                
                
            </a>
            
			
			
			
			<div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </i>
            </div>
			
			
			
			
			
			
        </div>
        <div class="sidebar-body pt-0 data-scrollbar">
            <div class="sidebar-list">
                <!-- Sidebar Menu Start -->
                <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">{{__("messages.Home")}}</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::segment(1) == "dashboard" ? "active" : "bg-soft-primary"}}" aria-current="page" href="{{ route("panelIndex") }}">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                  <path fill-rule="evenodd" clip-rule="evenodd" d="M7.33049 2.00049H16.6695C20.0705 2.00049 21.9905 3.92949 22.0005 7.33049V16.6705C22.0005 20.0705 20.0705 22.0005 16.6695 22.0005H7.33049C3.92949 22.0005 2.00049 20.0705 2.00049 16.6705V7.33049C2.00049 3.92949 3.92949 2.00049 7.33049 2.00049ZM12.0495 17.8605C12.4805 17.8605 12.8395 17.5405 12.8795 17.1105V6.92049C12.9195 6.61049 12.7705 6.29949 12.5005 6.13049C12.2195 5.96049 11.8795 5.96049 11.6105 6.13049C11.3395 6.29949 11.1905 6.61049 11.2195 6.92049V17.1105C11.2705 17.5405 11.6295 17.8605 12.0495 17.8605ZM16.6505 17.8605C17.0705 17.8605 17.4295 17.5405 17.4805 17.1105V13.8305C17.5095 13.5095 17.3605 13.2105 17.0895 13.0405C16.8205 12.8705 16.4805 12.8705 16.2005 13.0405C15.9295 13.2105 15.7805 13.5095 15.8205 13.8305V17.1105C15.8605 17.5405 16.2195 17.8605 16.6505 17.8605ZM8.21949 17.1105C8.17949 17.5405 7.82049 17.8605 7.38949 17.8605C6.95949 17.8605 6.59949 17.5405 6.56049 17.1105V10.2005C6.53049 9.88949 6.67949 9.58049 6.95049 9.41049C7.21949 9.24049 7.56049 9.24049 7.83049 9.41049C8.09949 9.58049 8.25049 9.88949 8.21949 10.2005V17.1105Z" fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">{{__("messages.Dashboard")}}</span>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link {{ Request::segment(2) == "add-link" ? "active" : ""}}" aria-current="page" href="{{ url("/studio/add-link") }}">
                            <i class="icon">
                                 <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M7.33 2H16.66C20.06 2 22 3.92 22 7.33V16.67C22 20.06 20.07 22 16.67 22H7.33C3.92 22 2 20.06 2 16.67V7.33C2 3.92 3.92 2 7.33 2ZM12.82 12.83H15.66C16.12 12.82 16.49 12.45 16.49 11.99C16.49 11.53 16.12 11.16 15.66 11.16H12.82V8.34C12.82 7.88 12.45 7.51 11.99 7.51C11.53 7.51 11.16 7.88 11.16 8.34V11.16H8.33C8.11 11.16 7.9 11.25 7.74 11.4C7.59 11.56 7.5 11.769 7.5 11.99C7.5 12.45 7.87 12.82 8.33 12.83H11.16V15.66C11.16 16.12 11.53 16.49 11.99 16.49C12.45 16.49 12.82 16.12 12.82 15.66V12.83Z" fill="currentColor"></path>
                                    <circle cx="18" cy="11.8999" r="1" fill="currentColor"></circle>
                                </svg>
                                                         
                            </i>
                            <span class="item-name">{{__("messages.Add Link")}}</span>
                        </a>
                    </li>
                    @if(auth()->user()->role == "admin")
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">{{__("messages.Administration")}}</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#utilities-error" role="button" aria-expanded="false" aria-controls="utilities-error">
                            <i class="icon">
								<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M20.4023 13.58C20.76 13.77 21.036 14.07 21.2301 14.37C21.6083 14.99 21.5776 15.75 21.2097 16.42L20.4943 17.62C20.1162 18.26 19.411 18.66 18.6855 18.66C18.3278 18.66 17.9292 18.56 17.6022 18.36C17.3365 18.19 17.0299 18.13 16.7029 18.13C15.6911 18.13 14.8429 18.96 14.8122 19.95C14.8122 21.1 13.872 22 12.6968 22H11.3069C10.1215 22 9.18125 21.1 9.18125 19.95C9.16081 18.96 8.31259 18.13 7.30085 18.13C6.96361 18.13 6.65702 18.19 6.40153 18.36C6.0745 18.56 5.66572 18.66 5.31825 18.66C4.58245 18.66 3.87729 18.26 3.49917 17.62L2.79402 16.42C2.4159 15.77 2.39546 14.99 2.77358 14.37C2.93709 14.07 3.24368 13.77 3.59115 13.58C3.87729 13.44 4.06125 13.21 4.23498 12.94C4.74596 12.08 4.43937 10.95 3.57071 10.44C2.55897 9.87 2.23194 8.6 2.81446 7.61L3.49917 6.43C4.09191 5.44 5.35913 5.09 6.38109 5.67C7.27019 6.15 8.425 5.83 8.9462 4.98C9.10972 4.7 9.20169 4.4 9.18125 4.1C9.16081 3.71 9.27323 3.34 9.4674 3.04C9.84553 2.42 10.5302 2.02 11.2763 2H12.7172C13.4735 2 14.1582 2.42 14.5363 3.04C14.7203 3.34 14.8429 3.71 14.8122 4.1C14.7918 4.4 14.8838 4.7 15.0473 4.98C15.5685 5.83 16.7233 6.15 17.6226 5.67C18.6344 5.09 19.9118 5.44 20.4943 6.43L21.179 7.61C21.7718 8.6 21.4447 9.87 20.4228 10.44C19.5541 10.95 19.2475 12.08 19.7687 12.94C19.9322 13.21 20.1162 13.44 20.4023 13.58ZM9.10972 12.01C9.10972 13.58 10.4076 14.83 12.0121 14.83C13.6165 14.83 14.8838 13.58 14.8838 12.01C14.8838 10.44 13.6165 9.18 12.0121 9.18C10.4076 9.18 9.10972 10.44 9.10972 12.01Z" fill="currentColor"></path>
								</svg>
							</i>
                            <span class="item-name">{{__("messages.Admin")}}</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="utilities-error" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                <a class="nav-link {{ Request::segment(2) == "config" ? "active" : ""}}" href="{{ url("admin/config") }}">
                                  <i class="bi bi-wrench-adjustable-circle-fill"></i>
                                    <span class="item-name">{{__("messages.Config")}}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::segment(2) == "users" ? "active" : ""}}" href="{{ url("admin/users/all") }}">
                                  <i class="bi bi-people-fill"></i>
                                    <span class="item-name">{{__("messages.Manage Users")}}</span>
                                </a>
                            </li>
							<li class="nav-item">
                                <a class="nav-link {{ Request::segment(2) == "pages" ? "active" : ""}}" href="{{ url("admin/pages") }}">
                                  <i class="bi bi-collection-fill"></i>
                                    <span class="item-name">{{__("messages.Footer Pages")}}</span>
                                </a>
                            </li>
							<li class="nav-item">
                                <a class="nav-link {{ Request::segment(2) == "site" ? "active" : ""}}" href="{{ url("admin/site") }}">
                                  <i class="bi bi-palette-fill"></i>
                                    <span class="item-name">{{__("messages.Site Customization")}}</span>
                                </a>
                            </li>
                           <li class="nav-item">
                                <a class="nav-link {{ Request::segment(2) == "plugins" ? "active" : ""}}" href="{{ route("admin.plugins.index") }}">
                                  <i class="bi bi-puzzle-fill"></i>
                                  <span class="item-name">{{__("Plugins")}}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">{{__("messages.Personalization")}}</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::segment(2) == "links" ? "active" : ""}}" href="{{ url("/studio/links") }}">
                            <i class="icon">
                                 <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">                                
									<path fill-rule="evenodd" clip-rule="evenodd" d="M4.54 2H7.92C9.33 2 10.46 3.15 10.46 4.561V7.97C10.46 9.39 9.33 10.53 7.92 10.53H4.54C3.14 10.53 2 9.39 2 7.97V4.561C2 3.15 3.14 2 4.54 2ZM4.54 13.4697H7.92C9.33 13.4697 10.46 14.6107 10.46 16.0307V19.4397C10.46 20.8497 9.33 21.9997 7.92 21.9997H4.54C3.14 21.9997 2 20.8497 2 19.4397V16.0307C2 14.6107 3.14 13.4697 4.54 13.4697ZM19.4601 2H16.0801C14.6701 2 13.5401 3.15 13.5401 4.561V7.97C13.5401 9.39 14.6701 10.53 16.0801 10.53H19.4601C20.8601 10.53 22.0001 9.39 22.0001 7.97V4.561C22.0001 3.15 20.8601 2 19.4601 2ZM16.0801 13.4697H19.4601C20.8601 13.4697 22.0001 14.6107 22.0001 16.0307V19.4397C22.0001 20.8497 20.8601 21.9997 19.4601 21.9997H16.0801C14.6701 21.9997 13.5401 20.8497 13.5401 19.4397V16.0307C13.5401 14.6107 14.6701 13.4697 16.0801 13.4697Z" fill="currentColor"></path></svg> 
                            </i>
                            <span class="item-name">{{__("messages.Links")}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::segment(2) == "page" ? "active" : ""}}" href="{{ url("/studio/page") }}">
                            <i class="icon">
                                 <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">                                
									<path fill-rule="evenodd" clip-rule="evenodd" d="M16.6653 2.01034C18.1038 1.92043 19.5224 2.41991 20.5913 3.3989C21.5703 4.46779 22.0697 5.88633 21.9898 7.33483V16.6652C22.0797 18.1137 21.5703 19.5322 20.6013 20.6011C19.5323 21.5801 18.1038 22.0796 16.6653 21.9897H7.33487C5.88636 22.0796 4.46781 21.5801 3.39891 20.6011C2.41991 19.5322 1.92043 18.1137 2.01034 16.6652V7.33483C1.92043 5.88633 2.41991 4.46779 3.39891 3.3989C4.46781 2.41991 5.88636 1.92043 7.33487 2.01034H16.6653ZM10.9811 16.845L17.7042 10.102C18.3136 9.4826 18.3136 8.48364 17.7042 7.87427L16.4056 6.57561C15.7862 5.95625 14.7872 5.95625 14.1679 6.57561L13.4985 7.22893L7.09246 13.6548C6.48309 14.2742 6.48309 15.2732 7.09246 15.8825L8.39112 17.1812C9.01048 17.8006 10.0095 17.8006 10.6288 17.1812L10.9811 16.845Z" fill="currentColor"></path>
								</svg>                            
                            </i>
                            <span class="item-name">{{__("messages.Page Settings")}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::segment(2) == "profile" ? "active" : ""}}" href="{{ url("/studio/profile") }}">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">                                    
									<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C14.9091 2 17.5545 3.05455 19.5909 4.81818C20.6091 5.69091 21.4 6.75455 21.9273 7.93636C22.0364 8.16364 22.0364 8.43636 21.9273 8.66364C21.4 9.84545 20.6091 10.9091 19.5909 11.7818C17.5545 13.5455 14.9091 14.6 12 14.6C9.09091 14.6 6.44545 13.5455 4.40909 11.7818C3.39091 10.9091 2.6 9.84545 2.07273 8.66364C1.96364 8.43636 1.96364 8.16364 2.07273 7.93636C2.6 6.75455 3.39091 5.69091 4.40909 4.81818C6.44545 3.05455 9.09091 2 12 2ZM12 12.1C13.6545 12.1 15 10.7545 15 9.1C15 7.44545 13.6545 6.1 12 6.1C10.3455 6.1 9 7.44545 9 9.1C9 10.7545 10.3455 12.1 12 12.1ZM21.1636 16.6C21.1636 16.6 20.8909 19.4818 18.6545 21.1273C16.6 22.6 12.7273 22.6 12.7273 22.6H11.2727C11.2727 22.6 7.4 22.6 5.34545 21.1273C3.10909 19.4818 2.83636 16.6 2.83636 16.6C2.83636 16.6 2.91818 15.3091 5.23636 15.3091H18.7636C21.0818 15.3091 21.1636 16.6 21.1636 16.6Z" fill="currentColor"></path>
								</svg>
                            </i>
                            <span class="item-name">{{__("messages.Profile")}}</span>
                        </a>
                    </li>
                </ul>
                <!-- Sidebar Menu End -->
            </div>
        </div>
        <div class="sidebar-footer">
		<a href="{{ url("/".$usrhandl) }}" target="_blank" class="btn btn-primary w-100">{{__("messages.View Page")}}</a>
		</div>
    </aside>
    <main class="main-content">
      <div class="position-relative iq-banner">
        <!--Nav Start-->
        <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
          <div class="container-fluid navbar-inner">
            <a href="{{ route("panelIndex") }}" class="navbar-brand">
                
                <!--Logo start-->
                <div class="logo-main">
                    @if(file_exists(base_path("assets/linkstack/images/").findFile("avatar")))
                    <div class="logo-normal">
                      <img class="img logo" src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" style="width:auto;height:30px;">
                  </div>
                  <div class="logo-mini">
                    <img class="img logo" src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" style="width:auto;height:30px;">
                  </div>
                    @else
                    <div class="logo-normal">
                      <img class="img logo" type="image/svg+xml" src="{{ asset("assets/linkstack/images/logo.svg") }}" width="30px" height="30px">
                  </div>
                  <div class="logo-mini">
                    <img class="img logo" type="image/svg+xml" src="{{ asset("assets/linkstack/images/logo.svg") }}" width="30px" height="30px">
                  </div>
                    @endif
                    </div>
                <!--logo End-->
                
                
                
                <h4 class="logo-title">{{env("APP_NAME")}}</h4>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                 <svg width="20px" height="20px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                </svg>
                </i>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon">
                  <span class="mt-2 navbar-toggler-bar bar1"></span>
                  <span class="navbar-toggler-bar bar2"></span>
                  <span class="navbar-toggler-bar bar3"></span>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
                <li class="nav-item dropdown">
                  <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(file_exists(base_path("assets/linkstack/images/").findFile("avatar")))
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/".findFile("avatar")) }}" alt="User-Profile" class="theme-color-red-img img-fluid avatar avatar-50 avatar-rounded">
                    @else
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
                    <img src="{{ asset("assets/linkstack/images/user.svg") }}" alt="User-Profile" class="theme-color-red-img img-fluid avatar avatar-50 avatar-rounded">
                    @endif
                    <div class="caption ms-3 d-none d-md-block ">
                        <h6 class="mb-0 caption-title">{{ Auth::user()->name }}</h6>
                        <p class="mb-0 caption-sub-title">{{ Auth::user()->role }}</p>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{{ url("/studio/profile") }}">{{__("messages.Profile")}}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route("logout") }}">{{__("messages.Logout")}}</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>        <!--Nav End-->
      </div>
      <div class="conatiner-fluid content-inner mt-n5 py-0">
        @yield("content")
      </div>
      <!-- Footer Start -->
      <footer class="footer">
          <div class="footer-body">
              <ul class="left-panel list-inline mb-0 p-0">
                  <li class="list-inline-item"><a href="{{ url("admin/pages/privacy-policy") }}">{{__("messages.Privacy Policy")}}</a></li>
                  <li class="list-inline-item"><a href="{{ url("admin/pages/terms-of-service") }}">{{__("messages.Terms of Use")}}</a></li>
              </ul>
              <div class="right-panel">
                  ©<script>document.write(new Date().getFullYear())</script> {{env("APP_NAME")}}, Made with
                  <span class="text-danger">
                      <svg width="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M15.846 4.10001C15.846 4.10001 13.9246 2.19995 12.0033 2.19995C10.0819 2.19995 8.16049 4.10001 8.16049 4.10001C8.16049 4.10001 6.24696 5.99995 6.24696 8.29995C6.24696 10.5999 8.35179 12.4059 12.0033 15.5527C15.6548 12.4059 17.7596 10.5999 17.7596 8.29995C17.7596 5.99995 15.846 4.10001 15.846 4.10001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                          <path d="M12.0033 15.5527C12.0033 15.5527 10.6205 16.6027 9.06982 17.8297C8.58849 18.1797 8.24696 18.7197 8.24696 19.2997C8.24696 20.4497 9.76682 21.7997 12.0033 21.7997C14.2398 21.7997 15.7596 20.4497 15.7596 19.2997C15.7596 18.7197 15.4181 18.1797 14.9368 17.8297C13.3861 16.6027 12.0033 15.5527 12.0033 15.5527Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                  </span> by <a href="https://manus.im">Manus</a>.
              </div>
          </div>
      </footer>
      <!-- Footer End -->
    </main>
    <!-- Wrapper End-->
    <!-- Library Bundle Script -->
    <script src="{{asset("assets/js/core/libs.min.js")}}"></script>
    
    <!-- External Library Bundle Script -->
    <script src="{{asset("assets/js/core/external.min.js")}}"></script>
    
    <!-- Widgetchart Script -->
    <script src="{{asset("assets/js/charts/widgetcharts.js")}}"></script>
    
    <!-- mapchart Script -->
    <script src="{{asset("assets/js/charts/vectore-chart.js")}}"></script>
    <script src="{{asset("assets/js/charts/dashboard.js")}}" ></script>
    
    <!-- fslightbox Script -->
    <script src="{{asset("assets/js/plugins/fslightbox.js")}}"></script>
    
    <!-- Settings Script -->
    <script src="{{asset("assets/js/plugins/setting.js")}}"></script>
    
    <!-- Slider-tab Script -->
    <script src="{{asset("assets/js/plugins/slider-tabs.js")}}"></script>
    
    <!-- Form Wizard Script -->
    <script src="{{asset("assets/js/plugins/form-wizard.js")}}"></script>
    
    <!-- AOS Animation Plugin-->
    <script src="{{asset("assets/vendor/aos/dist/aos.js")}}"></script>
    
    <!-- App Script -->
    <script src="{{asset("assets/js/hope-ui.js")}}" defer></script>
    
	@stack("scripts")
	
  </body>
</html>

