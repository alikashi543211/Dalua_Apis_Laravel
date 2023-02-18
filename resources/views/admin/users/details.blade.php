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
                                <h3 class="mb-0">User Detail</h3>
                            </div>
                            <div class="col-4 text-right">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                                <a href="javascript:void(0);" data-body-text="Are you sure want to {{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} user?" data-title="{{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} User" data-href="{{ route('admin.users.changeStatus', ['id' => $user->id]) }}" class="btn btn-{{ $user->status == STATUS_ACTIVE ? 'danger' : 'success' }} delete_button_in_listing">{{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} User</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">

            <div class="col-xl-3">
                @include('admin.users.includes.user_basic_info')
            </div>
            <div class="col-xl-9 mb-5 mb-xl-0">
                <div class="row">
                    <div class="col-md-12 mb-5">
                        <div class="card shadow card_detail">
                            <div class="card-header border-0">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="mb-0">Products</h3>
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
                                            <th scope="col">Model</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Subcategory</th>
                                            <th scope="col">Image</th>
                                            <th scope="col">Specification</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- {{ dd($tickets) }} --}}
                                        @foreach ($user->products as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->model }}</td>
                                                <td>{{ $product->category->name }}</td>
                                                <td>{{ optional($product->subcategory)->name }}</td>
                                                <td>
                                                    <img src="{{ $product->image }}" alt="" width="50">
                                                </td>
                                                <td>{!! $product->specification !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-5  ">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Groups</h3>
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
                                    <th scope="col">Devices</th>
                                    <th scope="col">Schedule</th>
                                    <th scope="col">Aquarium</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($user->groups as $group)
                                <tr>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ count($group->devices) }}</td>
                                    <td>{{ $group->schedule->name ?? '' }}</td>
                                    <td>{{ $group->aquarium->name  ?? ''}}</td>
                                    <td>
                                        <a href="{{ route('admin.groups.detail', ['id' => $group->id]) }}" class="btn btn-sm btn-warning">Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-5  ">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Devices</h3>
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
                                    <th scope="col">Aquarium</th>
                                    <th scope="col">Group</th>
                                    <th scope="col">Mac Address</th>
                                    <th scope="col">Actions</th>

                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($user->devices as $device)
                                <tr>
                                    <td>{{ $device->name }}</td>
                                    <td>{{ $device->topic }}</td>
                                    <td>{{ optional($device->aquarium)->name }}</td>
                                    <td>{{ optional($device->group)->name }}</td>
                                    <td>{{ $device->mac_address }}</td>
                                    <td>
                                        <a href="{{ route('admin.devices.detail',['id' => $device->id]) }}" class="btn btn-sm btn-warning">Detail</a>
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to update OTA?" data-title="Update OTA" data-href="{{ route('admin.users.updateOta',['id' => $device->id]) }}" class="btn btn-primary delete_button_in_listing">Update OTA</a>
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Device" data-href="{{ route('admin.devices.delete',['id' => $device->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-5  ">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Aquariums</h3>
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
                                    <th scope="col">Devices</th>
                                    <th scope="col">Groups</th>
                                    <th scope="col">Temperature</th>
                                    <th scope="col">PH</th>
                                    <th scope="col">Salinity</th>
                                    <th scope="col">Alkalinity</th>
                                    <th scope="col">Magnesium</th>
                                    <th scope="col">Nitrate</th>
                                    <th scope="col">Phosphate</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($user->aquaria as $aquarium)
                                <tr>
                                    <td>{{ $aquarium->name }}</td>
                                    <td>{{ $aquarium->devices->count() }}</td>
                                    <td>{{ $aquarium->groups->count() }}</td>
                                    <td>{{ $aquarium->temperature }}</td>
                                    <td>{{ $aquarium->ph }}</td>
                                    <td>{{ $aquarium->salinity }}</td>
                                    <td>{{ $aquarium->alkalinity }}</td>
                                    <td>{{ $aquarium->magnesium }}</td>
                                    <td>{{ $aquarium->nitrate }}</td>
                                    <td>{{ $aquarium->phosphate }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-5  ">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Shared Aquariums</h3>
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
                                    <th scope="col">Devices</th>
                                    <th scope="col">Groups</th>
                                    <th scope="col">Temperature</th>
                                    <th scope="col">PH</th>
                                    <th scope="col">Salinity</th>
                                    <th scope="col">Alkalinity</th>
                                    <th scope="col">Magnesium</th>
                                    <th scope="col">Nitrate</th>
                                    <th scope="col">Phosphate</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($user->sharedAquaria as $aquarium)
                                <tr>
                                    <td>{{ $aquarium->name }}</td>
                                    <td>{{ $aquarium->devices->count() }}</td>
                                    <td>{{ $aquarium->groups->count() }}</td>
                                    <td>{{ $aquarium->temperature }}</td>
                                    <td>{{ $aquarium->ph }}</td>
                                    <td>{{ $aquarium->salinity }}</td>
                                    <td>{{ $aquarium->alkalinity }}</td>
                                    <td>{{ $aquarium->magnesium }}</td>
                                    <td>{{ $aquarium->nitrate }}</td>
                                    <td>{{ $aquarium->phosphate }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-5  ">
                <div class="card shadow">
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
                                    <th scope="col">Sr. No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Accessibility</th>
                                    <th scope="col">Geolocation</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Water Type</th>
                                    <th scope="col">Uploaded</th>
                                    <th scope="col">Created By</th>
                                    <th scope="col">Actions</th>
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
                                        <td>{!! $schedule->geo_location_id ? 'Enabled <br> <span style="font-size:12px">(' . $schedule->geolocation->name . ')</span> ' : 'Disabled' !!}</td>
                                        <td>{{ $schedule->mode == SCHEDULE_EASY ? 'Easy' : 'Advanced' }}</td>
                                        <td>{{ $schedule->water_type ?? '' }}</td>
                                        <td>{{ $schedule->enabled ? 'Yes' : 'No' }}</td>
                                        <td class="text-center">
                                            @if($schedule->mode == SCHEDULE_EASY)
                                                <button class="btn btn-primary easy-view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                            @else
                                                <button class="btn btn-primary view-schedule" data-mode='{{ $schedule->mode }}' id="schedule-{{ $schedule->id }}" type="button" data-id="{{ $schedule->id }}">View Schedule</button>
                                            @endif
                                            @if ($schedule->mode == SCHEDULE_EASY)
                                                <a href="{{ route('admin.schedules.editEasy', ['id' => $schedule->id]) }}" class="btn btn-warning">Edit</a>
                                            @else
                                                <a href="{{ route('admin.schedules.edit', ['id' => $schedule->id]) }}" class="btn btn-warning">Edit</a>
                                            @endif
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Schedule" data-href="{{ route('admin.schedules.delete', ['id' => $schedule->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
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
                </div>
            </div>
        </div>


    </div>

        <div class="modal" id="changePasswordModal">
            <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                <h4 class="modal-title">Change Password</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('admin.users.password.change') }}" method="POST">
                    <!-- Modal body -->
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label for="password-new">New Password</label>
                            <input type="text" required id="password-new" minlength="8" name="password" class="form-control" placeholder="New Password">
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            </div>
      </div>
@endsection

@section('script')
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
