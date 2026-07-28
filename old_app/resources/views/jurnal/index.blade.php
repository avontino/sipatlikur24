
@extends('layouts.master')

@section('content')

<section class="content-header">
      <div class="container-fluid">
      	
	@if(session('sukses'))
	<div class="alert alert-success alert-dismissible" role="alert">
										<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										<i class="fa fa-check-circle"></i> 

  	{{session('sukses')}}
	</div>
	@endif
@if(session('gagal'))
  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <i class="fa fa-check-circle"></i> 

    {{session('gagal')}}
  </div>
   @endif

	</div>
	
	<div class="card">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header">
									<h3 class="panel-title">Data Jurnal SMAN TARUNA NALA Malang</h3>

								</div>

								<div class="card-body">	
								@if($data_jurnal->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td>
											</tr>
										@else
                                    
								    <form class="form-inline" method="GET" action="/jurnal">
								       @if(auth()->user()->role=='admin')
								  	<button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#exampleModal">Tambah Jurnal</button>

								    <!--  <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button> -->
                                        @endif

                                    <input name="crtgl" type="date" class="form-control-sm mr-sm-2"> 
                    <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="tanggal">Filter Tanggal</button>

                            @if(auth()->user()->role=='admin' OR auth()->user()->role=='lihat')
					<select name="kelas" class="form-control-sm mr-sm-2" >
                    @foreach($ke_las as $kelas)
                    <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                    @endforeach                 
                    </select>        
                    <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="kelas">Filter Kelas</button>
							@endif
                                        @if(auth()->user()->role=='admin')
								    <a href="/jurnal/export" class="btn btn-sm btn-primary mr-sm-2">Excel Semua Tanggal</a>

						<input name="filterexport" class="form-control mr-sm-2" type="date" >
								    <a href="/jurnal/exporttanggal" class="btn btn-sm btn-primary mr-sm-2">Excel Per Tanggal</a>
                                        @endif
												@if(auth()->user()->role=='admin')
    <button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#rekapModal">Rekap Jurnal Guru</button>
@endif

                        @if(auth()->user()->role=='ketuakelas')
                            <a href="/jurnal/editsexport" class="btn btn-sm btn-primary mr-sm-4">Download Rekap (Excel)</a>
                        

                        <label>
                            Menu Download ini dapat digunakan dengan membuka Browser, buka Website sidasa.sman10malang.sch.id kemudian login di website tersebut
                        </label>



                        @endif
								  </form>
								</br>
											 
									 <table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
											    @if(auth()->user()->role=='admin')
												<th>AKSI</th>
												@endif
												<th>KELAS</th>
												<th>KET GURU</th>
												<th>PENUGASAN</th>
												<th>JAM KE</th>
												<th>JUMLAH JAM</th>
												<th>MATA PELAJARAN</th>
												<th>GURU</th>
												<th>MATERI</th>
												<th>CATATAN</th>
												<th>WAKTU</th>
												
												
											</tr>
										</thead>
										<tbody>

											@foreach($data_jurnal as $jurnal)
											<tr style="@if($jurnal->materi == 'Jam Kosong') background-color: #ffcccc; @elseif($jurnal->penugasan == 'Ada') background-color: #ffffcc; @endif">
												@if(auth()->user()->role=='admin')
												<td>
													<!-- <a href="/jurnal/{{$jurnal->id}}/edit" class="btn btn-warning btn-sm">Edit</a> -->
													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$jurnal->id}}"
													data-mykelas="{{$jurnal->kelas}}"
											data-myket_guru_mapel="{{$jurnal->ket_guru_mapel}}"
													data-mypenugasan="{{$jurnal->penugasan}}"
													data-myjamke="{{$jurnal->jamke}}"
													data-myjumlahjam="{{$jurnal->jumlahjam}}"
													data-mymapel="{{$jurnal->mapel}}"
													data-myguru="{{$jurnal->guru}}"
												
													data-mymateri="{{$jurnal->materi}}"
													data-mycatatan="{{$jurnal->catatan}}"

									data-toggle="modal" data-target="#editjurnal">Edit</button>

													<a href="/jurnal/{{$jurnal->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
												</td>
												@endif
												<td>{{$jurnal->kelas}}</td>
												<td>
													@if($jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->ket_guru_mapel}}
													@elseif($jurnal->ket_guru_mapel=='Hadir')
													<font color="#000000">{{$jurnal->ket_guru_mapel}}
													@endif
												</td>
												<td>
													@if($jurnal->penugasan=='Tidak Ada'&&$jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->penugasan}}
													@elseif($jurnal->penugasan=='Ada')
													<font color="#000000">{{$jurnal->penugasan}}
													@endif
												</td>
												<td>{{$jurnal->jamke}}</td>
												<td>{{$jurnal->jumlahjam}}</td>
												<td>{{$jurnal->mapel}}</td>
												<td>{{$jurnal->guru}}</td>
												
