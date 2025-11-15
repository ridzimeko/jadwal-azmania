<flux:sidebar sticky collapsible="mobile" class="bg-white border-zinc-200 group !data-current:bg-primary shrink-0">
    <flux:sidebar.header>

        <div class="flex items-center gap-4 pointer-events-none">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-14">
            <div class="flex flex-col">
                <h2 class="text-lg">Sistem Jadwal</h2>
                <h3 class="uppercase text-2xl font-bold text-primary">Azmania</h3>
            </div>
        </div>
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="/">Beranda</flux:sidebar.item>

        <flux:sidebar.item icon="calendar" href="/jadwal/periode">Periode Jadwal</flux:sidebar.item>

        @php
            $periodeList = \App\Models\Periode::orderBy('tahun_ajaran', 'desc')->get();
        @endphp
        <flux:sidebar.group expandable icon="book" heading="Jadwal" class="grid">
            @if (count($periodeList) >= 1)
                @foreach ($periodeList as $periode)
                    <flux:sidebar.item href="/jadwal/pelajaran/detail/{{ $periode->id }}">
                        {{ $periode->tahun_ajaran }}
                    </flux:sidebar.item>
                @endforeach
                @if (count($periodeList) > 3)
                    <flux:sidebar.item href="/jadwal/pelajaran">
                        Periode lain
                    </flux:sidebar.item>    
                @endif
            @else
                <flux:sidebar.item class="text-gray-600">Belum ada periode</flux:sidebar.item>
            @endif
        </flux:sidebar.group>

        <flux:sidebar.group expandable icon="pencil-square" heading="Data" class="grid">
            <flux:sidebar.item href="/data/mata-pelajaran">Mata Pelajaran</flux:sidebar.item>
            <flux:sidebar.item href="/data/jam-pelajaran">Jam Pelajaran</flux:sidebar.item>
            <flux:sidebar.item href="/data/guru">Guru</flux:sidebar.item>
            <flux:sidebar.item href="/data/kelas">Kelas</flux:sidebar.item>
        </flux:sidebar.group>
        @if (auth()->user()->role === 'superadmin')
            <flux:sidebar.item icon="users" href="/atur-admin">Kelola Admin</flux:sidebar.item>
        @endif
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile name="{{ auth()->user()->nama ?? '' }}" avatar:color="amber" />

        <flux:menu>
            <flux:menu.item icon="cog-6-tooth" href="/pengaturan/akun">Pengaturan Akun</flux:menu.item>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item type="submit" variant="danger" icon="arrow-right-start-on-rectangle">
                    Logout
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
