@extends('layouts.main')

@section('content')
    @include('classes.tab')

    <section class="container px-4 mt-5 mx-auto max-w-6xl w-full">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex gap-2 items-center">
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize ">
                    Anggota Kelas
                </h3>
                <span
                    class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full ">{{$total}} Orang</span>
            </div>


            <div class="flex items-center mt-4 gap-x-3" x-data="{ isOpenAddNewMember: false }">
                @if(in_array('add:member', $permissions))
                    <button
                        @click="isOpenAddNewMember = true"
                        class="flex items-center justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg sm:w-auto gap-x-2 hover:bg-blue-600 ">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                        </svg>
                        <span>Tambah Anggota</span>
                    </button>
                @endif

                <div x-show="isOpenAddNewMember"
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
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle"
                              aria-hidden="true">&#8203;</span>

                        <div
                            class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl  sm:my-8 sm:w-full sm:max-w-sm sm:p-6 sm:align-middle">
                            <h3 class="text-lg font-medium leading-6 text-gray-800 capitalize "
                                id="modal-title">
                                Tambah Anggota baru
                            </h3>
                            <p class="mt-2 text-sm text-gray-500 ">
                                Masukan alamat email anggota baru yang ingin anda masukkan ke kelas ini.
                            </p>

                            <form class="mt-4" action="{{ route('classes.addMember', $classCode) }}" method="post">
                                @csrf
                                <label class="block mt-3" for="email">
                                    <input type="text" name="email"
                                           required
                                           id="email" placeholder="sammi@gmail.com"
                                           class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 "/>
                                </label>

                                <label class="block mt-3" for="role">
                                    <select name="role" id="role"
                                            class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 ">
                                        <option value="Teacher">Pengajar</option>
                                        <option value="Student">Siswa</option>
                                    </select>
                                </label>

                                <div class="mt-4 sm:flex sm:items-center sm:-mx-2">
                                    <button type="button" @click="isOpenAddNewMember = false"
                                            class="w-full px-4 py-2 text-sm font-medium tracking-wide text-gray-700 capitalize transition-colors duration-300 transform border border-gray-200 rounded-md sm:w-1/2 sm:mx-2  hover:bg-gray-100 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-40">
                                        Batal
                                    </button>

                                    <button type="submit"
                                            class="w-full px-4 py-2 mt-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-blue-600 rounded-md sm:mt-0 sm:w-1/2 sm:mx-2 hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40">
                                        Tambahkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col mt-6">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden border border-gray-200  md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 ">
                            <thead class="bg-gray-50 ">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 px-4 text-sm font-normal text-left rtl:text-right text-gray-500 ">
                                    <div class="flex items-center gap-x-3">
                                        <span>Nama</span>
                                    </div>
                                </th>

                                <th scope="col"
                                    class="px-12 py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 ">
                                    <button class="flex items-center gap-x-2">
                                        <span>Peran</span>
                                    </button>
                                </th>

                                <th scope="col" class="relative py-3.5 px-4">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200  ">
                            <tr>
                                <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-x-3">
                                        <div class="flex items-center gap-x-2">
                                            <img class="object-cover w-10 h-10 rounded-full"
                                                 src="{{$owner->picture}}"
                                                 alt="">
                                            <div>
                                                <h2 class="font-medium text-gray-800 ">{{$owner->name}}</h2>
                                                <p class="text-sm font-normal text-gray-600 ">
                                                    {{$owner->email}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-12 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                    <div
                                        class="inline-flex items-center px-3 py-1 rounded-full gap-x-2 bg-emerald-100/60 ">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        <h2 class="text-sm font-normal text-emerald-500">Pemilik Kelas</h2>
                                    </div>
                                </td>
                            </tr>

                            @foreach($teachers as $teacher)
                                <tr>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-3">
                                            <div class="flex items-center gap-x-2">
                                                <img class="object-cover w-10 h-10 rounded-full"
                                                     src="{{$teacher->picture}}"
                                                     alt="">
                                                <div>
                                                    <h2 class="font-medium text-gray-800 ">{{$teacher->name}}</h2>
                                                    <p class="text-sm font-normal text-gray-600 ">
                                                        {{$teacher->email}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-12 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center px-3 py-1 rounded-full gap-x-2 bg-emerald-100/60 ">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                            <h2 class="text-sm font-normal text-emerald-500">Pengajar</h2>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm whitespace-nowrap">
                                        @if(in_array('delete:member', $permissions))
                                            <form
                                                method="post"
                                                onsubmit="return confirm('Apakah anda yakin ingin menghapus pengajar ini?');"
                                                action="{{ route('classes.removeMember', [
                                                    'classId' => $classId,
                                                    'userId' => $teacher->id
                                                ]) }}"
                                                class="flex items-center gap-x-6">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="text-gray-500 transition-colors duration-200  hover:text-red-500 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @foreach($students as $student)
                                <tr>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-3">
                                            <div class="flex items-center gap-x-2">
                                                <img class="object-cover w-10 h-10 rounded-full"
                                                     src="{{$student->picture}}"
                                                     alt="">
                                                <div>
                                                    <h2 class="font-medium text-gray-800 ">{{$student->name}}</h2>
                                                    <p class="text-sm font-normal text-gray-600 ">
                                                        {{$student->email}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-12 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center px-3 py-1 rounded-full gap-x-2 bg-emerald-100/60 ">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                            <h2 class="text-sm font-normal text-emerald-500">Siswa</h2>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm whitespace-nowrap">
                                        @if(in_array('delete:member', $permissions))
                                            <form
                                                method="post"
                                                onsubmit="return confirm('Apakah anda yakin ingin menghapus siswa ini?');"
                                                action="{{ route('classes.removeMember', [
                                                    'classId' => $classId,
                                                    'userId' => $student->id
                                                ]) }}"
                                                class="flex items-center gap-x-6">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="text-gray-500 transition-colors duration-200  hover:text-red-500 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

