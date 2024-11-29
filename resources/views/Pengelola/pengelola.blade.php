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
                        <a href="" class="btn btn-warning btn-sm">Edit</a>
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
@endsection