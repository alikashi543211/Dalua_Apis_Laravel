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
                                <h3 class="mb-0">Devices</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('admin.users.allUpdateOta') }}" class="btn btn-primary " type="button" >
                                    Update All OTA
                                </a>
                            </div>
                            {{-- Search Filters --}}
                            <div class="col-12">
                                <hr>
                                <form action="" class="filter_form">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="water_type" id="" class="form-control filter_input">
                                                    <option value="">Select Type</option>
                                                    <option value="Marine" @if(request('water_type') == "Marine") selected @endif>Marine</option>
                                                    <option value="Fresh" @if(request('water_type') == "Fresh") selected @endif>Fresh</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="product_id" id="" class="form-control filter_input">
                                                    <option value="">Select Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" @if(request('product_id') == $product->id) selected @endif>{{ $product->name }}</option>
                                                    @endforeach
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
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Aquarium</th>
                                    <th scope="col">Group</th>
                                    <th scope="col">Mac Address</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($devices as $device)
                                    <tr>
                                        <td>{{ $device->name }}</td>
                                        <td>{{ $device->topic }}</td>
                                        <td>{{ optional($device->aquarium)->name }}</td>
                                        <td>{{ optional($device->group)->name }}</td>
                                        <td>{{ $device->mac_address }}</td>
                                        <td>{{ $device->water_type }}</td>
                                        <td>{{ $device->product->name ?? 'N/A' }}</td>
                                        <td>{{ $device->user->FullName }}</td>
                                        <td>{{ date('d-M-Y h:i',strtotime($device->created_at)) }}</td>
                                        <td>
                                            <a href="{{ route('admin.devices.detail',['id' => $device->id]) }}" class="btn btn-warning">Detail</a>
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to update OTA?" data-title="Update OTA" data-href="{{ route('admin.users.updateOta',['id' => $device->id]) }}" class="btn btn-primary delete_button_in_listing">Update OTA</a>
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Device" data-href="{{ route('admin.devices.delete',['id' => $device->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($devices->count() == 0)
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
                        {{ $devices->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
