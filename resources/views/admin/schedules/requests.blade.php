@extends('admin.layouts.app')

@section('content')
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8"></div>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">Schedule Requests</h3>
                            </div>
                            <div class="col-4 text-right">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        New Schedule
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="{{ route('admin.schedules.addEasy') }}">Easy</a>
                                        <a class="dropdown-item" href="{{ route('admin.schedules.add') }}">Advanced</a>
                                    </div>
                                </div>
                            </div>
                            @include('admin.schedules.includes.search_filter')
                        </div>
                    </div>

                    <div class="col-12">
                                            </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Name</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Accessibiltiy</th>
                                    <th>Geolocation</th>
                                    <th>Type</th>
                                    <th>Water Type</th>
                                    <th>Uploaded</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
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
                                    <td>{{ optional($schedule->user)->FullName }} {{ $schedule->user_id == 1 ? ', (Dalua Preset)' : '' }}</td>
                                    <td>{{ $schedule->user->email ?? '' }}</td>
                                    <td>{{ $schedule->public ? 'Public' : 'Private' }}</td>
                                    <td>{!! $schedule->geo_location ? 'Enabled <br> <span style="font-size:12px">('.$schedule->geolocation->name.')</span> ' : 'Disabled' !!}</td>
                                    <td>{{ $schedule->mode == SCHEDULE_EASY ? 'Easy' : 'Advanced' }}</td>
                                    <td>{{ $schedule->water_type ?? '' }}</td>
                                    <td>{{ $schedule->enabled ? 'Yes' : 'No' }}</td>
                                    <td>
                                        @if($schedule->mode == SCHEDULE_EASY)
                                            <button class="btn btn-primary easy-view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                        @else
                                            <button class="btn btn-primary view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                        @endif
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to accept?" data-title="Accept" data-href="{{ route('admin.schedules.update.approval', ['id' => $schedule->id, 'approval' => 'Accepted' ]) }}" class="btn btn-success delete_button_in_listing">Accept</a>
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to reject?" data-title="Reject" data-href="{{ route('admin.schedules.update.approval', ['id' => $schedule->id, 'approval' => 'Rejected' ]) }}" class="btn btn-danger delete_button_in_listing">Reject</a>
                                    </td>
                                </tr>
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
                    <div class="card-footer py-4">
                        {{ $schedules->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')

@endsection
