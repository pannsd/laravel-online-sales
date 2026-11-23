@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="row" style="justify-content: space-between;">
        <h1>Produk</h1>
        <a class="btn" href="{{ route('admin.products.create') }}">Tambah Produk</a>
    </div>

    <div class="card">
        <form method="GET" class="row">
            <input 
                class="input" 
                style="max-width: 320px;" 
                name="q" 
                value="{{ request('q') }}" 
                placeholder="Cari nama/SKU"
            >
            <button class="btn" type="submit">Cari</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <div class="row">
                                <a class="btn" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit" onclick="return confirm('Yakin ingin menghapus/menonaktifkan produk ini?')">
                                        Hapus/Nonaktif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 16px;">
            {{ $products->links() }}
        </div>
    </div>
@endsection