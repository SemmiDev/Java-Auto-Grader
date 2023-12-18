@extends('layouts.main')

@section('content')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4 p-1 md:p-12">
        @forelse($classes as $no => $class)
            @php
                $owner = $class->owner;
                $role = $class->role;
                $class = $class->class;

                $totalSoftColors = count($softColors);
                $remainder = $no % $totalSoftColors;
                $softColor = $softColors[$remainder];
            @endphp

            <div class="max-w-2xl w-full hover:shadow-md transition duration-300 ease-in-out
             px-8 py-4 rounded-lg" style="background-color: {{ $softColor }};">

                <div class="flex items-center justify-start">
                    <a class="px-3 py-1 text-sm font-bold rounded cursor-pointer hover:bg-gray-500 transition-colors duration-300 bg-[#4A90E2] text-white"
                       tabindex="0" role="button">
                        @if($role == "Teacher")
                            Pengajar
                        @elseif($role == "Student")
                            <div class="flex gap-1 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                                </svg>
                                Siswa
                            </div>
                        @elseif($role == "Owner")
                            Pemilik
                        @endif
                    </a>
                </div>

                <div class="mt-2">
                    <a href="{{route('classes.assignments', $class->id)}}"
                       class="text-xl font-bold text-gray-700 hover:text-gray-600 hover:underline"
                       tabindex="0" role="link">{{$class->name}}</a>
                    <p class="mt-2 text-gray-600">
                        {{Str::limit($class->description, 100)}}
                    </p>
                </div>

                <div class="flex items-center mt-7">
                    <img class="hidden object-cover w-10 h-10 mr-4 rounded-full sm:block ring ring-3"
                         src="{{$owner->picture}}" alt="avatar">
                    <div class="flex flex-col">
                        <a class="font-bold text-gray-700 cursor-pointer"
                           tabindex="0" role="link">
                            {{ session()->get('user')->id == $owner->id ? "Anda" : $owner->name}}
                        </a>
                        <div>
                            <span class="break-all">
                                {{ $owner->email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <section
                class="flex justify-center items-center max-w-md p-4 mx-auto bg-white border border-gray-200 rounded-2xl">
                <p class="text-xl text-gray-600">
                    Kamu belum memiliki kelas.
                </p>
            </section>
        @endforelse
    </div>
@endsection
