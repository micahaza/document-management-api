<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiTokenAuth
{
    /**
     * Trying to authenticate with auth-test.
     * If it is successful we store the result in Cache
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = $request->header('X-API-KEY');

        if(Cache::has($key)){
            $request->client_id = Cache::get($key);
            return $next($request);
        }

        $response = json_decode(file_get_contents('http://auth-test.company.com/api/0.1/customer/auth?key='.$key), true);
        if ($response['success'] != true) {
            abort(401, 'Invalid API key');
        }

        // put the key into the Cache
        Cache::put($key, $response['id'], env('APP_AUTH_CACHE_TIMEOUT'));
        // add customer_id to the request
        $request->client_id = Cache::get($key);

        return $next($request);
    }
}
