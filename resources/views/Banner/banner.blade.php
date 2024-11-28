@extends('master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
@endsection

@section('scripts')
    @include('sweetalert::alert')
    <!-- Tambahkan jQuery dan DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <!-- Inisialisasi DataTables dengan Filter -->
    <script>
        $(document).ready(function () {
            $('#banner-table').DataTable();
        });
    </script>
@endsection

@section('konten')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Banner</h1>
    </div>
    
    <div>
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createModal">Tambah Banner</button>
        <table class="table table-bordered" id="banner-table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Gambar</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banners as $index => $banner)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $banner['nama'] }}</td>
                    <td class="text-center">
                        @if(!empty($banner['gambar']))
                            <img src="{{ $banner['gambar'] }}" alt="{{ $banner['nama'] }}" 
                                 style="max-height: 100px;" class="img-thumbnail">
                        @else
                            No Image
                        @endif
                    </td>
                    <td>{{ $banner['status'] }}</td>
                    <td class="text-center">
                        <!-- <a href="/edit/{{ $banner['id'] }}" class="btn btn-warning btn-sm">Edit</a> -->
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $banner['id'] }}">Edit</button>
                        <!-- <button class="btn btn-info btn-sm edit-banner" data-id="{{ $banner['id'] }}"></button> -->
                        <!-- <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteBanner{{ $banner['id']}}">Delete</button> -->
                        <form action="{{ url('/banner/'.$banner['id']) }}" method="POST" 
                              class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

<!-- Modal Create -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Tambah Banner</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar:</label>
                    <input type="file" class="form-control" id="gambar" name="gambar">
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="publish">Publish</option>
                        <option value="unpublish">Unpublish</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
  </div>
</div>

@foreach($banners as $banner)
    <!-- Modal Edit-->
    <div class="modal fade" id="editModal{{ $banner['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Edit Banner</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form action="/banner/{{ $banner['id'] }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" id="nama" value="{{ $banner['nama'] }}" class="form-control @error('nama') is-invalid @enderror">
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
                    @if(isset($banner['gambar']))
                        <img src="{{$banner['gambar']}}" alt="Current Banner Image" 
                            style="max-height: 100px;" class="img-thumbnail">
                    @else
                        <p>No Image</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="{{ $banner['status'] }}" hidden>{{ $banner['status']}}</option>
                        <option value="publish" {{ $banner['status'] == 'publish' ? 'selected' : '' }}>Publish</option>
                        <option value="unpublish" {{ $banner['status'] == 'unpublish' ? 'selected' : '' }}>Unpublish</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </form>
        </div>
        </div>
    </div>
    </div>
@endforeach
@endsection