@extends('layouts.main')

@section('content')
    <section class="container px-4 mt-5 mx-auto max-w-4xl pb-20 w-full">

        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold leading-6 text-gray-800 capitalize "
                    id="modal-title">
                    Edit Tugas
                </h3>
            </div>

            <div class="flex gap-2 items-center">
                <a href="{{route('assignments.downloadTemplateJavaAssignment')}}"
                   class="inline-flex items-center  gap-2 px-4 py-2 mt-2 text-xs font-medium text-white bg-blue-500 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>

                    <span class="text-sm font-medium">Download Template Unit Test</span>
                </a>
            </div>
        </div>

        <form class="mt-4" action="{{ route('assignments.update', ['classId' => $classId, 'assignmentId' => $assignment->id]) }}" method="post"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label class="block mt-5" for="title">
                <span class="text-gray-700 ">Nama Tugas
                <span class="text-red-500">*</span>
                </span>
                <input type="text" name="title"
                       required
                       autofocus
                       value="{{ $assignment->title }}"
                       id="title" placeholder="Tugas 1 Looping"
                       class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40  "/>
            </label>

            <label class="block mt-3" for="deadline">
                <span class="text-gray-700 ">Batas Waktu
               <span class="text-red-500">*</span></span>

                @php
                    $due_date = date('Y-m-d\TH:i', strtotime($assignment->due_date . ' +7 hours'));
                @endphp

                <input type="datetime-local" name="deadline"
                       required
                       value="{{ $due_date }}"
                       id="deadline"
                       class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40  "/>
            </label>

            <label class="block mt-3" for="file">
                <span class="text-gray-700">Template Test Cases (zip)
                </span>
                <div class="fallback">
                    <input type="file" name="template"
                           id="template"
                           accept=".zip"
                           class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40"/>
                </div>
            </label>


            <label class="block mt-3" for="description">
                <span class="text-gray-700 ">Deskripsi Tugas
                </span>
                <textarea type="text" name="description"
                          rows="12"
                          id="description" placeholder="Silahkan kerjakan tugas berikut ini dengan baik dan benar."
                          class="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40">{{ $assignment->description }}</textarea>
            </label>

            <button type="submit"
                    class="flex items-center mt-5 justify-center w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg sm:w-auto gap-x-2 hover:bg-blue-600 ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Simpan Perubahan
            </button>
        </form>
    </section>
@endsection