<!-- Menampilkan Materi dengan Badge Hijau jika Ada URL -->
    <td>
        @if($jurnal->materi_url)
            <a href="{{ $jurnal->materi_url }}" class="badge badge-success" target="_blank">
                {{ $jurnal->materi_text }}
            </a>
        @else
            <!-- Jika tidak ada URL, tampilkan teks biasa -->
            {{ $jurnal->materi_text }}
        @endif
    </td>
												<td>
													@if($jurnal->catatan=='Catatan')
													<font color="#000000">{{$jurnal->catatan}}
													@elseif($jurnal->catatan!='Catatan')
													<font color="#ff0000">{{$jurnal->catatan}}
													@endif
												</td>
												<td>{{$jurnal->created_at->format('d M Y - H:i:s')}}</td>
												
											</tr>
											@endforeach
											


										</tbody>
									</table>
								@endif
								</div>
							</div>
				</div>
			</div>
		</div>


		@if($data_jurnal->isEmpty())
											<tr>
												<!-- <td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td> -->
											</tr>
										@else


<!-- Modal untuk Rekap Jurnal Guru -->
<div class="modal fade" id="rekapModal" tabindex="-1" role="dialog" aria-labelledby="rekapModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rekapModalLabel">Rekap Jurnal Guru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk memilih rentang tanggal -->
                <form id="rekapForm" method="GET" action="/jurnal">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Lihat Rekap</button>
                </form>

                <!-- Tombol Export ke Excel dan PDF dalam Modal -->
                <div class="mt-3">
                    <!-- Tombol Excel -->
                    <a href="#" id="exportExcelBtn" class="btn btn-success">Export ke Excel</a>
                    <!-- Tombol PDF -->
                    <a href="#" id="exportPdfBtn" class="btn btn-danger">Export ke PDF</a>
                </div>
            </div>
        </div>
    </div>
</div>




										<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Tambah Jurnal</h5>
				    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <form action="/jurnal/createsusul" method="POST"> 
				        	{{csrf_field()}}
				        	<div class="form-group">
						    <label for="exampleInputEmail1">TANGGAL</label>
						    <input name="tgl" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
						  </div>
						  <div class="form-group">
						    <label >Kelas</label>
						    <select name="kelas" class="form-control" >
						    @foreach($ke_las as $kelas)
						    	<option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
						    @endforeach						    	
						    </select>

						  	</div>
						  <div class="form-group">
						    <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
						    <select name="ket_guru_mapel" class="form-control" id="exampleFormControlSelect1">
						      <option value="Hadir">Hadir</option>
						      <option value="Tidak Masuk">Tidak Masuk</option>
						    </select>
						  </div>					
						  <div class="form-group">
						    <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
						    <select name="penugasan" class="form-control" id="exampleFormControlSelect1">
						      <option value="Tidak Ada">Tidak Ada</option>
						      <option value="Ada">Ada</option>
						    </select>
						  </div>
