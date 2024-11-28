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
            $('#warna-table').DataTable();
        });
    </script>
@endsection

@section('konten')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Warna</h1>
    </div>
    
    <div>
        <table class="table table-bordered" id="warna-table">
            <thead>
                <tr>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Kode Warna</th>
                    <th class="text-center">Warna</th>
                    <th class="text-center">Update Warna</th>
                </tr>
            </thead>
            <tbody>
                @foreach($colors as $color)
                <tr>
                    <td>{{ $color['nama'] }}</td>
                    <td>{{ $color['code'] }}</td>
                    <td style="background-color: #{{$color['code']}};"></td>
                    <td class="text-center"><button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#editModal{{ $color['id'] }}">Ubah</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="editModal{{ $color['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Warna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('warna.update', $color['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="{{ $color['nama'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="code">Kode Warna</label>
                        <input type="color" class="form-control" id="code" name="code" value="#{{ $color['code'] }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection