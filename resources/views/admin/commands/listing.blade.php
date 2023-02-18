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
                                <h3 class="mb-0">Commands</h3>
                            </div>
                            <div class="col-4 text-right">
                                {{-- <a href="{{ route('admin.users.allUpdateOta') }}" class="btn btn-primary " type="button" >
                                    Update All OTA
                                </a> --}}
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
                                                    <option value="1" @if(request('status') == "1") selected @endif>Completed</option>
                                                    <option value="0" @if(request('status') == "0") selected @endif>Pending</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="group_id" id="" class="form-control filter_input">
                                                    <option value="">Select Option</option>
                                                    <option value="1" @if(request('group_id') == "1") selected @endif>Group</option>
                                                    <option value="0" @if(request('group_id') == "0") selected @endif>Device</option>
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
                                    <th scope="col">ID</th>
                                    <th scope="col">topic</th>
                                    <th scope="col">Command Id</th>
                                    <th scope="col">Sent By</th>
                                    <th scope="col">Sent At</th>
                                    <th scope="col">Device/Group</th>
                                    <th scope="col">Mac Address</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commands as $key => $command)
                                <tr>
                                    <td>{{ $command->id }}</td>
                                    <td>{{ str_replace('/ack','',$command->topic) }}</td>
                                    <td>{{ $command->command_id }}</td>
                                    <td>{{ $command->user ? $command->user->username : ''}}</td>
                                    <td>{{ date('M-d H:i:s',(int) $command->timestamp) }}</td>
                                    <td>
                                        @if ($command->group_id)
                                            {{ $command->group->name }} <span class="small">(Group)</span>
                                        @else
                                            {{ $command->device->name }} <span class="small">(Device)</span>
                                        @endif
                                    </td>
                                    <td>{{ $command->mac_address }}</td>
                                    <td>
                                        @if($command->status)
                                            <span class="label label-success">Completed</span>
                                        @else
                                            <span class="label label-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-primary view-payload" data-toggle="modal" data-target="#payload-{{ $command->id }}">Payload</button>
                                        <button class="btn btn-primary view-response" data-toggle="modal" data-target="#response-{{ $command->id }}">Response</button>
                                    </td>
                                </tr>
                                @endforeach
                                @if($commands->count() == 0)
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
                        {{ $commands->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
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
