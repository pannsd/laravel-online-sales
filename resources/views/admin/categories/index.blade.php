@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <h1>Kategori</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <label>
                Nama Kategori
                <input class="input" name="name" required>
            </label>
            <button class="btn" type="submit">Tambah</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jumlah Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            <form class="row" method="POST" action="{{ route('admin.categories.update', $category) }}">
                                @csrf
                                @method('PUT')
                                <input class="input" style="max-width: 240px;" name="name" value="{{ $category->name }}">
                                <button class="btn" type="submit">Simpan</button>
                            </form>
                        </td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection