<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;

class DocumentUploadTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @test
     */
    public function we_can_upload_documents()
    {
        // Given if I have a well-formed JSON
        $document = factory(Document::class)->make();
        $files = factory(File::class, 2)->make();

        $data = [
            'type' => 'document',
            'attributes' => $document->toArray(),
            'files' => [
                [
                    'type' => 'file',
                    'attributes' => $files[0]->toArray()
                ],
                [
                    'type' => 'file',
                    'attributes' => $files[1]->toArray()
                ],
            ]
        ];
        // If I post it to upload url
        // I should see the file in the filesystem
        // And I should see the document in the database
        // And I should see the file(s) in the database
    }
}


