@extends('layouts.sidebar')

@section('page-title', '➕ Buat Tiket Baru')

@section('title', 'Buat Tiket')

@section('content')
<div class="container-fluid">
    @if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Validasi gagal:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label for="title" class="form-label">Judul</label>
         <input type="text" name="title" id="title"
             class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title') }}" placeholder="Contoh: Meja rusak saat pemasangan">
        @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="category" class="form-label">Kategori</label>
        <select name="category_id" id="category"
                class="form-select @error('category_id') is-invalid @enderror">
            <option value="">Pilih Kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('category_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Kategori membantu tim mengarahkan ticket ke agent yang tepat.</div>
    </div>

    <div class="mb-3">
        <label for="priority" class="form-label">Prioritas</label>
        <select name="priority" id="priority"
                class="form-select @error('priority') is-invalid @enderror">
            <option value="">Pilih Prioritas</option>
            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
        </select>
        @error('priority')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Gunakan prioritas tinggi untuk isu yang kritikal.</div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea name="description" id="description" rows="5"
              class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan masalah, langkah yang sudah dicoba, dan harapan perbaikan.">{{ old('description') }}</textarea>
        @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="attachments" class="form-label">Lampiran (opsional)</label>
         <input type="file" name="attachments[]" id="attachments" class="form-control"
             multiple>
         <div class="form-text">Format umum didukung (PDF, JPG, PNG). Maks per file 2MB.</div>
    </div>

    <button type="submit" class="btn btn-primary">Kirim Ticket</button>
</form>
@endsection
