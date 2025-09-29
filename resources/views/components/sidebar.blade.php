@php
    $menus = [
        [
            'label' => 'Dashboard',
            'icon' => 'squares-2x2',
            'route' => 'dashboard',
        ],
        [
            'label' => 'Jadwal',
            'icon' => 'book',
            'submenu' => [
                [
                    'label' => 'SMP',
                    'route' => 'jadwal.smp',
                ],
                [
                    'label' => 'MA',
                    'route' => 'jadwal.ma',
                ],
            ],
        ],
        [
            'label' => 'Data',
            'icon' => 'pencil-square',
            'submenu' => [
                [
                    'label' => 'Mata Pelajaran',
                    'route' => 'data.mata-pelajaran',
                ],
                [
                    'label' => 'Guru',
                    'route' => 'data.guru',
                ],
                [
                    'label' => 'Kelas',
                    'route' => 'data.kelas',
                ],
            ],
        ],
    ];
@endphp

<aside class="w-64 h-screen bg-white shadow flex flex-col">
    <!-- Logo -->
    <div class="p-4 flex items-center justify-center gap-4">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16">
        <div class="flex flex-col gap2">
            <h2 class="text-lg">Sistem Jadwal</h2>
            <h3 class="uppercase text-3xl font-bold text-primary">Azmania</h3>
        </div>
    </div>

    <!-- Menu -->
    <nav class="flex-1 px-2 py-4 overflow-y-auto">
        <ul class="space-y-2">
            @foreach($menus as $menu)
                @if(isset($menu['submenu']))
                    <!-- Menu with Submenu -->
                    <li x-data="{ open: {{ collect($menu['submenu'])->pluck('route')->contains(fn($r) => request()->routeIs($r)) ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-md text-gray-700
                                       hover:bg-accent-light hover:text-primary">
                            <span class="flex items-center">
                                 <flux:icon :name="$menu['icon']" class="mr-2" />
                                {{ $menu['label'] }}
                            </span>
                            <!-- <span class="material-icons text-sm" x-text="open ? 'expand_less' : 'expand_more'"></span> -->
                        </button>
                        <ul x-show="open" x-collapse class="ml-4 border-l border-gray-300 pl-3 mt-1 space-y-1">
                            @foreach($menu['submenu'] as $sub)
                                @if(Route::has($sub['route']))
                                    <li>
                                        <a href="{{ route($sub['route']) }}"
                                           class="block px-2 py-1 rounded-md text-sm
                                                  {{ request()->routeIs($sub['route'])
                                                      ? 'bg-primary text-white'
                                                      : 'text-gray-600 hover:bg-accent-light hover:text-primary' }}">
                                            {{ $sub['label'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @else
                    <!-- Menu Item -->
                    @if(Route::has($menu['route']))
                        <li>
                            <a href="{{ route($menu['route']) }}"
                               class="flex items-center px-3 py-2 rounded-md
                                      {{ request()->routeIs($menu['route'])
                                          ? 'bg-primary text-white'
                                          : 'text-gray-700 hover:bg-accent-light hover:text-primary' }}">
                                 <flux:icon :name="$menu['icon']" class="mr-2" />
                                {{ $menu['label'] }}
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    </nav>

    <!-- Account Container -->
    <div class="p-4 flex items-center space-x-3">
        <div class="h-10 w-10 rounded-full bg-secondary text-white flex items-center justify-center font-bold">
            {{ strtoupper(substr(auth()->user()->name ?? "AAA BBB", 0, 1)) }}
        </div>
        <div>
            <div class="text-sm font-semibold">{{ auth()->user()->name ?? "Akowkokwao" }}</div>
            <div class="text-xs text-gray-500">Akun Admin</div>
        </div>
    </div>
</aside>
