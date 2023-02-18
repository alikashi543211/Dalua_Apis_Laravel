@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.headers.cards')

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-xl-12 mb-5 mb-xl-0">
                <div class="card bg-gradient-default shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase text-light ls-1 mb-1">Overview</h6>
                                <h2 class="text-white mb-0">Users and Devices</h2>
                            </div>
                            <div class="col">
                                <ul class="nav nav-pills justify-content-end">
                                    {{-- <li class="nav-item mr-2 mr-md-0" data-toggle="chart" data-target="#chart-sales" data-update='{"data":{"datasets":[{"data":[0, 20, 10, 30, 15, 40, 20, 60, 60]}]}}' data-prefix="$" data-suffix="k">
                                        <a href="#" class="nav-link py-2 px-3 active" data-toggle="tab">
                                            <span class="d-none d-md-block">Month</span>
                                            <span class="d-md-none">M</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" data-toggle="chart" data-target="#chart-sales" data-update='{"data":{"datasets":[{"data":[0, 20, 5, 25, 10, 30, 15, 40, 40]}]}}' data-prefix="$" data-suffix="k">
                                        <a href="#" class="nav-link py-2 px-3" data-toggle="tab">
                                            <span class="d-none d-md-block">Week</span>
                                            <span class="d-md-none">W</span>
                                        </a>
                                    </li> --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chart -->
                        <div class="chart">
                            <!-- Chart wrapper -->
                            <canvas id="chart-sales" class="chart-canvas" width="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-xl-9 mb-5 mb-xl-0">
                <div class="card shadow dashboard_card_detail">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Recent Public Schedules</h3>
                            </div>
                            <div class="col text-right">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Sr. No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Accessibiltiy</th>
                                    <th scope="col">Geolocation</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Enabled</th>
                                    <th scope="col">Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $key => $schedule)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            {{ $schedule->name }}
                                            {{ $schedule->device ? ' (Device)' : ($schedule->group ? ' (Group)' : '') }}
                                        </td>
                                        <td>{{ $schedule->device ? $schedule->device->topic : ($schedule->group ? $schedule->group->topic : '') }}</td>
                                        <td>{{ $schedule->public ? 'Public' : 'Private' }}</td>
                                        <td>{!! $schedule->geo_location ? 'Enabled <br> <span style="font-size:12px">('.$schedule->geolocation->name.')</span> ' : 'Disabled' !!}</td>
                                        <td>{{ $schedule->mode == SCHEDULE_EASY ? 'Easy' : 'Advanced' }}</td>
                                        <td>{{ $schedule->enabled ? 'Yes' : 'No' }}</td>
                                        <td>{{ optional($schedule->user)->FullName }} {{ $schedule->user_id == 1 ? ', (Dalua Preset)': '' }}</td>
                                    </tr>
                                    @include('admin.schedules.includes.view_schedule_modal')
                                @endforeach
                                @if($schedules->count() == 0)
                                    <tr>
                                        <td colspan="100" class="text-center">
                                            <span>{{ DATA_NOT_AVAILABLE_TABLE }}</span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card shadow dashboard_card_detail">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Social traffic</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Country</th>
                                    <th scope="col">Users</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($countryUsers as $countryUser)
                                    <tr>
                                        <th scope="row">
                                            {{ $countryUser->country }}
                                        </th>
                                        <td>
                                            {{ $countryUser->total }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="mr-2">{{ $countryUser->percentage }}%</span>
                                                <div>
                                                    <div class="progress">
                                                    <div class="progress-bar bg-gradient-{{ $countryUser->progress_color }}" role="progressbar" aria-valuenow="{{ $countryUser->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $countryUser->percentage }}%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.layouts.footers.auth')
    </div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js" integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        var months = [];
        var users = [];
        var devices = [];
        @foreach ($UserGraph['months'] as $month)
            months.push("{{$month}}");
        @endforeach
        @foreach ($UserGraph['users'] as $userCount)
            users.push("{{$userCount}}");
        @endforeach
        @foreach ($UserGraph['devices'] as $deviceCount)
            devices.push("{{$deviceCount}}");
        @endforeach
        const ctx = document.getElementById('chart-sales').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'No of Users',
                    type: 'line',
                    data: users,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                },{
                    label: 'No of Devices',
                    type: 'line',
                    data: devices,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endpush
