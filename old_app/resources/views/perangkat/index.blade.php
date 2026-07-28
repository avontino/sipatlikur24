@extends('layouts.master')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif

        <div class="card">
            <div class="row">
                <div class="col-md-12">
                    
                        <div class="card-header">
                            <h3 class="panel-title">Perangkat Pembelajaran</h3>
                        </div>
                        
                <div class="card-body">
                  @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                <form class="form-inline ml-auto" action="/perangkat" method="get">
                         
                            <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="sinkron">Sinkron</button>
                    </form>
                  @endif
                    <br>

    <table id="example3" class="table table-bordered">
        <thead>
            <tr>
            <th>No</th>
                    <th>Guru</th>
                    <th>TP/ATP</th>
                    <th>Modul Ajar</th>
                    <th>Media Pembelajaran</th>
                    <th>Rencana Penilaian</th>
                    <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <!-- resources/views/perangkat/index.blade.php -->

<!-- resources/views/perangkat/index.blade.php -->

@foreach ($perangkats as $key => $perangkat)
    <tr>
        <td>{{ $key + 1 }}</td>
        <td>{{ $perangkat->guru }}</td>

        <!-- TP/ATP -->
        <td>
            @if($perangkat->tp == 'belum')
                <i class="fas fa-times-circle" style="color: red;"></i> <!-- Ikon jika "belum" -->
            @else
                <a href="{{ $perangkat->tp }}" class="badge badge-success" target="_blank">LINK</a> <!-- Badge untuk "LINK" -->
            @endif
        </td>

        <!-- Modul Ajar -->
        <td>
            @if($perangkat->modul == 'belum')
                <i class="fas fa-times-circle" style="color: red;"></i> <!-- Ikon jika "belum" -->
            @else
                <a href="{{ $perangkat->modul }}" class="badge badge-success" target="_blank">LINK</a> <!-- Badge untuk "LINK" -->
            @endif
        </td>

        <!-- Media Pembelajaran -->
        <td>
            @if($perangkat->media == 'belum')
                <i class="fas fa-times-circle" style="color: red;"></i> <!-- Ikon jika "belum" -->
            @else
                <a href="{{ $perangkat->media }}" class="badge badge-success" target="_blank">LINK</a> <!-- Badge untuk "LINK" -->
            @endif
        </td>

        <!-- Rencana Penilaian -->
        <td>
            @if($perangkat->penilaian == 'belum')
                <i class="fas fa-times-circle" style="color: red;"></i> <!-- Ikon jika "belum" -->
            @else
                <a href="{{ $perangkat->penilaian }}" class="badge badge-success" target="_blank">LINK</a> <!-- Badge untuk "LINK" -->
            @endif
        </td>

        <td>
            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" 
                    data-id="{{ $perangkat->id }}" 
                    data-guru="{{ $perangkat->guru }}"
                    data-tp="{{ $perangkat->tp }}"
                    data-modul="{{ $perangkat->modul }}"
                    data-media="{{ $perangkat->media }}"
                    data-penilaian="{{ $perangkat->penilaian }}">
                Edit
            </button>
          @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
            <form action="{{ route('perangkat.destroy', $perangkat->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          @endif
        </td>
    </tr>
@endforeach


        </tbody>
    </table>

    </div>
                </div>
            </div>
        </div>
    </div>



    
</section>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Perangkat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Guru (Disabled) -->
                    <div class="form-group">
                        <label for="guru">Guru</label>
                        <input type="text" class="form-control" id="guru" name="guru" required disabled>
                    </div>
                    <!-- TP/ATP (Editable) -->
                    <div class="form-group">
                        <label for="tp">TP/ATP</label>
                        <input type="text" class="form-control" id="tp" name="tp" required>
                    </div>
                    <!-- Modul Ajar (Editable) -->
                    <div class="form-group">
                        <label for="modul">Modul Ajar</label>
                        <input type="text" class="form-control" id="modul" name="modul" required>
                    </div>
                    <!-- Media Pembelajaran (Editable) -->
                    <div class="form-group">
                        <label for="media">Media Pembelajaran</label>
                        <input type="text" class="form-control" id="media" name="media" required>
                    </div>
                    <!-- Rencana Penilaian (Editable) -->
                    <div class="form-group">
                        <label for="penilaian">Rencana Penilaian</label>
                        <input type="text" class="form-control" id="penilaian" name="penilaian" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection




