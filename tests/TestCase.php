<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected $validHttpHeader = [
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/vnd.api+json',
        'X-API-KEY'     => '81nlsdu739juyjhdu351ldsd2lkjjh23kjhhsdfsd'
    ];

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $apiUrl = "";

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $this->apiUrl = "api/".env('API_VERSION')."/";

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // This is because I don't want to fire any network request during tests
        Cache::put($this->validHttpHeader['X-API-KEY'], 100, env('APP_AUTH_CACHE_TIMEOUT'));

        return $app;
    }
}
