<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap"
        rel="stylesheet"
    />

    @yield('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Comic Neue", cursive;
        }
        .alert-floating.fade-out {
            opacity: 0;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>
        @yield('title')
    </title>
{{--    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.1/dist/cdn.min.js"></script>--}}
</head>
<body>

<div>
    <div x-data="{ isOpenJoinToClass: false }">
        <div x-show="isOpenJoinToClass"
             x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
             x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl  sm:my-8 sm:w-full sm:max-w-sm sm:p-6 sm:align-middle">
                    <h3 class="text-lg font-medium leading-6 text-gray-800 capitalize "
                        id="modal-title">
                        Kode kelas
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 ">
                        Mintalah kode kelas kepada pengajar, lalu masukkan kode di sini.
                    </p>

                    <form class="mt-4" action="{{ route('classes.join') }}" method="post">
                        @csrf
                        <label class="block mt-3" for="email">
                            <input type="text" name="code"
                                   required
                                   id="code" placeholder="XU2DA2"
                                   class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 "/>
                        </label>

                        <div class="mt-4 sm:flex sm:items-center sm:-mx-2">
                            <button type="button" @click="isOpenJoinToClass = false"
                                    class="w-full px-4 py-2 text-sm font-medium tracking-wide text-gray-700 capitalize transition-colors duration-300 transform border border-gray-200 rounded-md sm:w-1/2 sm:mx-2   hover:bg-gray-100 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-40">
                                Batal
                            </button>

                            <button type="submit"
                                    class="w-full px-4 py-2 mt-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-blue-600 rounded-md sm:mt-0 sm:w-1/2 sm:mx-2 hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40">
                                Bergabung
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div x-data="{ isOpenCreateClass: false }">
            <div x-show="isOpenCreateClass"
                 x-transition:enter="transition duration-300 ease-out"
                 x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                 x-transition:leave="transition duration-150 ease-in"
                 x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                 x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-10 overflow-y-auto"
                 aria-labelledby="modal-title" role="dialog" aria-modal="true"
            >
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                    <div
                        class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl  sm:my-8 sm:w-full sm:max-w-sm sm:p-6 sm:align-middle">
                        <h3 class="text-lg font-medium leading-6 text-gray-800 capitalize "
                            id="modal-title">
                            Buat kelas
                        </h3>

                        <p class="mt-2 text-sm text-gray-500 ">
                            Buat kelas baru untuk mengajar.
                        </p>

                        <form class="mt-4" action="{{ route('classes.store') }}" method="post">
                            @csrf
                            <label class="block mt-3" for="email">
                                <input type="text" name="name"
                                       required
                                       id="name" placeholder="Konsep Pemrograman SI A {{ date('Y') }}"
                                       class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 "/>
                            </label>

                            <label class="block mt-3" for="description">
                            <input type="text" name="description"
                                      id="description" placeholder="Deskripsi kelas"
                                      class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 "></input>
                            </label>

                            <div class="mt-4 sm:flex sm:items-center sm:-mx-2">
                                <button type="button" @click="isOpenCreateClass = false"
                                        class="w-full px-4 py-2 text-sm font-medium tracking-wide text-gray-700 capitalize transition-colors duration-300 transform border border-gray-200 rounded-md sm:w-1/2 sm:mx-2   hover:bg-gray-100 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-40">
                                    Batal
                                </button>

                                <button type="submit"
                                        class="w-full px-4 py-2 mt-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-blue-600 rounded-md sm:mt-0 sm:w-1/2 sm:mx-2 hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40">
                                    Buat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <nav x-data="{ isOpen: false }" class="relative bg-white shadow ">
                <div class="container px-6 py-4 mx-auto">
                    <div class="lg:flex lg:items-center lg:justify-between">
                        <div class="flex items-center justify-between">
                            <a href="/" class="flex items-center gap-2">
                                <img class="w-auto h-8 sm:h-7" src="/images/logo.png" alt="logo">
                                <span class="leading-relaxed text-xl
                                 font-semibold
                                ">Auto Grader</span>
                            </a>

                            <!-- Mobile menu button -->
                            <div class="flex lg:hidden">
                                <button x-cloak @click="isOpen = !isOpen" type="button"
                                        class="text-gray-500  hover:text-gray-600  focus:outline-none focus:text-gray-600 "
                                        aria-label="toggle menu">
                                    <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/>
                                    </svg>

                                    <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Mobile Menu open: "block", Menu closed: "hidden" -->
                        <div x-cloak :class="[isOpen ? 'translate-x-0 opacity-100 ' : 'opacity-0 -translate-x-full']"
                             class="absolute inset-x-0 z-20 w-full px-6 py-4 transition-all duration-300 ease-in-out bg-white  lg:mt-0 lg:p-0 lg:top-0 lg:relative lg:bg-transparent lg:w-auto lg:opacity-100 lg:translate-x-0 lg:flex lg:items-center">
                            <div class="flex flex-col -mx-6 lg:flex-row lg:items-center lg:mx-8">
                                <button @click="isOpenJoinToClass = true"
                                        class="px-3 py-2 mx-3 mt-2 flex gap-2 items-center text-gray-700 transition-colors duration-300 transform rounded-md lg:mt-0  hover:bg-gray-100 ">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                    </svg>
                                    Gabung
                                    Ke Kelas
                                </button>
                                <button @click="isOpenCreateClass = true"
                                        class="px-3 py-2 mx-3 mt-2 flex  gap-2 items-center text-gray-700 transition-colors duration-300 transform rounded-md lg:mt-0  hover:bg-gray-100 ">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Buat Kelas
                                </button>
                            </div>

                            <div class="items-center hidden md:flex">
                                <button type="button" class="flex items-center focus:outline-none"
                                        aria-label="toggle profile dropdown">

                                    <div x-data="{ isOpen: true }" class="relative inline-block ">
                                        <!-- Dropdown toggle button -->
                                        <button @click="isOpen = !isOpen"
                                                class="relative z-10 flex items-center p-2 text-sm text-gray-600 bg-white border border-transparent rounded-md focus:border-blue-500 focus:ring-opacity-40  focus:ring-blue-300  focus:ring   focus:outline-none">
                                            <img
                                                class="flex-shrink-0 object-cover mx-1 rounded-full w-9 h-9 ring-4 ring-sky-200"
                                                src="{{ session()->get('user')->picture }}" alt="">
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-show="isOpen"
                                             @click.away="isOpen = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-90"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-90"
                                             class="absolute right-0 z-20 w-80 py-2 mt-56 overflow-hidden origin-top-right bg-white rounded-md shadow-xl "
                                        >
                                            <a href="#"
                                               class="flex items-center p-3 -mt-2 text-sm text-gray-600 transition-colors duration-300 transform  hover:bg-gray-100  ">
                                                <img class="flex-shrink-0 object-cover mx-1 rounded-full w-9 h-9"
                                                     src="{{ session()->get('user')->picture }}" alt="">
                                                <div class="mx-1">
                                                    <h1 class="text-sm font-semibold text-gray-700  break-all">
                                                        {{ session()->get('user')->name }}
                                                    </h1>
                                                    <p class="text-sm text-gray-500  break-all">
                                                        {{ session()->get('user')->email }}
                                                    </p>
                                                </div>
                                            </a>

                                            <hr class="border-gray-200  ">

                                            <form method="post"
                                                  action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit"
                                                        class="block px-4 w-full text-left py-3 text-sm text-gray-600 capitalize transition-colors duration-300 transform  hover:bg-gray-100  ">
                                                    Keluar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    @foreach(['success', 'error', 'warning', 'info'] as $type)
        @if(session()->has($type))
            <div class="fixed right-5 top-5 z-50 transition-opacity duration-500 opacity-100 alert-floating">
                <x-alert type="{{ $type }}" :message="session($type)"/>
            </div>
        @endif
    @endforeach
</div>
@yield('content')
@yield('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertDivs = document.querySelectorAll('.alert-floating');

        alertDivs.forEach(function (alertDiv) {
            alertDiv.addEventListener('click', function () {
                // Hapus alert saat diklik
                alertDiv.classList.add('fade-out');
                setTimeout(function () {
                    alertDiv.remove();
                }, 800); // Waktu transisi fade-out (0.5 detik)
            });

            setTimeout(function () {
                // Tambahkan kelas fade-out setelah beberapa detik
                alertDiv.classList.add('fade-out');
                setTimeout(function () {
                    // Hapus alert setelah transisi fade-out
                    alertDiv.remove();
                }, 800); // Waktu transisi fade-out (0.5 detik)
            }, 5000); // Waktu tampilan alert (7 detik)
        });
    });
</script>


</body>
</html>
