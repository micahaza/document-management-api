<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;

class AddFileToDocumentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function we_can_add_file_to_document()
    {
        // Given if I have a document
        $document = factory(Document::class)->create();

        $file = factory(File::class)->make();
        $file->encoded_data =
        $data = [
            'data' => [
                'type' => 'file',
                'attributes' => [
                    'actor_id'      => 133,
                    'original_name' => 'bug.png',
                    'mime_type'     => 'image/png',
                    'document_type' => 'idcard-pic',
                    'tag'           => 'idcard-pic',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/bug.png'))
                ]

            ]
        ];

        $this->assertEquals($document->files()->count(), 0);

        // If I try to add it to an existing document
        $this->json('POST', "api/v1/documents/{$document->id}/files", $data, $this->validHttpHeader)
            ->seeStatusCode(201);

        $this->assertEquals($document->files()->count(), 1);
    }
}
