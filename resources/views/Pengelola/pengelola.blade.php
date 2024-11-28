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

<!-- Modal -->
<!-- <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalTitle" aria-hidden="true">
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
</div> -->
@endsection