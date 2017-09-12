<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Art4\JsonApiClient\Utils\Manager;
use \Art4\JsonApiClient\Utils\Helper;

class ExceptionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_proves_we_will_get_proper_json_for_404()
    {
        $this->json('GET', $this->apiUrl."this-is-not-exist-for-sure", $this->validHttpHeader)
            ->seeStatusCode(404)
            ->seeJsonContains(['status' => "404"]);
    }

    /**
     * @test
     */
    public function we_get_json_for_405()
    {
        $this->json('GET', $this->apiUrl.'documents', $this->validHttpHeader)
            ->seeJsonContains(['status' => "405"])
            ->seeStatusCode(405);
    }

    /**
     * @test
     */
    public function we_test_validation_exception_json_response()
    {
        $data = ['data' => ['some' => 'thing']];
        $this->json('POST', $this->apiUrl.'documents', $data, $this->validHttpHeader)
            ->seeJsonContains(['status' => "422"])
            ->seeJsonContains(['title' => 'Unprocessable entity'])
            ->seeStatusCode(422);
    }
}
