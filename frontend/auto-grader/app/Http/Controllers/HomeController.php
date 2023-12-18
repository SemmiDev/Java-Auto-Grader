<?php

namespace App\Http\Controllers;

use Dflydev\DotAccessData\Data;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function index()
    {
        try {
            $response = $this->httpClient->get(config('app.api_url') . '/api/classes', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . request()->session()->get('auth_token'),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());
            $classes = $body->data->classes;

            $softColors = [
                '#F3E0D2',
                '#D4E2D4',
                '#E3D1E0',
                '#D4E8E8',
                '#F2D9E6',
                '#E7E9F1',
                '#E4D8D3',
                '#D5E3E4',
                '#ECE0DD',
                '#DAE6F0',
                '#E6D4D8',
                '#E7E0DC',
                '#F4E5E7',
                '#D6E9F3',
                '#E4E2D8',
                '#E0E6E1',
                '#E2E4F0',
                '#F2E0E7',
                '#D5E0E1',
                '#E4E7E1',
            ];

            return view('home', [
                'classes' => $classes,
                'softColors' => $softColors,
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
}
