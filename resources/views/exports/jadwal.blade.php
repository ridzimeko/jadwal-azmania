@php
    // dd($kelasList)
@endphp

<table>
    <thead>
        <tr>
            <th>Hari</th>
            <th>Jam</th>
            @foreach ($kelasList as $kelas)
                <th>{{ $kelas }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($jadwal as $hari => $items)
            @php
                $jamGroups = $items->groupBy(fn($j) => $j->jam_mulai . ' - ' . $j->jam_selesai);
            @endphp
            @foreach ($jamGroups as $jam => $jamItems)
                <tr>
                    <td>{{ $hari }}</td>
                    <td>{{ $jam }}</td>
                    @foreach ($kelasList as $kelas)
                        @php
                            $data = $jamItems->firstWhere('kelas.nama_kelas', $kelas);
                        @endphp
                        <td>
                            @if ($data)
                                {{ $data->mataPelajaran->nama_mapel ?? '-' }}
                                / {{ $data->guru->nama_guru ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
