@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto max-w-2xl w-full">
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
                    class="py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 ">
                    <button class="flex items-center gap-x-2">
                        <span>Skor / Nilai</span>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($leaderboard as $leader)
                <tr>
                    <td class="px-4 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">
                        <div class="inline-flex items-center gap-x-3">
                            <div class="flex items-center gap-x-2">
                                <img class="ring-2 ring-sky-500 object-cover w-10 h-10 rounded-full"
                                     src="{{$leader->picture}}"
                                     alt="">
                                <div>
                                    <h2 class="font-medium text-gray-800 ">{{$leader->name}}</h2>
                                    <p class="text-sm font-normal text-gray-600 ">
                                        {{$leader->email}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 text-3xl font-medium text-gray-700 whitespace-nowrap">
                       {{$leader->grade}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
@endsection
