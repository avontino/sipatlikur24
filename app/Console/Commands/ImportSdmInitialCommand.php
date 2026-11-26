<?php

namespace App\Console\Commands;

use App\Models\SdmPegawai;
use App\Models\User;
use Illuminate\Console\Command;

class ImportSdmInitialCommand extends Command
{
    protected $signature = 'sdm:import-initial';
    protected $description = 'Import 44 data Guru dan 5 data Tendik resmi SMPN 24 Malang (NIP, Status, Pangkat, Golongan, Tgl Lahir, WA) dan cocokkan dengan akun SIPATLIKUR';

    public function handle()
    {
        $guruData = [
            ['nama' => 'Drs. Teguh Edy Purwanta', 'nip' => '196710202000031006', 'status' => 'Guru PNS', 'pangkat' => 'Pembina Tk.I', 'golongan' => 'IV/b', 'tgl' => '1967-10-20', 'wa' => '082230967721', 'mapel' => 'Matematika', 'jk' => 'L', 'jp' => 24, 'kelas' => ['IX-A']],
            ['nama' => 'Yuliati, S.Pd', 'nip' => '196808281997032008', 'status' => 'Guru PNS', 'pangkat' => 'Pembina Tk.I', 'golongan' => 'IV/b', 'tgl' => '1968-08-28', 'wa' => '082145345228', 'mapel' => 'IPA', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D']],
            ['nama' => 'Arjo Setyo Adi Winarno, S.Pd', 'nip' => '196703041993031017', 'status' => 'Guru PNS', 'pangkat' => 'Pembina Tk.I', 'golongan' => 'IV/b', 'tgl' => '1967-03-04', 'wa' => '081252533550', 'mapel' => 'Matematika', 'jk' => 'L', 'jp' => 24, 'kelas' => ['IX-B', 'IX-C', 'IX-D', 'IX-E']],
            ['nama' => 'Sri Hartati, S.Pd', 'nip' => '197102271997022004', 'status' => 'Guru PNS', 'pangkat' => 'Pembina Tk.I', 'golongan' => 'IV/b', 'tgl' => '1971-02-27', 'wa' => '089610492979', 'mapel' => 'Bahasa Inggris', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F']],
            ['nama' => 'Dra. Hairina', 'nip' => '196902221990032001', 'status' => 'Guru PNS', 'pangkat' => 'Pembina Tk.I', 'golongan' => 'IV/b', 'tgl' => '1969-02-22', 'wa' => '085755080180', 'mapel' => 'Pend. Agama Islam', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Dra. Elly Hartatiek, M.Pd', 'nip' => '196812312005012057', 'status' => 'Guru PNS', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'tgl' => '1968-12-31', 'wa' => '081334319515', 'mapel' => 'IPS', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F']],
            ['nama' => 'Ida Fitriyah, S.P, S.Pd', 'nip' => '197610052007012016', 'status' => 'Guru PNS', 'pangkat' => 'Penata Tk.I', 'golongan' => 'III/d', 'tgl' => '1976-10-05', 'wa' => '08113130510', 'mapel' => 'IPA', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D']],
            ['nama' => 'Dra. Elsye Sandra Wijaya', 'nip' => '196801092007012014', 'status' => 'Guru PNS', 'pangkat' => 'Penata Tk.I', 'golongan' => 'III/d', 'tgl' => '1968-01-09', 'wa' => '089618420961', 'mapel' => 'IPS', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F']],
            ['nama' => 'Ainur Romlah, S.Pd', 'nip' => '197901082008012020', 'status' => 'Guru PNS', 'pangkat' => 'Penata Tk.I', 'golongan' => 'III/d', 'tgl' => '1979-01-08', 'wa' => '082232267149', 'mapel' => 'Bahasa Inggris', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F']],
            ['nama' => 'Lina Setyaningrum, S.Pd', 'nip' => '198206242010012019', 'status' => 'Guru PNS', 'pangkat' => 'Penata Tk.I', 'golongan' => 'III/d', 'tgl' => '1982-06-24', 'wa' => '085229985761', 'mapel' => 'IPA', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D']],
            ['nama' => 'Titik Dewi Utami, S.Pd', 'nip' => '197604182011012001', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1976-04-18', 'wa' => '081252148157', 'mapel' => 'IPS', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F']],
            ['nama' => 'Dwi Rahmawati, S.Pd', 'nip' => '198107222014072004', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1981-07-22', 'wa' => '081805006376', 'mapel' => 'Bahasa Inggris', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F']],
            ['nama' => 'Mimin Ernawati, S.Pd', 'nip' => '199403072019022004', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1994-03-07', 'wa' => '085755969673', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D']],
            ['nama' => 'Fifi Suryani, S.Pd', 'nip' => '199410122019022006', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1994-10-12', 'wa' => '088741009801', 'mapel' => 'Bimbingan Konseling', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E']],
            ['nama' => 'Noveriana Kartikasari, S.Pd', 'nip' => '199511152019022003', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1995-11-15', 'wa' => '081333384968', 'mapel' => 'Seni Budaya', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => "A'im Muflihatul Habibah, S.Pd", 'nip' => '199605152019022002', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1996-05-15', 'wa' => '085855105707', 'mapel' => 'Bahasa Daerah Jawa', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Marta Adi Prayitno, S.Pd', 'nip' => '198703282019021003', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1987-03-28', 'wa' => '085733694233', 'mapel' => 'Matematika', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D']],
            ['nama' => 'Akhmad Agung Firmansyah, S.Pd', 'nip' => '199304062019021001', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1993-04-06', 'wa' => '081331307113', 'mapel' => 'PJOK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Avo Satriyatma, S.Pd', 'nip' => '198911112020121005', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1989-11-11', 'wa' => '085649603553', 'mapel' => 'TIK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Endah Suci Permatasari, M.Pd', 'nip' => '199508192020122008', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1995-08-19', 'wa' => '082231992122', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D']],
            ['nama' => 'Zenitha Rizki Ardiantika, S.Pd', 'nip' => '199509182020122007', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1995-09-18', 'wa' => '087755352435', 'mapel' => 'Bimbingan Konseling', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E']],
            ['nama' => 'Imam Ghozali Kusuma Andryan Firdaus, S.Pd', 'nip' => '199405052020121005', 'status' => 'Guru PNS', 'pangkat' => 'Penata Muda Tk.I', 'golongan' => 'III/b', 'tgl' => '1994-05-05', 'wa' => '082245456777', 'mapel' => 'PJOK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Vita Arwidiah Raharizka, S.P', 'nip' => '198211202022212012', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1982-11-20', 'wa' => '081937780098', 'mapel' => 'Seni Budaya', 'jk' => 'P', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Anita Rahayu Ekawati, S.Pd', 'nip' => '198309132022212018', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1983-09-13', 'wa' => '081230644187', 'mapel' => 'Bimbingan Konseling', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E']],
            ['nama' => 'Erriendah Listiani, S.Si', 'nip' => '197909052022212014', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1979-09-05', 'wa' => '082335999595', 'mapel' => 'IPA', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-E', 'VII-F', 'VII-G', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Ir. I Made Argita', 'nip' => '196807192022211004', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1968-07-19', 'wa' => '0818385404', 'mapel' => 'TIK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Juli Endah Setiyarini, S.Pd', 'nip' => '198007242022212024', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1980-07-24', 'wa' => '085934931094', 'mapel' => 'Bimbingan Konseling', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-F', 'VII-G', 'VIII-F', 'VIII-G', 'IX-F', 'IX-G']],
            ['nama' => 'Dyah Amelya Miswati Soekram, S.Pd', 'nip' => '197608122023212004', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1976-08-12', 'wa' => '082332704980', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D']],
            ['nama' => 'Sira Meniria Tafonao, S.Pd', 'nip' => '198002102023212011', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1980-02-10', 'wa' => '082141481672', 'mapel' => 'Matematika', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D']],
            ['nama' => 'Wega Nur Fitri Handayani, S.Pd', 'nip' => '199506162023212024', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1995-06-16', 'wa' => '0895336767761', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-E', 'VII-F', 'VII-G', 'VIII-E']],
            ['nama' => 'Sriatin, S.Ag', 'nip' => '197110112023212002', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1971-10-11', 'wa' => '081259852115', 'mapel' => 'Pend. Agama Islam', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Anggi Pratama, S.Pd.I', 'nip' => '199101082023211005', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1991-01-08', 'wa' => '083840481324', 'mapel' => 'Pend. Agama Islam', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'M. Fernanda Adi Pradana, M.Pd', 'nip' => '199709302023211004', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1997-09-30', 'wa' => '081553991035', 'mapel' => 'Bahasa Indonesia', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VIII-F', 'VIII-G', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Widyatama Cahya Cipto, S.Pd', 'nip' => '198911052024211010', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1989-11-05', 'wa' => '082141535721', 'mapel' => 'TIK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'Siti Rohmawati, S.E', 'nip' => '197706022024212003', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1977-06-02', 'wa' => '082139056229', 'mapel' => 'IPS', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-G', 'VIII-G', 'IX-G']],
            ['nama' => 'Maria Ignatia Carolina Bessu Nande, S.Pd', 'nip' => '199507312024212025', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1995-07-31', 'wa' => '089651464627', 'mapel' => 'Bahasa Inggris', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-G', 'VIII-G', 'IX-G']],
            ['nama' => 'Mukhammad Nur, S.H', 'nip' => '197908242025211008', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1979-08-24', 'wa' => '085707982533', 'mapel' => 'Pendidikan Pancasila', 'jk' => 'L', 'jp' => 24, 'kelas' => ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G']],
            ['nama' => 'Mohammad Miftakhur Ramadhoni, S.Pd', 'nip' => '199912212025211014', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1999-12-21', 'wa' => '0817037444789', 'mapel' => 'PJOK', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'Hafida Nurhidayati, S.Pd', 'nip' => '199912112025212021', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1999-12-11', 'wa' => '089510084205', 'mapel' => 'IPS', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VIII-A', 'VIII-B']],
            ['nama' => 'Umi Farah Nur Imama, S.Pd', 'nip' => '200008192025212016', 'status' => 'Guru PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '2000-08-19', 'wa' => '082128707579', 'mapel' => 'Pendidikan Pancasila', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'Nada Barlian Fanikia, S.Pd', 'nip' => '-', 'status' => 'Guru Honorer', 'pangkat' => '-', 'golongan' => '-', 'tgl' => '2000-02-12', 'wa' => '085707726949', 'mapel' => 'Matematika', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'Tri Hartatik Ayu Setyaningtyas, S.Pd', 'nip' => '-', 'status' => 'Guru Honorer', 'pangkat' => '-', 'golongan' => '-', 'tgl' => '1999-08-15', 'wa' => '082264222184', 'mapel' => 'Matematika', 'jk' => 'P', 'jp' => 24, 'kelas' => ['VIII-E', 'VIII-F', 'VIII-G']],
            ['nama' => 'Fandy Romadhoni, S.Pd., M.Pd.', 'nip' => '-', 'status' => 'Guru Honorer', 'pangkat' => '-', 'golongan' => '-', 'tgl' => '1996-02-11', 'wa' => '085745452630', 'mapel' => 'Bahasa Daerah Jawa', 'jk' => 'L', 'jp' => 24, 'kelas' => ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G']],
            ['nama' => 'Ruth Yuniarini, S.PdK', 'nip' => '-', 'status' => 'Guru Honorer-Non Induk', 'pangkat' => '-', 'golongan' => '-', 'tgl' => '1985-06-15', 'wa' => '085231737906', 'mapel' => 'Pend. Agama Kristen', 'jk' => 'P', 'jp' => 12, 'kelas' => ['VII-A', 'VIII-A', 'IX-A']],
        ];

        $tendikData = [
            ['nama' => 'Hanna Ayu Rahmawati, S.Pd', 'nip' => '199802202025212030', 'status' => 'Tendik PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1998-02-20', 'wa' => '08883899528', 'jabatan' => 'Tenaga Administrasi', 'jk' => 'P'],
            ['nama' => 'Siti Ilifdiani Maghfuro, S.S', 'nip' => '199111042025212032', 'status' => 'Tendik PPPK', 'pangkat' => 'PPPK', 'golongan' => 'IX', 'tgl' => '1991-11-04', 'wa' => '081230046549', 'jabatan' => 'Tenaga Administrasi', 'jk' => 'P'],
            ['nama' => 'Indah Widyayanti', 'nip' => '198904112025212015', 'status' => 'Tendik PPPK', 'pangkat' => 'PPPK', 'golongan' => 'V', 'tgl' => '1989-04-11', 'wa' => '081933702288', 'jabatan' => 'Tenaga Administrasi', 'jk' => 'P'],
            ['nama' => 'Soegiono', 'nip' => '198302222025211032', 'status' => 'Tendik PPPK', 'pangkat' => 'PPPK', 'golongan' => 'V', 'tgl' => '1983-02-22', 'wa' => '08976627787', 'jabatan' => 'Pramubakti', 'jk' => 'L'],
            ['nama' => 'Tutut Agustiningsih, S.Pd', 'nip' => '197108242025212002', 'status' => 'Tendik PPPK', 'pangkat' => 'PPPK', 'golongan' => 'V', 'tgl' => '1971-08-24', 'wa' => '082330720408', 'jabatan' => 'Pramubakti', 'jk' => 'P'],
        ];

        $allUsers = User::whereIn('role', ['guru', 'tendik', 'kurikulum', 'kesiswaan', 'kepala'])->get();
        $matchedCount = 0;

        foreach ($guruData as $g) {
            $user = $this->findMatchingUser($g['nama'], $allUsers);
            if ($user) {
                $matchedCount++;
            }

            $cond = ($g['nip'] !== '-')
                ? ['nip' => $g['nip'], 'kategori' => 'guru']
                : ['nama' => $g['nama'], 'kategori' => 'guru'];

            SdmPegawai::updateOrCreate($cond, [
                'user_id' => $user ? $user->id : null,
                'kategori' => 'guru',
                'nama' => $user ? $user->name : $g['nama'],
                'nip' => $g['nip'],
                'status_kepegawaian' => $g['status'],
                'pangkat' => $g['pangkat'],
                'golongan' => $g['golongan'],
                'jabatan' => 'Guru Mata Pelajaran',
                'mapel' => $g['mapel'],
                'jenis_kelamin' => $g['jk'],
                'tanggal_lahir' => $g['tgl'],
                'no_wa' => $g['wa'],
                'beban_jp' => $g['jp'],
                'kelas_diampu' => $g['kelas'],
            ]);
        }

        foreach ($tendikData as $t) {
            $user = $this->findMatchingUser($t['nama'], $allUsers);
            if ($user) {
                $matchedCount++;
            }

            SdmPegawai::updateOrCreate(
                ['nip' => $t['nip'], 'kategori' => 'tendik'],
                [
                    'user_id' => $user ? $user->id : null,
                    'kategori' => 'tendik',
                    'nama' => $user ? $user->name : $t['nama'],
                    'nip' => $t['nip'],
                    'status_kepegawaian' => $t['status'],
                    'pangkat' => $t['pangkat'],
                    'golongan' => $t['golongan'],
                    'jabatan' => $t['jabatan'],
                    'jenis_kelamin' => $t['jk'],
                    'tanggal_lahir' => $t['tgl'],
                    'no_wa' => $t['wa'],
                    'beban_jp' => 0,
                ]
            );
        }

        $this->info("Import awal SDM selesai! 44 Guru + 5 Tendik disimpan ({$matchedCount} akun berhasil dicocokkan ke tabel users SIPATLIKUR).");
        return 0;
    }

    private function normalizeName(string $name): string
    {
        $clean = mb_strtolower($name);
        // Hapus gelar umum
        $clean = preg_replace('/\b(drs|dra|ir|s\.pd\.i|s\.pdk|s\.pd|m\.pd|s\.si|s\.ag|s\.e|s\.h|s\.p|s\.s)\b\.?/iu', '', $clean);
        $clean = preg_replace('/[^a-z0-9\s]/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', trim($clean));
        return $clean;
    }

    private function findMatchingUser(string $nama, $users)
    {
        $target = $this->normalizeName($nama);
        foreach ($users as $u) {
            if ($this->normalizeName($u->name) === $target) {
                return $u;
            }
        }
        // Fallback: kecocokan prefix 2 kata pertama (misal "Tri Hartatik Ayu Setyaningtyas" vs "Tri Hartatik Ayu S")
        $words = explode(' ', $target);
        if (count($words) >= 2) {
            $prefix = $words[0] . ' ' . $words[1];
            foreach ($users as $u) {
                if (str_starts_with($this->normalizeName($u->name), $prefix)) {
                    return $u;
                }
            }
        }
        return null;
    }
}
