<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    protected Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function index($classId, $assignmentId, $studentId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId . '/students/' . $studentId . '/submissions', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);


            $body = json_decode($response->getBody()->getContents());
            $data = $body->data;

            $submissions = $data->submissions;
            $highestGrade = $data->highest_grade;

            return view('submissions.show', [
                'highestGrade' => $highestGrade,
                'submissions' => $submissions,
            ]);
        }  catch (ClientException $e) {
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

    public function store(Request $request, $classId, $assignmentId)
    {
        try {
            $files = $request->file('files');
            $multipart = [];

            foreach ($files as $file) {
                $multipart[] = [
                    'name' => 'files[]', // Gunakan 'files[]' untuk menangani multiple file uploads
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }

            $this->httpClient->post(config('app.api_url') . '/api/classes/' . $classId . '/assignments/' . $assignmentId . '/submissions', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
                'multipart' => $multipart,
            ]);

            return back()->with('success', 'Tugas berhasil dikumpulkan dan dalam proses penilaian');
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

    public function logs($logFile)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/logs/' . $logFile, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('auth_token'),
                ],
            ]);

            $response = json_decode($response->getBody()->getContents(), true);
            $data = $response['data'];
            $content = $data['content'];

            return view('submissions.logs', compact('content'));
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
}
