<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    protected Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'description' => 'nullable',
            ], [
                'name.required' => 'Nama kelas tidak boleh kosong',
            ]);

            $this->httpClient->post(config('app.api_url') . '/api/classes', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
                'json' => [
                    'name' => $request->name,
                    'description' => $request->description,
                ]
            ]);

            return redirect()->route('home')->with('success', 'Kelas berhasil dibuat');
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

    public function join(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required',
            ], [
                'code.required' => 'Kode kelas tidak boleh kosong',
            ]);

            $response = $this->httpClient->post(config('app.api_url') . '/api/classes/join', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
                'json' => [
                    'code' => $request->code,
                    'role' => 'Student',
                ]
            ]);

            $body = json_decode($response->getBody()->getContents());

            if (!$body->success) {
                return redirect()->route('home')->with('error', $body->message);
            }

            return redirect()->route('home')->with('success', "Berhasil bergabung");
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

    public function assignment($classId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());

            if (!$body->success) {
                return redirect()->route('home')->with('error', $body->message);
            }

            $assignments = $body->data->class->assignments;
            $assignments = array_reverse($assignments);

            $total = count($assignments);
            $permissions = $body->data->permissions;

            return view('classes.assignment', [
                'classId' => $classId,
                'assignments' => $assignments,
                'total' => $total,
                'permissions' => $permissions,
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

    public function member($classId)
    {
        try {

            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());

            if (!$body->success) {
                return redirect()->route('home')->with('error', $body->message);
            }

            $teachers = $body->data->teachers;
            $students = $body->data->students;
            $owner = $body->data->owner;

            $classCode = $body->data->class->code;
            $permissions = $body->data->permissions;
            $total = count($students) + count($teachers) + 1;

            return view('classes.member', [
                'classId' => $classId,
                'owner' => $owner,
                'students' => $students,
                'teachers' => $teachers,
                'total' => $total,
                'classCode' => $classCode,
                'permissions' => $permissions,
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

    public function setting($classId)
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes/' . $classId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());

            if (!$body->success) {
                return redirect()->route('home')->with('error', $body->message);
            }

            $class = $body->data->class;

            $className = $class->name;
            $classDescription = $class->description;
            $classCode = $class->code;

            $permissions = $body->data->permissions;

            return view('classes.setting', [
                'classId' => $classId,
                'className' => $className,
                'classCode' => $classCode,
                'classDescription' => $classDescription,
                'permissions' => $permissions,
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

    public function update(Request $request, $classId)
    {
        try {
            $request->validate([
                'name' => 'required',
                'description' => 'nullable',
            ], [
                'name.required' => 'Nama kelas tidak boleh kosong',
            ]);

            $response = $this->httpClient->put(config('app.api_url') . '/api/classes/' . $classId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
                'json' => [
                    'name' => $request->name,
                    'description' => $request->description,
                ]
            ]);

            return redirect()->route('classes.setting', ['classId' => $classId])->with('success', 'Kelas berhasil diubah');
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

    public function destroy($classId)
    {
        try {
            $this->httpClient->delete(config('app.api_url') . '/api/classes/' . $classId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            return redirect()->route('home')->with('success', 'Kelas berhasil dihapus');
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

    public function addMember(Request $request, $classCode)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'role' => 'required|in:Student,Teacher',
            ], [
                'email.required' => 'Email tidak boleh kosong',
                'email.email' => 'Email tidak valid',
                'role.required' => 'Role tidak boleh kosong',
                'role.in' => 'Role tidak valid',
            ]);

            $this->httpClient->post(config('app.api_url') . '/api/classes/join', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
                'json' => [
                    'code' => $classCode,
                    'email' => $request->email,
                    'role' => $request->role,
                ]
            ]);

            return redirect()->back()->with('success', "Berhasil bergabung");
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

    public function removeMember($classId, $userId)
    {
        try {
            $this->httpClient->delete(config('app.api_url') . '/api/classes/' . $classId . '/members/' . $userId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            return redirect()->back()->with('success', 'Anggota berhasil dihapus');
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

    public function leave($classId)
    {
        try {
            $userId = session()->get('user')->id;
            $this->httpClient->delete(config('app.api_url') . '/api/classes/' . $classId . '/members/' . $userId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            return redirect()->route('home')->with('success', 'Berhasil keluar dari kelas');
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
}
