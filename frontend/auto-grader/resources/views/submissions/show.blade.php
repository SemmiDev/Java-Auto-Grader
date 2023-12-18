@php use App\Helpers\SubmissionStatus; @endphp
@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto max-w-6xl w-full">
        <div class="mx-auto gap-4 mt-5">
            <div class="mx-auto">

                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-x-3">
                        <h2 class="text-lg font-medium text-gray-800 ">Riwayat Pengumpulan Tugas</h2>
                        <span
                            class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full  ">{{ count($submissions) }} Kali Pengumpulan</span>
                    </div>
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
                </div>

                <div class="mt-12 flex flex-col gap-3">
                    @forelse($submissions as $submission)
                        <div class="w-full px-8 py-4 bg-white rounded-lg shadow-md ">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-light text-gray-600 ">
                                    @php
                                        $logFile = $submission->logs;
                                        if ($logFile == null || $logFile == '') {
                                            $logFile = 'Tidak ada log';
                                        }

                                        \Carbon\Carbon::setLocale('id');
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
        </div>
    </section>
@endsection
