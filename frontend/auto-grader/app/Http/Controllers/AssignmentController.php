<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class AssignmentController extends Controller
{
    protected Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function create($classId)
    {
        return view('assignments.create', [
            'classId' => $classId
        ]);
    }

    public function store(Request $request, $classId)
    {
        try {

            $request->validate([
                'title' => 'required',
                'deadline' => 'required',
                'template' => 'required|mimes:zip',
            ], [
                'template.required' => 'File tidak boleh kosong',
                'template.mimes' => 'File harus berupa ZIP',
                'title.required' => 'Judul tidak boleh kosong',
                'deadline.required' => 'Deadline tidak boleh kosong',
            ]);

            $zipFile = $request->file('template');
            $zipFileName = $zipFile->getClientOriginalName();

            // Simpan file ZIP ke direktori tertentu
            $zipPath = storage_path('app/uploads/' . $zipFileName);
            $zipFile->move(storage_path('app/uploads'), $zipFileName);

            // Hapus folder __MACOSX dari arsip ZIP
            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $this->deleteMacOSFromZip($zip);
                $zip->close();
            }

            $payload = [
                [
                    'name' => 'title',
                    'contents' => $request->title,
                ],
                [
                    'name' => 'description',
                    'contents' => $request->description,
                ],
                [
                    'name' => 'deadline',
                    'contents' => $request->deadline,
                ],
                [
                    'name' => 'template',
                    'filename' => $zipFileName,
                    'contents' => fopen($zipPath, 'r'),
                ],
            ];

            $this->httpClient->post(config('app.api_url') . '/api/classes/' . $classId . '/assignments', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
                'multipart' => $payload,
            ]);

            // remove file from storage
            unlink($zipPath);

            return redirect()->route('classes.assignments', ['classId' => $classId])->with('success', 'Tugas berhasil dibuat');
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    private function deleteMacOSFromZip($zip)
    {
        $fileCount = $zip->numFiles;
        for ($i = 0; $i < $fileCount; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_starts_with($filename, '__MACOSX/')) {
                // Hapus file dari arsip
                $zip->deleteIndex($i);
            }
        }
    }

    public function show($classId, $assignmentId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());
            $data = $body->data;

            $assignment = $data->assignment;
            $students = $data->students;
            $userRole = $data->role;
            $permissions = $data->permissions;


            if ($userRole == "Owner" or $userRole == "Teacher") {
                return view('assignments.show', [
                    'assignment' => $assignment,
                    'classId' => $classId,
                    'assignmentId' => $assignmentId,
                    'userRole' => $userRole,
                    'students' => $students,
                    'permissions' => $permissions,
                    'classId' => $classId,
                ]);
            }

            // call api to get submissions
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId . '/submissions', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());
            $data = $body->data;

            $submissions = $data->submissions;
            $highestGrade = $data->highest_grade;

            return view('assignments.show-student', [
                'assignment' => $assignment,
                'classId' => $classId,
                'assignmentId' => $assignmentId,
                'userRole' => $userRole,
                'submissions' => $submissions,
                'highestGrade' => $highestGrade,
                'permissions' => $permissions,
                'classId' => $classId,
            ]);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function leaderboard($classId, $assignmentId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId . '/leaderboard', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());
            $data = $body->data;

            return view('assignments.leaderboard', [
                'leaderboard' => $data
            ]);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function destroy($classId, $assignmentId) {
        try {
            $this->httpClient->delete(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId , [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            return redirect()->route('classes.assignments', ['classId' => $classId])->with('success', 'Tugas berhasil dihapus');
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function csv($classId, $assignmentId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId . '/csv', [
                'headers' => [
                    'Accept' => 'text/csv',
                    'Content-Type' => 'text/csv',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = $response->getBody()->getContents();

            // Set custom filename in the "Content-Disposition" header
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=' . $assignmentId . '.csv',
            ];

            return response($body, 200, $headers);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function assignmentDescription($classId, $assignmentId)
    {
        try {

            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body)->data;
            $assignment = $data->assignment;
            $description = $assignment->description;

            return view('assignments.description', [
                'description' => $description,
            ]);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function edit($classId, $assignmentId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body)->data;

            return view('assignments.edit', [
                'assignment' => $data->assignment,
                'classId' => $classId,
                'assignmentId' => $assignmentId,
            ]);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }



    public function update(Request $request, $classId, $assignmentId)
    {
        try {
            $request->validate([
                'title' => 'required',
                'deadline' => 'required',
                'template' => 'nullable|mimes:zip',
            ], [
                'template.mimes' => 'File harus berupa ZIP',
                'title.required' => 'Judul tidak boleh kosong',
                'deadline.required' => 'Deadline tidak boleh kosong',
            ]);

            $payload = [
                [
                    'name' => 'title',
                    'contents' => $request->title,
                ],
                [
                    'name' => 'description',
                    'contents' => $request->description,
                ],
                [
                    'name' => 'deadline',
                    'contents' => $request->deadline,
                ],
            ];

            $zipPath = null;

            if ($request->hasFile('template')) {
                $zipFile = $request->file('template');
                $zipFileName = $zipFile->getClientOriginalName();

                // Simpan file ZIP ke direktori tertentu
                $zipPath = storage_path('app/uploads/' . $zipFileName);
                $zipFile->move(storage_path('app/uploads'), $zipFileName);

                // Hapus folder __MACOSX dari arsip ZIP
                $zip = new ZipArchive;
                if ($zip->open($zipPath) === TRUE) {
                    $this->deleteMacOSFromZip($zip);
                    $zip->close();
                }

                $payload = [
                    [
                        'name' => 'title',
                        'contents' => $request->title,
                    ],
                    [
                        'name' => 'description',
                        'contents' => $request->description,
                    ],
                    [
                        'name' => 'deadline',
                        'contents' => $request->deadline,
                    ],
                    [
                        'name' => 'template',
                        'filename' => $zipFileName,
                        'contents' => fopen($zipPath, 'r'),
                    ],
                ];
            }

            $this->httpClient->put(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
                'multipart' => $payload,
            ]);

            // remove file from storage
            if ($zipPath) {
                unlink($zipPath);
            }

            return redirect()->route('classes.assignments', ['classId' => $classId])->with('success', 'Tugas berhasil diubah');
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = json_decode($e->getResponse()->getBody()->getContents())->message;

            if ($statusCode == 500) {
                return redirect()->route('error')->with([
                    'message' => $message,
                    'status_code' => $statusCode,
                ]);
            }

            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            return redirect()->route('error')->with([
                'message' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ]);
        }
    }

    public function downloadTemplateJavaAssignment(Request $request)
    {
        $url = config('app.api_url') . '/api/assignments/teachers/templates/download';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . session('auth_token')
        ])->get($url);

        if ($response->successful()) {
            $fileName = 'template-java-assignment.tar';
            $fileContent = $response->body();

            return response($fileContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', "attachment; filename={$fileName}");
        } else {
            return redirect()->back()->with('error', 'Gagal mengunduh template');
        }
    }

    public function downloadTemplateJavaStudent(Request $request)
    {
        $url = config('app.api_url') . '/api/assignments/students/templates/download';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . session('auth_token')
        ])->get($url);

        if ($response->successful()) {
            $fileName = 'template-java-student.tar'; // Sesuaikan dengan nama template yang diunduh
            $fileContent = $response->body();

            return response($fileContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', "attachment; filename={$fileName}");
        } else {
            return redirect()->back()->with('error', 'Gagal mengunduh template');
        }
    }
}
