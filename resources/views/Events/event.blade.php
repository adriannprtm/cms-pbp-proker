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
            $('#event-table').DataTable();
        });
    </script>
@endsection

@section('konten')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Event</h1>
    </div>
    
    <div>

        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createModal">Tambah Event</button>
        <table class="table table-bordered" id="event-table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Judul</th>
                    <th class="text-center">Banner</th>
                    <th class="text-center">Manfaat</th>
                    <th class="text-center">Category</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $index => $event)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $event['title'] }}</td>
                    <td class="text-center">
                        @if(!empty($event['bannerUrl']))
                        <img src="{{ $event['bannerUrl'] }}" alt="{{ $event['bannerUrl'] }}" 
                        style="max-height: 100px;" class="img-thumbnail">
                        @else
                        No Image
                        @endif
                    </td>
                    <td>{{ $event['benefits'] }}</td>
                    <td>{{ $event['category'] }}</td>
                    <td class="text-center">
                        <a href="" class="btn btn-warning btn-sm">Edit</a>
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#detailModal{{ $event['id'] }}">Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@foreach($events as $event)
    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal{{ $event['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0">
      <!-- Modal Header -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="exampleModalLongTitle">Event Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Title</h6>
            <p class="text-dark mb-0">{{ $event['title'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Banner</h6>
            <p class="text-dark mb-0">
                @if(!empty($event['bannerUrl']))
                    <img src="{{ $event['bannerUrl'] }}" alt="{{ $event['bannerUrl'] }}" style="max-height: 100px;" class="img-thumbnail">
                @else
                    No Image
                @endif
            </p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Manfaat</h6>
            <p class="text-dark mb-0">{{ $event['benefits'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Kategori</h6>
            <p class="text-dark mb-0">{{ $event['category'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Deskripsi</h6>
            <p class="text-dark mb-0">{{ $event['description'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Dokumentasi</h6>
            <p class="text-dark mb-0">
                @if(!empty($event['documentationUrl']))
                    <img src="{{ $event['documentationUrl'] }}" alt="{{ $event['documentationUrl'] }}" style="max-height: 100px;" class="img-thumbnail">
                @else
                    No Image
                @endif
            </p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Galeri</h6>
            <p class="text-dark mb-0">
                @if(!empty($event['galleryUrls']))
                    <img src="{{ $event['galleryUrls'] }}" alt="{{ $event['galleryUrls'] }}" style="max-height: 100px;" class="img-thumbnail">
                @else
                    No Image
                @endif
            </p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Lokasi Event</h6>
            <p class="text-dark mb-0">{{ $event['location'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Status</h6>
            <p class="text-dark mb-0">{{ $event['status'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Timeline</h6>
            <p class="text-dark mb-0">{{ $event['timeline'] }}</p>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-muted font-weight-bold mb-0">Tipe</h6>
            <p class="text-dark mb-0">{{ $event['type'] }}</p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endforeach

@endsection