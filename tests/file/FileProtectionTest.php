<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\File;

class FileProtectionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * TODO: it must be fixed, does not work
     * @test
     */
    public function document_requests_will_showe_random_generated_file_urls()
    {
        // Given if I have a file
        $file = factory(File::class)->create();

        // when I request the document
        $this->json('GET', "api/v1/documents/{$file->document()->first()->id}", [], $this->validHttpHeader)
            //->dump()
            ->seeStatusCode(200);

        $response = json_decode($this->response->getContent());

        foreach($response->included as $fileData){
            $fileUrl = $fileData->attributes->links[0]->url;
            //echo $fileUrl."\n";
            //$fileData = file_get_contents($fileUrl);
            //dd($fileData);
        }
        // I'll see random url in the request
    }

}
