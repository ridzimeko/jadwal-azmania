<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal Pelajaran {{ $tingkat }}</title>
    <link rel="icon" href="{{ asset('icon-512.png') }}" type="image/png">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        /* START KOP SURAT */
        .kop-surat-container {
            width: 100%;
            max-width: 800px;
            /* Standard A4 width approximation */
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Header Section */
        .kop-header {
            display: flex;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
            /* Thick line */
            margin-bottom: 18px;
            position: relative;
        }

        .kop-logo {
            flex-shrink: 0;
            /* Prevent logo from shrinking */
            margin-right: 20px;
            position: absolute;
            left: 0;
            top: 0;
        }

        .kop-logo img {
            width: 80px;
            /* Adjust logo size as needed */
            height: auto;
            /* display: block; */
            border-radius: 4px;
            /* Slightly rounded corners for the logo */
        }

        .kop-info {
            text-align: center;
        }

        .kop-info h1,
        .kop-info h2,
        .kop-info p {
            margin: 0;
            line-height: 1.12;
        }

        .kop-info h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .kop-info h2 {
            font-size: 18px;
            font-weight: 600;
            color: #222;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .kop-info p {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }

        /* Garis Kop Surat (Optional, if you want a separate line below the main one) */
        .garis-kop-surat {
            border-bottom: 1px solid #666;
            /* Thinner line */
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* END KOP SURAT */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            /* page-break-inside: avoid; */
        }

        th,
        td {
            border: 1px solid #666;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #902C8E;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #666;
        }

        th#hari {
            background-color: #fee685;
            color: #000000;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 0;
        }

        .subtitle {
            text-align: left;
            margin-top: 20px;
            /* margin-bottom: 20px; */
            font-size: 16px;
            font-weight: 600;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="kop-header">
        <div class="kop-logo">
            <!-- Placeholder for Kabupaten Bungo Logo -->
            <!-- Ganti URL gambar di bawah dengan URL logo Kabupaten Bungo Anda -->
            <img src="{{ public_path('images/logo.png') }}" width="200px">
        </div>
        <div class="kop-info">
            <h1>PONDOK PESANTREN AZMANIA</h1>
            <h2>{{ $tingkat == 'SMP' ? 'SEKOLAH MENENGAH PERTAMA' : 'MADRASAH ALIYAH' }} PONOROGO</h2>
            <p>Jl. Azmania No. 2, Kel. Ronowijayan, Kec. Siman, Kab. Ponorogo, Telp. (0352) 3576660</p>
            <p>Email: azmaniapo@gmail.com Website: www.azmania.sch.id</p>
            <p>NPSN. 69956471, Akreditasi : A</p>
        </div>
    </div>

    @foreach ($jadwalPerHari as $hari => $jadwal)
        <div style="margin-bottom: 16px;">
            <table>
                <thead>
                    <tr>
                        <th id="hari" colspan="{{ 2 + $kelasList->count() }}">{{ $hari ?? '' }}</th>
                    </tr>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 75px;">Jam</th>
                        @foreach ($kelasList as $kelas)
                            <th>{{ $kelas->nama_kelas }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach ($jadwal as $jamLabel => $items)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $jamLabel }}</td>

                            @php
                                $isGlobal = $items->filter(function ($item) {
                                    return in_array($item->kelas?->kode_kelas, ['SMP', 'MA']);
                                });
                            @endphp

                            @if ($isGlobal->count() > 0)
                                @php
                                    $item = $items->first();
                                    $bg = $item->mataPelajaran->warna ?? '#ffffff';
                                    $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                    $textBentrok = $item->is_bentrok ?? null ? 'red' : $text;
                                @endphp
                                <td colspan="{{ count($kelasList) }}"
                                    style="background-color: {{ $bg }}; color: {{ $textBentrok }}">
                                    @if ($item)
                                        <div><strong>{{ $item->mataPelajaran->nama_mapel }}</strong></div>
                                        <div style="font-size: 12px;">{{ $item->guru->nama_guru ?? null }}</div>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                            @else
                                @foreach ($kelasList as $kelas)
                                    @php
                                        $item = $items->firstWhere('kelas_id', $kelas->id);
                                        $bg = $item->mataPelajaran->warna ?? '#ffffff';
                                        $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                        $textBentrok = $item->is_bentrok ?? null ? 'red' : $text;
                                    @endphp
                                    <td style="background-color: {{ $bg }}; color: {{ $textBentrok }}">
                                        @if ($item)
                                            <div><strong>{{ $item->mataPelajaran->nama_mapel }}</strong></div>
                                            <div style="font-size: 12px;">{{ $item->guru->nama_guru ?? null }}</div>
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

</body>

</html>
