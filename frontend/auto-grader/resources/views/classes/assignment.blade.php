@extends('layouts.main')

@section('content')
    @include('classes.tab')

    <section class="container px-4 mt-5 mx-auto max-w-6xl w-full">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex gap-2 items-center">
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize">
                    Daftar Tugas
                </h3>
                <span
                    class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full">{{$total == 0 ? 'Belum ada' : $total}} Tugas</span>
            </div>

            @if(in_array('create:assignment', $permissions))
                <div class="flex items-center mt-4 gap-x-3">
                    <a
                        href="{{route('assignments.create', ['classId' => $classId])}}"
                        class="flex items-center justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg sm:w-auto gap-x-2 hover:bg-blue-600">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_3098_154395)">
                                <path
                                    d="M13.3333 13.3332L9.99997 9.9999M9.99997 9.9999L6.66663 13.3332M9.99997 9.9999V17.4999M16.9916 15.3249C17.8044 14.8818 18.4465 14.1806 18.8165 13.3321C19.1866 12.4835 19.2635 11.5359 19.0351 10.6388C18.8068 9.7417 18.2862 8.94616 17.5555 8.37778C16.8248 7.80939 15.9257 7.50052 15 7.4999H13.95C13.6977 6.52427 13.2276 5.61852 12.5749 4.85073C11.9222 4.08295 11.104 3.47311 10.1817 3.06708C9.25943 2.66104 8.25709 2.46937 7.25006 2.50647C6.24304 2.54358 5.25752 2.80849 4.36761 3.28129C3.47771 3.7541 2.70656 4.42249 2.11215 5.23622C1.51774 6.04996 1.11554 6.98785 0.935783 7.9794C0.756025 8.97095 0.803388 9.99035 1.07431 10.961C1.34523 11.9316 1.83267 12.8281 2.49997 13.5832"
                                    stroke="currentColor" stroke-width="1.67" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_3098_154395">
                                    <rect width="20" height="20" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>

                        <span>Buat Tugas Baru</span>
                    </a>
                </div>
            @endif
        </div>

        <div class="flex flex-col mt-6">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden border border-gray-200 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($assignments as $assignment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center gap-x-3">
                                            <div class="flex items-center gap-x-5">
                                                <div
                                                    class="flex items-center justify-center w-20 h-20 text-blue-500 bg-blue-100 rounded-full">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    @php
                                                        \Carbon\Carbon::setLocale('id');
                                                        $carbon = \Carbon\Carbon::parse($assignment->due_date);
                                                        $diffForHumans = $carbon->diffForHumans();
                                                        $dueDate = $carbon->isoFormat('D MMMM YYYY HH:mm');
                                                    @endphp
                                                    <a
                                                        href="{{route('assignments.show', ['classId' => $classId, 'assignmentId' => $assignment->id])}}"
                                                        class="mt-5 font-normal text-3xl text-gray-800">{{$assignment->title}}</a>

                                                    <p class="text-md font-normal text-gray-500">Batas
                                                        waktu: {{$dueDate}}
                                                    </p>
                                                    <span class="text-sm font-light text-teal-600">
                                                        {{$diffForHumans}}
                                                    </span>


                                                    <div></div>
                                                    <a
                                                        class="px-3 mt-5 py-2 inline-flex gap-2  items-center text-sm tracking-wide text-white transition-colors duration-200 bg-teal-500 rounded-lg gap-x-2 hover:bg-teal-600"
                                                        href="{{route('assignments.leaderboard', ['classId' => $classId, 'assignmentId' => $assignment->id])}}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                                                        </svg>


                                                        Leaderboard
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-medium text-gray-900">Belum ada tugas</span>
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
@endsection
