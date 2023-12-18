@php use App\Helpers\SubmissionStatus; @endphp
@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto max-w-6xl w-full">
        <div class="flex justify-between">
            <div>
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize "
                    id="modal-title">
                    {{ $assignment->title }}
                </h3>
                @php
                    \Carbon\Carbon::setLocale('id');
                    $carbon = \Carbon\Carbon::parse($assignment->due_date);
                    $dueDate = $carbon->isoFormat('D MMMM YYYY HH:mm');
                @endphp
                <p class="text-md font-normal text-teal-500">Batas Waktu: {{$dueDate}}</p>
                <a href="{{ route('assignments.description', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}"
                    class="px-3 mt-5 py-2 inline-flex gap-2  items-center text-sm tracking-wide text-white transition-colors duration-200 bg-teal-500 rounded-lg gap-x-2 hover:bg-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-sm font-medium">Lihat Soal</span>
                </a>
            </div>

            <div>
                <a href="{{route('assignments.downloadTemplateJavaStudent')}}"
                   class="inline-flex items-center  gap-2 px-4 py-2 mt-2 text-xs font-medium text-white bg-blue-500 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>

                    <span class="text-sm font-medium">Download Template</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 mt-5">
            <div class="col-span-8">
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-medium text-gray-800 ">Riwayat Pengumpulan Tugas</h2>
                    <span
                        class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full  ">{{ count($submissions) }} Kali Pengumpulan</span>
                </div>

                <div class="mt-5 flex flex-col gap-3">
                    @forelse($submissions as $submission)
                        <div class="max-w-2xl px-8 py-4 bg-white rounded-lg shadow-md ">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-light text-gray-600 ">
                                    @php
                                        $logFile = $submission->logs;
                                        if ($logFile == null || $logFile == '') {
                                            $logFile = 'Tidak ada log';
                                        }

                                        $carbon = \Carbon\Carbon::parse($submission->updated_at);
                                        $diffForHumans = $carbon->diffForHumans();
                                    @endphp

                                    {{ $diffForHumans }}
                                </span>

                                @if($logFile != "Tidak ada log")
                                    <a href="{{route('submissions.logs', ['logFile' => $submission->logs ])}}"
                                       class="px-3 py-1 text-sm font-bold text-gray-100 transition-colors duration-300 transform bg-gray-600 rounded cursor-pointer hover:bg-gray-500"
                                       tabindex="0" role="button">Lihat Log</a>
                                @endif
                            </div>

                            <div class="mt-2">
                                <a href="#"
                                   class="text-5xl font-bold text-gray-700  hover:text-gray-600  hover:underline"
                                   tabindex="0" role="link">{{$submission->grade}}</a>
                                <p class="mt-2 text-gray-600  break-all">
                                    <span class="text-gray-700">Total Tests</span> <span class="font-bold">
                                        {{ $assignment->total_test_cases}}
                                    </span> 👉
                                    <span class="text-green-700">Passed</span> <span class="font-bold">
                                        {{ $submission->test_cases->passed}}
                                    </span> |
                                    <span class="text-red-700">Failed</span> <span class="font-bold">
                                        {{ $submission->test_cases->failures}}
                                    </span> |
                                    <span class="text-yellow-700">Skipped</span> <span class="font-bold">
                                        {{ $submission->test_cases->skipped}}
                                    </span> |
                                    <span class="text-sky-700">Pending</span> <span class="font-bold">
                                        {{ $submission->test_cases->errors}}
                                    </span>
                                </p>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <a href="#"
                                   class="text-blue-600 flex gap-2 items-center  hover:underline"
                                   tabindex="0"
                                   role="link">
                                    @switch($submission->status)
                                        @case('BEING GRADED')
                                            <div>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor"
                                                     class="w-5 h-5 animate-spin">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                                </svg>
                                            </div>
                                            @break
                                        @case('SUCCESSFULLY GRADED')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                                            </svg>
                                            @break
                                        @case('FAILED TO GRADE')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            @break
                                    @endswitch
                                    {{ SubmissionStatus::toID($submission->status) }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <span class="text-sm text-gray-500">Belum ada pengumpulan tugas</span>
                    @endforelse
                </div>
            </div>

            <div class="col-span-4">
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-medium text-gray-800 ">Skor Tertinggi</h2>
                    <span
                        class="px-3 flex gap-2  items-center  py-1 text-xl text-blue-600 bg-blue-100 rounded-full   font-bold">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                        </svg>
                        {{ $highestGrade }}
                    </span>
                </div>

                <div class="mt-5">
                        <div class="w-full max-w-sm p-6 m-auto mx-auto bg-white rounded-lg shadow-md ">
                            <form
                                action="{{route('submissions.store', ['assignmentId' => $assignment->id, 'classId' => $classId])}}"
                                method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div>
                                    <label for="files" class="block text-sm text-gray-800 ">Upload file .java</label>
                                    <input type="file"
                                           multiple
                                           id="files"
                                           name="files[]"
                                           accept=".java"
                                           required
                                           class="block w-full mt-3 px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40"/>
                                </div>

                                @if($assignment->is_over_due)
                                    <div class="mt-6">
                                        <button
                                            disabled
                                            class="w-full px-6 py-2.5 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-red-800 rounded-lg hover:bg-red-700 focus:outline-none focus:ring focus:ring-red-300 focus:ring-opacity-50">
                                            Telah Lewat Batas Waktu
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-6">
                                        <button
                                            type="submit"
                                            class="w-full px-6 py-2.5 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-gray-800 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-50">
                                            Kumpulkan Tugas
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </section>
@endsection
