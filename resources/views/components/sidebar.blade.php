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

        <flux:sidebar.group expandable icon="book" heading="Jadwal" class="grid">
            <flux:sidebar.item href="/jadwal/smp">SMP</flux:sidebar.item>
            <flux:sidebar.item href="/jadwal/ma">MA</flux:sidebar.item>
            <flux:sidebar.item href="/jadwal/tetap">Kegiatan Tetap</flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group expandable icon="pencil-square" heading="Data" class="grid">
            <flux:sidebar.item href="/data/mata-pelajaran">Mata Pelajaran</flux:sidebar.item>
            <flux:sidebar.item href="/data/guru">Guru</flux:sidebar.item>
            <flux:sidebar.item href="/data/kelas">Kelas</flux:sidebar.item>
        </flux:sidebar.group>
        @if(auth()->user()->role === 'superadmin')
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
