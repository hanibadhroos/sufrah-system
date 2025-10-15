<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpClientService
{
    public function get($uri, $headers = [])
    {
        $serviceUrl = $this->resolveService($uri);
        return Http::withHeaders($headers)->get($serviceUrl)->json();
    }

    public function post($uri, $data, $headers = [])
    {
        $serviceUrl = $this->resolveService($uri);
        return Http::withHeaders($headers)->post($serviceUrl, $data)->json();
    }

    private function resolveService($uri)
    {
        // هنا تحدد أي Microservice يتعامل مع هذا URI
        if (str_starts_with($uri, '/orders')) {
            return config('services.order_service').$uri;
        }
        if (str_starts_with($uri, '/users')) {
            return config('services.auth_service').$uri;
        }

        if (str_starts_with($uri, '/tenants')) {
            return config('services.tenant_service').$uri;
        }
        // وهكذا لبقية الخدمات...
    }
}
