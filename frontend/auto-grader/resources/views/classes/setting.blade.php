@extends('layouts.main')

@section('content')
    @include('classes.tab')

    <section class="container px-4 mt-5 mx-auto max-w-4xl w-full">

        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize "
                    id="modal-title">
                    Ubah Informasi kelas
                </h3>

                <p class="mt-2 text-sm text-gray-500 ">
                    Silahkan isi informasi kelas yang ingin diubah.
                </p>
            </div>

            <div class="flex gap-2 items-center">
                @if(in_array('delete:class', $permissions))
                    <form action="{{ route('classes.destroy', $classId) }}"
                          class="mt-5"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');"
                          method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="flex items-center justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-red-500 rounded-lg sm:w-auto gap-x-2 hover:bg-red-600 ">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Hapus Kelas
                        </button>
                    </form>
                @endif
            </div>

        </div>

        <form class="mt-4" action="{{ route('classes.update', $classId) }}" method="post">
            @csrf
            @method('PUT')
            <label class="block mt-5" for="email">
                <span class="text-gray-700 ">Kode Kelas</span>
                <div class="relative flex items-center mt-2">
                    <button
                        type="button"
                        id="copy-button"
                        class="absolute right-0 focus:outline-none rtl:left-0 rtl:right-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor"
                             class="w-6 h-6 mx-4 text-gray-400 transition-colors duration-300  hover:text-gray-500 ">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                        </svg>
                    </button>
                    <input
                        type="text"
                        readonly
                        id="class-code"
                        value="{{ $classCode }}"
                        class="font-bold text-teal-600 block w-full py-2.5 placeholder-gray-400/70 bg-white border border-gray-200 rounded-lg pl-5 pr-11 rtl:pr-5 rtl:pl-11  focus:border-blue-400  focus:ring-blue-300 focus:outline-none focus:ring focus:ring-opacity-40">
                </div>
            </label>

            <label class="block mt-5" for="email">
                <span class="text-gray-700 ">Nama Kelas</span>
                <input type="text" name="name"
                       required
                       value="{{ old('name', $className) }}"
                       id="name" placeholder="Konsep Pemrograman SI A {{ date('Y') }}"
                       class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40  "/>
            </label>

            <label class="block mt-3" for="description">
                <span class="text-gray-700 ">Deskripsi Kelas</span>
                <input type="text" name="description"
                       placeholder="Deskripsi kelas"
                       value="{{ old('description', $classDescription) }}"
                       class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40  "/>
            </label>

            @if(in_array('update:class', $permissions))
                <button type="submit"
                        class="flex items-center mt-5 justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg sm:w-auto gap-x-2 hover:bg-blue-600 ">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                    Simpan Perubahan
                </button>
            @endif
        </form>
    </section>
@endsection

@section('scripts')
    <script>
        const copyButton = document.getElementById('copy-button');
        const classCode = document.getElementById('class-code');

        copyButton.addEventListener('click', () => {
            navigator.clipboard.writeText(classCode.value).then(() => {
                copyButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-4 text-gray-400 transition-colors duration-300  hover:text-gray-500 ">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    <span class="text-gray-400">Siip</span>
                `;
            });
        });
    </script>
@endsection
