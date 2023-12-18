@extends('layouts.guest')

@section('content')
    <section class="bg-white dark:bg-gray-900 ">
        <div class="container min-h-screen px-6 py-12 mx-auto lg:flex lg:items-center lg:gap-12">
            <div class="wf-ull lg:w-1/2">
                <p class="text-sm font-medium text-blue-500 dark:text-blue-400">{{$statusCode}} Error</p>
                <p class="mt-4 text-gray-500 dark:text-gray-400">{{$message}}</p>

                <div class="flex items-center mt-6 gap-x-3">
                    <a
                        href="/"
                        class="w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg shrink-0 sm:w-auto hover:bg-blue-600 dark:hover:bg-blue-500 dark:bg-blue-600">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

            <div class="relative w-full mt-12 lg:w-1/2 lg:mt-0">
                <img class="w-full max-w-lg lg:mx-auto" src="/images/components/illustration.svg" alt="">
            </div>
        </div>
    </section>
@endsection
