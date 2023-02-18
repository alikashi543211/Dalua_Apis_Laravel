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
								<h3 class="mb-0">Users</h3>
							</div>
							<div class="col-4 text-right">

							</div>
                            {{-- Search Filters --}}
                            <div class="col-12">
                                <hr>
                                <form action="" class="filter_form">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="status" id="" class="form-control filter_input">
                                                    <option value="">Select Status</option>
                                                    <option value="1" @if(request('status') == "1") selected @endif>Active</option>
                                                    <option value="0" @if(request('status') == "0") selected @endif>De-Active</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="country" id="" class="form-control filter_input">
                                                    <option value="">Select Country</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country }}" @if(request('country') == $country) selected @endif>{{ $country }}</option>
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
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">User ID</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Country</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($users as $user)
                                    <tr class="" data-href="{{ route('admin.users.details',['id' => $user->id]) }}">
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->first_name }}</td>
                                        <td>{{ $user->last_name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->country ?? '' }}</td>
                                        <td>
                                            @if($user->status == 1)
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                            @if($user->status == 0)
                                                <span class="badge badge-danger">De-Active</span>
                                            @endif
                                        </td>
                                        <td>{{ date('d-M-Y h:i',strtotime($user->created_at)) }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.details', ['id' => $user->id]) }}" class="btn btn-warning">Details</a>
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to {{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} user?" data-title="{{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} User" data-href="{{ route('admin.users.changeStatus', ['id' => $user->id]) }}" class="btn btn-{{ $user->status == STATUS_ACTIVE ? 'danger' : 'success' }} delete_button_in_listing">{{ $user->status == STATUS_ACTIVE ? 'Deactivate' : 'Activate' }} User</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($users->count() == 0)
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
                        {{ $users->links() }}
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
