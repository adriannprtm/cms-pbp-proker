@extends('master')
@section('konten')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Banner</title>
</head>
<body>
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('banners.update', $uid) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" value="{{ $nama }}" class="form-control @error('nama') is-invalid @enderror">
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" class="form-control @error('gambar') is-invalid @enderror" id="gambar" name="gambar">
                @error('gambar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br>
                @if(isset($gambar_url))
                    <img src="{{ $gambar_url }}" alt="Current Banner Image" 
                         style="max-height: 100px;" class="img-thumbnail">
                @else
                    <p>No Image</p>
                @endif
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="publish" {{ $status == 'publish' ? 'selected' : '' }}>Publish</option>
                    <option value="unpublish" {{ $status == 'unpublish' ? 'selected' : '' }}>Unpublish</option>
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('banners.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
@endsection