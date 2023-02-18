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
                                <h3 class="mb-0">Dalua Preset</h3>
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
                            {{-- Search Filters --}}
                            <div class="col-12">
                                <hr>
                                <form action="" class="filter_form">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="mode" id="mode" class="form-control filter_input">
                                                    <option value="">Select Type</option>
                                                    <option value="1" @if(request('mode') == SCHEDULE_EASY) selected @endif>Easy</option>
                                                    <option value="0" @if(request('mode') == SCHEDULE_ADVANCED) selected @endif>Advanced</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="geo_location_id" id="access" class="form-control filter_input">
                                                    <option value="">Select Geo Location</option>
                                                    <option value="1" @if(request('geo_location_id') == "1") selected @endif>Enabled</option>
                                                    <option value="0" @if(request('geo_location_id') == "0") selected @endif>Disabled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger clear_filter_button">Clear</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="col-12">
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Sr. No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Geolocation</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Water Type</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $key => $schedule)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $schedule->name }}</td>
                                    <td>{{ optional($schedule->user)->FullName }} {{ $schedule->user_id == 1 ? ', (Dalua Preset)' : '' }}</td>
                                    <td>{{ $schedule->user->email ?? '' }}</td>
                                    <td>{!! $schedule->geo_location_id ? 'Enabled <br> <span style="font-size:12px">('.$schedule->location->name.')' ?? 'N/A'.')</span> ' : 'Disabled' !!}</td>
                                    <td>{{ $schedule->mode == SCHEDULE_EASY ? 'Easy' : 'Advanced' }}</td>
                                    <td>{{ $schedule->water_type ?? '' }}</td>
                                    <td>
                                        @if ($schedule->default)
                                            <button class="btn btn-info" type="button">Default</button>
                                        @else
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to set default?" data-title="Set Default" data-href="{{ route('admin.schedule.setDefaultSchedule',['id' => $schedule->id]) }}" class="btn btn-secondary delete_button_in_listing">Set Default</a>
                                        @endif
                                        @if($schedule->mode == SCHEDULE_EASY)
                                            <button class="btn btn-primary easy-view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                        @else
                                            <button class="btn btn-primary view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                        @endif
                                        @if ($schedule->mode == SCHEDULE_EASY)
                                        <a href="{{ route('admin.schedules.editEasy',['id' => $schedule->id ]) }}" class="btn btn-warning">Edit</a>
                                        @else
                                        <a href="{{ route('admin.schedules.edit',['id' => $schedule->id ]) }}" class="btn btn-warning">Edit</a>
                                        @endif
                                        @if (!$schedule->default)
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Schedule" data-href="{{ route('admin.schedules.delete', ['id' => $schedule->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
                                        @endif
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

