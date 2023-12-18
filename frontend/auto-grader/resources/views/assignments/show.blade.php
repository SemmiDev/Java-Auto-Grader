@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto max-w-6xl w-full">
        <div class="flex justify-between">
            <div>
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize " id="modal-title">
                    {{ $assignment->title }}
                </h3>
                @php
                    \Carbon\Carbon::setLocale('id');
                    $carbon = \Carbon\Carbon::parse($assignment->due_date);
                    $dueDate = $carbon->isoFormat('D MMMM YYYY HH:mm');
                @endphp
                <p class="text-md mt-2 font-normal text-teal-500">Batas Waktu: {{ $dueDate }}</p>
                <a href="{{ route('assignments.description', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}"
                    class="px-3 mt-5 py-2 inline-flex gap-2  items-center text-sm tracking-wide text-white transition-colors duration-200 bg-teal-500 rounded-lg gap-x-2 hover:bg-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-sm font-medium">Lihat Soal</span>
                </a>
            </div>
            <div>
                <div x-data="{ isOpen: false }" class="relative inline-block ">
                    <button @click="isOpen = !isOpen"
                        class="flex items-center justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg sm:w-auto gap-x-2 hover:bg-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="mx-1">Lainnya</span>
                    </button>

                    <!-- Dropdown menu -->
                    <div x-show="isOpen" @click.away="isOpen = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                        class="absolute right-0 z-20 w-56 py-2 mt-2 overflow-hidden origin-top-right bg-white rounded-md shadow-xl ">

                        <hr class="border-gray-200  ">

                        <a href="{{ route('assignments.csv', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}"
                            class="block px-4 py-3 text-sm text-gray-600 capitalize transition-colors duration-300 transform  hover:bg-gray-100 ">
                            Unduh Nilai (CSV)
                        </a>

                        <a href="{{ route('assignments.edit', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}"
                            class="block px-4 py-3 text-sm text-gray-600 capitalize transition-colors duration-300 transform  hover:bg-gray-100 ">
                            Edit Tugas
                        </a>

                        <form onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');"
                            action="{{ route('assignments.destroy', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="block w-full px-4 py-3 text-sm text-left text-red-600 capitalize transition-colors duration-300 transform  hover:bg-gray-100 ">
                                Hapus Tugas
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-5 mt-12">
            <section class="container col-span-12">
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-medium text-gray-800 ">Siswa</h2>
                    <span class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full  ">
                        {{ count($students) }} Orang
                    </span>
                </div>

                <div class="flex flex-col mt-5">
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
                                                class="px-4 py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 ">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 ">
                                        @forelse($students as $student)
                                            <tr>
                                                <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                                    <div class="inline-flex items-center gap-x-3">
                                                        <div class="flex items-center gap-x-2">
                                                            <img class="object-cover w-10 h-10 rounded-full"
                                                                src="{{ $student->picture }}" alt="{{ $student->name }}" />
                                                            <div>
                                                                <h2 class="font-medium text-gray-800  ">{{ $student->name }}
                                                                </h2>
                                                                <p class="text-sm font-normal text-gray-600 ">
                                                                    {{ '@' . $student->email }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-500  whitespace-nowrap">
                                                    <a href="{{ route('submissions.index', ['classId' => $classId, 'assignmentId' => $assignment->id, 'studentId' => $student->id]) }}"
                                                        class="px-4 py-2 text-sm font-medium text-white transition-colors duration-200 transform bg-teal-500 rounded-md hover:bg-teal-600">
                                                        Riwayat Pengumpulan Tugas
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="px-4 py-4 text-sm text-gray-500  whitespace-nowrap">
                                                <td class="px-4 py-4 text-sm text-gray-500  whitespace-nowrap">
                                                    Belum ada siswa.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
@endsection
