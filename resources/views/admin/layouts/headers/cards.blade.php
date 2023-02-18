<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <a href="{{ route('admin.users.listing') }}">
                        <div class="card card-stats mb-4 mb-xl-0 p-2">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $users_count ?? 0 }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                            <i class="fa fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <a href="{{ route('admin.devices.listing') }}">
                        <div class="card card-stats mb-4 mb-xl-0 p-2">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Devices</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $devices_count ?? 0 }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                            <i class="ni ni-planet"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <a href="{{ route('admin.schedules.listingDalua') }}">
                        <div class="card card-stats mb-4 mb-xl-0 p-2">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Dalua Presets</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $dalua_presets_count ?? 0 }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                            <i class="ni ni-spaceship"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                </div>
                <div class="col-xl-3 col-lg-6">
                    <a href="{{ route('admin.schedules.public_requests') }}">
                        <div class="card card-stats mb-4 mb-xl-0 p-2">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Public Schedules</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $public_schedules_count ?? 0 }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
