<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HttpClientService;

class OrderProxyController extends Controller
{
    public function __construct(private HttpClientService $httpClient) {}

    public function index(Request $request)
    {
        return $this->httpClient->get('/orders', $request->headers->all());
    }

    public function store(Request $request)
    {
        return $this->httpClient->post('/orders', $request->all(), $request->headers->all());
    }
}
