<div class="card shadow card_detail mb-5 @if(Route::currentRouteName() == 'admin.profile.edit') profile_card_detail @else card_detail @endif">
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
                    <td colspan="2" class="text-center">
                        <img class="img-fluid rounded-circle" width="150" src="{{ asset($user->image) }}" alt="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        First Name
                    </th>
                    <td>
                        {{ $user->first_name }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Middle Name
                    </th>
                    <td>
                        {{ $user->middle_name ? $user->middle_name : '--' }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Last Name
                    </th>
                    <td>
                        {{ $user->last_name }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Username
                    </th>
                    <td>
                        {{ $user->username }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Email
                    </th>
                    <td>
                        {{ $user->email }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Phone No.
                    </th>
                    <td>
                        {{ $user->phone_no ? $user->phone_no : '--' }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Country
                    </th>
                    <td>
                        {{ $user->country ?? '--' }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Login Type
                    </th>
                    <td>
                        {{ $user->login_type == LOGIN_EMAIL ? 'Email' : ($user->login_type == LOGIN_APPLE ? 'Apple' : ($user->login_type == LOGIN_FACEBOOK ? 'Facebook': 'Google')) }}
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Status
                    </th>
                    <td>
                        <span class="badge badge-pill badge-{{ $user->status == STATUS_ACTIVE ? 'success' : 'danger' }}">{{ $user->status == STATUS_ACTIVE ? 'Active' : 'Deactive' }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
