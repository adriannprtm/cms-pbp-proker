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
            $('#onboard-table').DataTable();
        });
    </script>
@endsection

@section('konten')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">onBoarding Page</h1>
    </div>
    
    <div>
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createModal">Tambah OnBoarding Konten</button>
        <table class="table table-bordered" id="onboard-table">
            <thead>
                <tr>
                    <th class="text-center">Title</th>
                    <th class="text-center">Gambar</th>
                    <th class="text-center">Deskripsi</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($onBoards as $onBoard)
                <tr>
                    <td>{{ $onBoard['title'] }}</td>
                    <td class="text-center">
                        @if(!empty($onBoard['gambar']))
                            <img src="{{ $onBoard['gambar'] }}" alt="{{ $onBoard['title'] }}"
                                style="max-height: 100px;" class="img-thumbnail">
                        @else
                            Tidak Ada Gambar
                        @endif
                    </td>
                    <td>{{ $onBoard['description'] }}</td>
                    <td class="text-center">{{ $onBoard['status'] }}</td>
                    <td class="text-center"><button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#editModal{{ $onBoard['id'] }}">Ubah</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @foreach($onBoards as $onBoard)
    <!-- Modal Edit-->
    <div class="modal fade" id="editModal{{ $onBoard['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Warna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/onBoard/{{ $onBoard['id'] }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ $onBoard['title'] }}" required>
                    </div>

                    <div class="form-group">
                    <label for="gambar" class="form-label">Gambar</label>
                        <input type="file" class="form-control @error('gambar') is-invalid @enderror" id="gambar" name="gambar">
                        @error('gambar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <br>
                        @if(isset($onBoard['gambar']))
                            <img src="{{$onBoard['gambar']}}" alt="Current onBoard Image" 
                                style="max-height: 100px;" class="img-thumbnail">
                        @else
                            <p>No Image</p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ $onBoard['description'] }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="{{ $onBoard['status'] }}" hidden>{{ $onBoard['status']}}</option>
                            <option value="publish" {{ $onBoard['status'] == 'publish' ? 'selected' : '' }}>Publish</option>
                            <option value="unpublish" {{ $onBoard['status'] == 'unpublish' ? 'selected' : '' }}>Unpublish</option>
                        </select>
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
@endforeach

<!-- Modal Create -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah On Boarding</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('onBoard.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="gambar">Gambar</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <input type="text" class="form-control" id="description" name="description" required>
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
@endsection