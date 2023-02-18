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
                                <h3 class="mb-0">Groups</h3>
                            </div>
                            <div class="col-4 text-right">
                            </div>
                            {{-- Search Filters --}}
                            <div class="col-12">
                                <hr>
                                <form action="" class="filter_form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select name="water_type" id="" class="form-control filter_input">
                                                    <option value="">Select Type</option>
                                                    <option value="Marine" @if(request('water_type') == "Marine") selected @endif>Marine</option>
                                                    <option value="Fresh" @if(request('water_type') == "Fresh") selected @endif>Fresh</option>
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
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Uid</th>
                                    <th scope="col">Devices</th>
                                    <th scope="col">Aquarium</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    <tr class="">
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->topic }}</td>
                                        <td>{{ $group->uid }}</td>
                                        <td>{{ count($group->devices) }}</td>
                                        <td>{{ optional($group->aquarium)->name }}</td>
                                        <td>{{ optional($group->user)->FullName }}</td>
                                        <td>{{ $group->water_type ?? 'N/A' }}</td>
                                        <td>{{ date('d-M-Y h:i',strtotime($group->created_at)) }}</td>
                                        <td>
                                            <a href="{{ route('admin.groups.detail',['id' => $group->id]) }}" class="btn btn-warning">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($groups->count() == 0)
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
                        {{ $groups->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
