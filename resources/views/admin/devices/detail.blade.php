@extends('admin.layouts.app')
@section('css')
    <!-- third party css -->
    <link href="{{ url('assets/libs/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/libs/datatables/buttons.bootstrap4.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/libs/datatables/responsive.bootstrap4.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
	<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8"></div>
	<div class="container-fluid mt--7">
		<div class="row">
			<div class="col">
				<div class="card shadow">
					<div class="card-header border-0">
						<div class="row align-items-center">
							<div class="col-8">
								<h3 class="mb-0">Device Detail</h3>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-5">

			<div class="col-xl-3">
				<div class="card shadow card_detail">
					<div class="card-header border-0">
						<div class="row align-items-center">
							<div class="col">
								<h3 class="mb-0">Basic Info</h3>
							</div>
							<div class="col text-right">
							</div>
						</div>
					</div>
					<div class="table-responsive">
						<!-- Projects table -->
						<table class="table align-items-center table-flush">
							<tbody>
								<tr>
									<th scope="row">
										Name
									</th>
									<td>
										{{ $device->name }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Topic
									</th>
									<td>
										{{ $device->topic }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Aquarium
									</th>
									<td>
										{{ optional($device->aquarium)->name }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Group
									</th>
									<td>
										{{ optional($device->group)->name }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Mac Address
									</th>
									<td>
										{{ $device->mac_address }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										User
									</th>
									<td>
										{{ $device->user->FullName }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Total Schedules
									</th>
									<td>
										{{ $schedules->count() }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Product/Device Type
									</th>
									<td>
										{{ $device->product->name ?? 'N/A' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Water Type
									</th>
									<td>
										{{ $device->water_type ?? '' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										ESP-2 Version
									</th>
									<td>
										{{ $device->version ?? '' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										ESP-2 Product Name
									</th>
									<td>
										{{ $device->esp_product_name ?? '' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										ESP-2 Wifi
									</th>
									<td>
										{{ $device->wifi ?? '' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										ESP-2 IP
									</th>
									<td>
										{{ $device->ip_address ?? '' }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Timezone
									</th>
									<td>
										{{ $device->timezone }}
									</td>
								</tr>
								<tr>
									<th scope="row">
										Created At
									</th>
									<td>
										{{ date('d-M-Y h:i', strtotime($device->created_at)) }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-xl-9 mb-5 mb-xl-0">
				{{-- Schedules --}}
				<div class="row">
					<div class="col-md-12">
						<div class="card shadow card_detail">
							<div class="card-header border-0">
								<div class="row align-items-center">
									<div class="col">
										<h3 class="mb-0">Schedules</h3>
									</div>
									<div class="col text-right">

									</div>
								</div>
							</div>
							<div class="table-responsive">
								<!-- Projects table -->
								<table class="datatable table align-items-center table-flush">
									<thead class="thead-light">
										<tr>
											<th scope="col">Name</th>
											<th scope="col">Topic</th>
											<th scope="col">Accessibiltiy</th>
											<th scope="col">Geolocation</th>
											<th scope="col">Type</th>
											<th scope="col">Enabled</th>
											<th scope="col">Created By</th>
                                            <th scope="col">Actions</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($schedules as $schedule)
											<tr>
												<td>{{ $schedule->name }}</td>
												<td>{{ $device->topic }}</td>
												<td>{{ $schedule->public ? 'Public' : 'Private' }}</td>
												<td>{!! $schedule->geo_location ? 'Enabled <br> <span style="font-size:12px">(' . $schedule->geolocation->name . ')</span> ' : 'Disabled' !!}</td>
												<td>
													{{ $schedule->mode == SCHEDULE_EASY ? 'Easy' : 'Advanced' }}
												</td>
												<td>{{ $schedule->enabled ? 'Yes' : 'No' }}</td>
												<td>{{ optional($schedule->user)->FullName }} {{ $schedule->user_id == 1 ? ', (Dalua Preset)' : '' }}</td>
                                                <td>
                                                    @if($schedule->mode == SCHEDULE_EASY)
                                                        <button class="btn btn-primary easy-view-schedule btn-sm" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                                    @else
                                                        <button class="btn btn-primary view-schedule btn-sm" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                                    @endif
                                                    @if(!$schedule->enabled)
                                                        <a href="{{route('admin.schedule.scheduleUpload', $schedule->id)}}" class="btn btn-sm btn-warning upload-schedule" data-schedule-id="{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">Upload Schedule</a>
                                                    @else
                                                        <button class="btn btn-sm btn-success" data-schedule-id="{{ $schedule->id }}" type="button" data-id=*"{{ $schedule->id }}">Uploaded</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @include('admin.schedules.includes.view_schedule_modal')
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
        <div class="row my-3">
            <div class="col-md-12">
                <h2 class="text-center">Device Logs</h2>
            </div>
        </div>
        {{-- Connectivity --}}
        <div class="row">
            <div class="col-md-6 mb-5">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Connectivity</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="datatable table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Message</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($connectivityLogs as $log)
                                    <tr>
                                        <td>{{ $log->message }}</td>
                                        <td>{{ date('d M, Y h:i a', strtotime($log->created_at)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Subscribe --}}
            <div class="col-md-6 mb-5">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Subscribe</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="datatable table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Message</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscribeLogs as $log)
                                    <tr>
                                        <td>{{ $log->message }}</td>
                                        <td>{{ date('d M, Y h:i a', strtotime($log->created_at)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Topics --}}
            <div class="col-md-6 mb-5">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Topics</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="datatable table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Message</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topicLogs as $log)
                                    <tr>
                                        <td>{{ $log->topic }}</td>
                                        <td>{{ $log->message }}</td>
                                        <td>{{ date('d M, Y h:i a', strtotime($log->created_at)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Schedules --}}
            <div class="col-md-6 mb-5">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Recent Schedules</h3>
                            </div>
                            <div class="col text-right">

                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="datatable table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commands as $key => $command)
                                <tr>
                                    <td>{{ $command->payload->scheduleName ?? 'N/A' }}</td>
                                    <td>{{ $command->status ? 'Completed' : 'Pending' }}</td>
                                    <td>{{ $command->created_at->format('d M, Y h:i a') }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm view-payload" data-toggle="modal" data-target="#payload-{{ $command->id }}">Payload</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>


                </div>
            </div>
        </div>
	</div>

    @foreach ($commands as $command)
        <div class="modal" id="payload-{{ $command->id }}">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal body -->
                    <div class="modal-body">
                        <pre>
                            @php
                            print_r(json_decode($command->getRawOriginal('payload'), true))
                            @endphp
                        </pre>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
        <div class="modal" id="response-{{ $command->id }}">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal body -->
                    <div class="modal-body">
                        <pre>
                            @php
                            print_r(json_decode($command->getRawOriginal('response'), true))
                            @endphp
                        </pre>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('script')
    @include('admin.schedules.includes.view_schedule_js')
    <script src="{{ url('assets/libs/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.datatable').DataTable({
                searching: false,
                paging: true,
                info: false,
                ordering: false,
                lengthChange: false
            });
        })

    </script>
@endsection