<!-- 						  <div class="form-group">
						    <label for="exampleInputEmail1">Jam Ke</label>
						    <input name="jamke" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Diisi 1-11">
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">Jumlah Jam</label>
						    <input name="jumlahjam" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Jumlah Jam">
						  </div> -->

						  <div class="form-group">
						    <label >Mata Pelajaran</label>
						    <select name="mapel" class="form-control" >
						    @foreach($ma_pel as $mapel)
						    	<option value="{{$mapel->mapel}}">{{$mapel->mapel}}</option>
						    @endforeach						    	
						    </select>

						  	</div>

						  	<div class="form-group">
						    <label >Guru</label>
						    <select name="guru" class="form-control" >
						    @foreach($gu_ru as $guru)
						    	<option value="{{$guru->guru}}">{{$guru->guru}}</option>
						    @endforeach						    	
						    </select>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Materi</label>
						    <textarea name="materi" class="form-control" id="exampleFormControlTextarea1" rows="3" >Materi</textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Catatan</label>
						    <textarea name="catatan" class="form-control" id="exampleFormControlTextarea1" rows="3" >Catatan</textarea>
						  </div>

						  <div class="form-group">
						    <input name="waktu" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Waktu" value="{{ date('H:i:s') }}">
						  </div>

						  <div class="form-group">
                
               			 <input name="guru_id" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" >
                
              				</div>

						  
						  
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Submit</button>
						</form>
			</div>
		 </div>
	</div>
</div>


<!-- Modal Edit -->
<div class="modal fade" id="editjurnal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Edit Jurnal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/jurnal/update" method="post"> 
				        	{{csrf_field()}}
			<input type="hidden" name="jurnalid" id="jurnalid" value="">
						   
				<div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" id="kelas">
            
                @foreach($ke_las as $kelas)

              @if( $kelas->kelas == $jurnal->kelas )
                      <option value="{{ $kelas->kelas }}" selected="selected" > {{ $kelas->kelas }}</option>
                  @else
                      <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
                  @endif
   
                @endforeach   
                          
                </select>

                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
                <select name="ket_guru_mapel" class="form-control" id="ket_guru_mapel">
                  <option value="Hadir" @if($jurnal->ket_guru_mapel=='Hadir') selected @endif>Hadir</option>
                  <option value="Tidak Masuk" @if($jurnal->ket_guru_mapel=='Tidak Masuk') selected @endif>Tidak Masuk</option>
                </select>
              </div>          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
                <select name="penugasan" class="form-control" id="penugasan">
                  <option value="Tidak Ada" @if($jurnal->penugasan=='Tidak Ada') selected @endif>Tidak Ada</option>
                  <option value="Ada" @if($jurnal->penugasan=='Ada') selected @endif>Ada</option>
                </select>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jurnal->jamke}}">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jurnal->jumlahjam}}">
              </div>
              <div class="form-group">
                <label >Mata Pelajaran</label>
                <select name="mapel" class="form-control" id="mapel">
                @foreach($ma_pel as $mapel)
                  @if( $mapel->mapel == $jurnal->mapel )
                      <option value="{{ $mapel->mapel }}" selected="selected"> {{ $mapel->mapel }}</option>
                    @else
                      <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
                    @endif
                @endforeach                 
                </select>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <select name="guru" class="form-control" id="guru">
                @foreach($gu_ru as $guru)
                  @if( $guru->guru == $jurnal->guru )
                      <option value="{{ $guru->guru }}" selected="selected"> {{ $guru->guru }}</option>
                    @else
                      <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
                    @endif
                @endforeach                 
                </select>
             
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Materi</label>
                <textarea name="materi" class="form-control" id="materi" rows="3" >{{$jurnal->materi}}</textarea>
              </div>
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Catatan</label>
                <textarea name="catatan" class="form-control" id="catatan" rows="3" >{{$jurnal->catatan}}</textarea>
              </div>
						 						  
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>
</div>



@endif



@endsection


