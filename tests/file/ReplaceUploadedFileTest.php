<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\File;
use App\Models\Document;

class ReplaceUploadedFileTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_proves_that_we_can_replace_uploaded_file()
    {
        // Given if I have an uploaded document
        // With a physical file attached to it
        $document = factory(Document::class)->make();

        $data = [
            'data' => [
                'type' => 'document',
                'attributes' => [
                    'user_id'       => $document->user_id,
                    'actor_id'      => $document->actor_id,
                    'tag'           => $document->tag,
                ],
                'relationships' => [
                    'files' => [
                        ['data' => [
                            'type' => 'file',
                            'attributes' => [
                                'actor_id'      => 13,
                                'original_name' => 'bug.png',
                                'mime_type'     => 'image/png',
                                'document_type' => 'idcard-pic',
                                'tag'           => 'neteller',
                                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/../document/testfiles/bug.png'))
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // I create the document with a file
        $this->json('POST', "api/v1/documents", $data, $this->validHttpHeader)
            ->seeStatusCode(201);

        // When I try to replace the file
        // I should see it in the response
        // And in the database
        // And in the filesystem
    }
}
