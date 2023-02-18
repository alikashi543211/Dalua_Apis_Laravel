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
                                <h3 class="mb-0">Products</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('admin.products.add') }}" class="btn btn-primary " type="button" >
                                    New Product
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Sr#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Image</th>

                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- {{ dd($tickets) }} --}}
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ date('d-M-Y h:i',strtotime($product->created_at)) }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->slug }}</td>
                                        <td>
                                            <img src="{{ $product->image }}" alt="" width="50">
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.edit',['id' => $product->id]) }}" class="btn btn-warning">Edit</a>
                                            <a href="javascript:void(0);" data-body-text="Are you sure want to delete?" data-title="Delete Product" data-href="{{ route('admin.products.delete',['id' => $product->id]) }}" class="btn btn-danger delete_button_in_listing">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($products->count() == 0)
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
                        {{ $products->links() }}
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
