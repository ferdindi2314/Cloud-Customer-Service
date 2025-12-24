@extends('layouts.sidebar')

@section('page-title', '📂 Manajemen Kategori')

@section('title', 'Manajemen Kategori')

@section('content')
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Manajemen Kategori</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $c)
                    <tr>
                        <td>
                            @if(method_exists($categories, 'currentPage'))
                                {{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->slug }}</td>
                        <td>{{ $c->description }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $categories->links() }}
</div>
</div>
@endsection
