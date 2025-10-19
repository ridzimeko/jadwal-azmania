@php
    $styleTd = 'border: 1px solid #000000; text-align:center; vertical-align: center;';
    $styleHari = 'border: 1px solid #000000; height: 28px; width: 40px; background-color:#fee685; vertical-align: center;';
    $styleHead = 'border: 1px solid #000000; height: 28px; width:140px; background-color: #902C8E; color: #ffffff; text-align:center; vertical-align: center;';
@endphp

<table>
    <tr>
        <td rowspan="5">
            {{-- <img src="{{ public_path('images/logo.png') }}" width="80"> --}}
        </td>
        <td colspan="{{ 1 + $kelasList->count() }}" style="text-align:center; font-weight:bold; font-size:16px;">
            PONDOK PESANTREN AZMANIA
        </td>
    </tr>
    <tr>
        <td colspan="{{ 1 + $kelasList->count() }}" style="text-align:center;">
            {{ $tingkat == 'SMP' ? 'SEKOLAH MENENGAH PERTAMA' : 'MADRASAH ALIYAH' }} PONOROGO
        </td>
    </tr>
    <tr>
        <td colspan="{{ 1 + $kelasList->count() }}" style="text-align:center;">
            Jl. Azmania No. 2, Kel. Ronowijayan, Kec. Siman, Kab. Ponorogo
        </td>
    </tr>
    <tr>
        <td colspan="{{ 1 + $kelasList->count() }}" style="text-align:center;">
            Email: azmaniapo@gmail.com | Website: www.azmania.sch.id
        </td>
    </tr>
    <tr><td colspan="{{ 1 + $kelasList->count() }}">&nbsp;</td></tr>
</table>

@foreach ($jadwalPerHari as $hari => $jadwal)
    <table cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr style="height: 60px; text-align:center; font-weight:bold;">
                <th colspan="{{ 2 + $kelasList->count() }}" align="center" style="{{ $styleHari }}">{{ $hari }}</th>
            </tr>
            <tr>
                <th style="{{ $styleHead }}">No</th>
                <th style="{{ $styleHead }}">Jam</th>
                @foreach ($kelasList as $kelas)
                    <th style="{{ $styleHead }}">{{ $kelas->nama_kelas }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($jadwal as $jamLabel => $items)
                <tr>
                    <td style="{{ $styleTd }}">{{ $no++ }}</td>
                    <td style="{{ $styleTd }}">{{ $jamLabel }}</td>
                    @foreach ($kelasList as $kelas)
                        @php
                        $item = $items->firstWhere('kelas_id', $kelas->id);
                        $bg = $item->guru->warna ?? '#ffffff';
                        $text = \App\Helpers\ColorHelper::getTextColor($bg);
                        @endphp
                        <td bgcolor="{{ $bg }}" style="{{ $styleTd }} font-weight: 400; color: {{ $text }};">
                            @if ($item)
                                {{ $item->mataPelajaran->nama_mapel }}<br>
                                {{ $item->guru->nama_guru }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
