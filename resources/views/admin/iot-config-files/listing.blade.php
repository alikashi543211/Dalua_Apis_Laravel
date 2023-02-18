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
                                <h3 class="mb-0">Configuration Files</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('admin.iotFile.add') }}" class="btn btn-primary">
                                    Upload New Version
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                                            </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="thead-light">ID</th>
                                    <th scope="thead-light">Name</th>
                                    <th scope="thead-light">Version</th>
                                    <th scope="thead-light">Product</th>
                                    <th scope="thead-light">Uploaded At</th>
                                    <th scope="thead-light">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deviceConfigs as $key => $config)
                                <tr>
                                    <td>{{ $config->id }}</td>
                                    <td>{{ $config->getRawOriginal('name') }}</td>
                                    <td>{{ $config->version }}</td>
                                    <td>{{ $config->product->name ?? 'N/A' }}</td>
                                    <td>{{ date('Y-m-d h:i a', strtotime($config->created_at)) }}</td>
                                    <td>
                                        <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Configuration File" data-href="{{ route('admin.iotFile.delete',['id' => $config->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                                @if($deviceConfigs->count() == 0)
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
                        {{ $deviceConfigs->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
