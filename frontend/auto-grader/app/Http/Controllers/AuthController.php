<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    protected Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function login()
    {
        if (request()->session()->has('auth_token')) {
            return redirect()->route('home');
        }

        return view('login');
    }

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $userFromGoogle = Socialite::driver('google')->stateless()->user();
            $user = collect($userFromGoogle)->get('user');
            $response = $this->httpClient->post(config('app.api_url') . '/api/login', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'picture' => $user['picture'],
                    'given_name' => $user['given_name'],
                ]
            ]);

            $body = json_decode($response->getBody()->getContents());

            $data = $body->data;
            $success = $body->success;

            if (!$success) {
                return redirect()->route('login')->with('error', $data['message']);
            }

            request()->session()->put('auth_token', $data->auth_token);
            request()->session()->put('user', $data->user);

            return redirect()->route('home');
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

    public function logout(Request $request)
    {
        $request->session()->forget('auth_token');
        $request->session()->forget('user');

        return redirect('/login');
    }
}
