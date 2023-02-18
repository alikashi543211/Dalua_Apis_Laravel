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
                                <h3 class="mb-0">Profile</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">

            <div class="col-xl-4">
                @include('admin.users.includes.user_basic_info')
            </div>
            <div class="col-xl-8 mb-5 mb-xl-0">
                <div class="row">
                    <div class="col-md-12 mb-5">
                        <div class="card shadow card_detail">
                            <div class="card-header border-0">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="mb-0">Edit Profile</h3>
                                    </div>
                                    <div class="col text-right">

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="post" action="{{ route('admin.profile.update') }}" autocomplete="off" enctype="multipart/form-data">
                                    @csrf

                                    <h6 class="heading-small text-muted mb-4">{{ __('User information') }}</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">First Name</label>
                                                <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Middle Name</label>
                                                <input type="text" class="form-control" name="middle_name" value="{{ $user->middle_name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Last Name</label>
                                                <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Username</label>
                                                <input type="text" class="form-control" name="username" value="{{ $user->username }}" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Email</label>
                                                <input type="text" class="form-control" name="email" value="{{ $user->email }}" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Phone No</label>
                                                <input type="text" class="form-control" name="phone_no" value="{{ $user->phone_no }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Country</label>
                                                <input type="text" class="form-control" name="country" value="{{ $user->country }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Profile Image</label>
                                                <input type="file" class="form-control" name="image" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                                    </div>
                                </form>
                                <hr>
                                <form method="post" action="{{ route('admin.profile.password') }}" autocomplete="off">
                                    @csrf
                                    <h6 class="heading-small text-muted mb-4">{{ __('Password') }}</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name">Current Password</label>
                                                <input type="password" class="form-control" name="old_password" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name">New Password</label>
                                                <input type="password" class="form-control" name="password" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name">Confirm New Password</label>
                                                <input type="password" class="form-control" name="password_confirmation" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success mt-4">{{ __('Change Password') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

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
