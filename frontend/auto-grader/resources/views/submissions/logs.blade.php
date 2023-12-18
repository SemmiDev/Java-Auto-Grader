@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto w-full break-all">
        <div class="w-full px-8 py-4 mt-16 bg-white rounded-lg shadow-lg">
            <h2 class="mt-2 text-xl font-semibold text-gray-800 md:mt-0">Log Pemrosesan Tugas</h2>
            <div class="mt-5">
                <code class="leading-relaxed text-teal-800 mt-12 text-sm">
                    {!! $content !!}
                </code>
            </div>
        </div>
    </section>
@endsection
