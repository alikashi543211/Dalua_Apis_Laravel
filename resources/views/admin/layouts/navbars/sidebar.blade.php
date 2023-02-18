<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Brand -->
        <a class="navbar-brand pt-0" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('argon') }}/img/brand/blue.png" style="height: 100px;" class="navbar-brand-img" alt="...">
        </a>
        <!-- User -->
        <ul class="nav align-items-center d-md-none">
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle">
                        <img alt="Image placeholder" src="{{ asset('argon') }}/img/theme/team-1-800x800.jpg">
                        </span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ __('Welcome!') }}</h6>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.logout') }}" class="dropdown-item" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                        <i class="ni ni-user-run"></i>
                        <span>{{ __('Logout') }}</span>
                    </a>
                </div>
            </li>
        </ul>
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="{{ route('admin.dashboard') }}">
                            <img src="{{ asset('argon') }}/img/brand/blue.png">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Form -->
            <form class="mt-4 mb-3 d-md-none">
                <div class="input-group input-group-rounded input-group-merge">
                    <input type="search" class="form-control form-control-rounded form-control-prepended" placeholder="{{ __('Search') }}" aria-label="Search">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="fa fa-search"></span>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Navigation -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="ni ni-tv-2" @routeis('admin.dashboard') style="color: #f4645f;" @endrouteis></i> <span @routeis('admin.dashboard') style="color: #f4645f;" @endrouteis>{{ __('Dashboard') }} </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.listing') }}">
                        <i class="fa fa-users" @routeis('admin.users.*') style="color: #f4645f;" @endrouteis></i> <span @routeis('admin.users.*') style="color: #f4645f;" @endrouteis>{{ __('Users') }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.devices.listing') }}">
                        <i class="ni ni-planet" @routeis('admin.devices.*') style="color: #f4645f;" @endrouteis></i> <span @routeis('admin.devices.*') style="color: #f4645f;" @endrouteis>{{ __('Devices') }}</span>
                    </a>
                </li>

                <li class="nav-item @routeis('admin.schedules.*') active @endrouteis">
                    <a class="nav-link" href="#navbar-examples-menu-b" data-toggle="collapse" role="button" @routeis('admin.schedules.*') aria-expanded="true" @endrouteis aria-controls="navbar-examples-menu-b">
                        <i class="ni ni-spaceship" @routeis('admin.schedules.*') style="color: #f4645f;" @endrouteis></i>
                        <span class="nav-link-text" @routeis('admin.schedules.*') style="color: #f4645f;" @endrouteis>{{ __('Schedules') }}</span>
                    </a>

                    <div class="collapse @routeis('admin.schedules.*') show @endrouteis" id="navbar-examples-menu-b">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link" @routeis('admin.schedules.listing') style="color: #f4645f;" @endrouteis href="{{ route('admin.schedules.listing') }}">
                                    {{ __('Listing') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @routeis('admin.schedules.requests') style="color: #f4645f;" @endrouteis href="{{ route('admin.schedules.requests') }}">
                                    {{ __('Requests') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @routeis('admin.schedules.public_requests') style="color: #f4645f;" @endrouteis href="{{ route('admin.schedules.public_requests') }}">
                                    {{ __('Public') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @routeis('admin.schedules.listingDalua') style="color: #f4645f;" @endrouteis href="{{ route('admin.schedules.listingDalua') }}">
                                    {{ __('Dalua Preset') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <li class="nav-item">
                    <a class="nav-link" @routeis('admin.groups.*') style="color: #f4645f;" @endrouteis href="{{ route('admin.groups.listing') }}">
                        <i class="ni ni-palette" @routeis('admin.groups.*') style="color: #f4645f;" @endrouteis></i> {{ __('Groups') }}
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" @routeis('admin.commandLogs.listing') style="color: #f4645f;" @endrouteis href="{{ route('admin.commandLogs.listing') }}">
                        <i class="ni ni-pin-3"></i> {{ __('Command Logs') }}
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" @routeis('admin.products.*') style="color: #f4645f;" @endrouteis  href="{{ route('admin.products.listing') }}">
                        <i class="fa fa-shopping-cart"></i> {{ __('Products') }}
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" @routeis('admin.iotFile.listing') style="color: #f4645f;" @endrouteis  href="{{ route('admin.iotFile.listing') }}">
                        <i class="ni ni-ungroup"></i> {{ __('Iot File Config') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
