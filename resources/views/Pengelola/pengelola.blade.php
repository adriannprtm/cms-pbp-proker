@extends('master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
@endsection

@section('scripts')
    <!-- Tambahkan jQuery dan DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <!-- Inisialisasi DataTables dengan Filter -->
    <script>
        $(document).ready(function () {
            $('#pengelola-table').DataTable();
        });
    </script>
@endsection

@section('konten')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengelola</h1>
    </div>
    
    <div>

        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createModal">Tambah Pengelola</button>
        <table class="table table-bordered" id="pengelola-table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengelolas as $index => $pengelola)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $pengelola['email'] }}</td>
                    <td>{{ $pengelola['name'] }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $pengelola['id'] }}">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal{{ $pengelola['id'] }}">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

<!-- Modal Tambah Pengelola -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Pengelola</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('pengelola.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Nama Depan</label>
                        <input type="text" class="form-control" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Belakang</label>
                        <input type="text" class="form-control" name="lastName">
                    </div>
                    <div class="form-group">
                        <label>Foto Profil</label>
                        <input type="file" class="form-control-file" name="image" accept="image/*">
                        <small class="form-text text-muted">Upload foto dengan format JPG, JPEG, atau PNG (max: 2MB)</small>
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

<!-- Modal Edit -->
@foreach($pengelolas as $pengelola)
    <div class="modal fade" id="editModal{{ $pengelola['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Edit Banner</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form action="/pengelola/{{ $pengelola['id'] }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Depan</label>
                    <input type="text" name="firstName" id="firstName" value="{{ $pengelola['firstName'] }}" class="form-control @error('firstName') is-invalid @enderror">
                    @error('firstName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Belakang</label>
                    <input type="text" name="lastName" id="lastName" value="{{ $pengelola['lastName'] }}" class="form-control @error('lastName') is-invalid @enderror">
                    @error('lastName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="imageUrl" class="form-label">Foto Profil</label>
                    <input type="file" class="form-control-file @error('imageUrl') is-invalid @enderror" accept="image/*" id="imageUrl" name="image">
                    <small class="form-text text-muted">Upload foto dengan format JPG, JPEG, atau PNG (max: 2MB)</small>
                    @error('imageUrl')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(isset($pengelola['imageUrl']))
                        <img src="{{$pengelola['imageUrl']}}" alt="Current pengelola Image" style="max-height: 100px;" class="img-thumbnail">
                    @else
                        <p>No Image</p>
                    @endif
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

@foreach($pengelolas as $pengelola)
<!-- Modal Delete-->
<div class="modal fade" id="deleteModal{{ $pengelola['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Pengelola</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('pengelola.destroy', $pengelola['id']) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <span>Apakah Anda Yakin Ingin Menghapus Data Ini?</span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection