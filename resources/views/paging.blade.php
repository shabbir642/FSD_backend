@extends('layout')

@section('content')
<h1>Products</h1>

<!-- <p>
    <a class="btn btn-primary" href="/products/create"><span class="glyphicon glyphicon-plus"></span> Add Product</a>
</p>
 -->
<table class="table table-bordered table-hover">
    <thead>
        <th>Category</th>
        <th>Name</th>
        <th>SKU</th>
        <th>Price</th>
        <th>Actions</th>
    </thead>
    <tbody>
        @if ($users->count() == 0)
        <tr>
            <td colspan="5">No products to display.</td>
        </tr>
        @endif

        @foreach ($users as $product)
        <tr>
            <td>{{ $product->username }}</td>
            <td>{{ $product->email }}</td>
           <!--  <td>{{ $product->assignee }}</td>
            <td>${{ $product->assignor }}</td>
            @if($product->dealine)
            <td>${{ $product->deadline }}</td>
            @endif -->
           <!--  <td>
                <a class="btn btn-sm btn-success" href="{{ action('ProductsController@edit', ['id' => $product->id]) }}">Edit</a>

                <form style="display:inline-block" action="{{ action('ProductsController@destroy', ['id' => $product->id]) }}" method="POST">
                    @method('DELETE')
                    @csrf
                    <button class="btn btn-sm btn-danger"> Delete</button>
                </form>
            </td> -->
        </tr>
        @endforeach
    </tbody>
</table>

{{ $products->links() }}

<p>
    Displaying {{$products->count()}} of {{ $products->total() }} product(s).
</p>

@endsection