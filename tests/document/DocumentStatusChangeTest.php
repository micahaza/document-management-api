<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;
use App\Traits\StatusTrait;

class DocumentStatusChangeTest extends TestCase
{
    use DatabaseTransactions;
    use StatusTrait;

    /**
     * @test
     */
    public function newly_created_documents_must_have_processing_status()
    {
        // Given if I have a well-formed input array
        $document = factory(Document::class)->make();

        $fileData = factory(File::class)->make()->toArray();
        $fileData['encoded_data'] = 'asdf';

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
                            'attributes' => $fileData
                        ]
                        ]
                    ]
                ]
            ]
        ];

        // If I try to create a document
        $this->json('POST', "api/v1/documents", $data, $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(201)
            ->seeInDatabase('documents', [
                'status'    => $this->getDocumentStatusIdByName('processing')
            ]);
    }

    /**
     * @test
     */
    public function we_allow_documents_to_exist_without_any_files_attached_to_it_but_they_remain_in_processing_status()
    {
        // Given if I have a well-formed input array
        $document = factory(Document::class)->create();

        $fileData = factory(File::class)->make()->toArray();
        $fileData['encoded_data'] = 'asdf';

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
                            'attributes' => $fileData
                        ]
                        ]
                    ]
                ]
            ]
        ];

        // If I try to create a document
        $this->json('POST', "api/v1/documents", $data, $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(201)
            ->seeInDatabase('documents', [
                'status'    => $this->getDocumentStatusIdByName('processing')
            ]);

        $response = json_decode($this->response->getContent());
        $documentId = $response->data->id;
        $fileId = $response->included[0]->id;
        $document = Document::find($documentId);

        // If I delete the file
        $this->json('DELETE', "api/v1/files/{$fileId}", [], $this->validHttpHeader)
            ->seeStatusCode(204);
        // Document must have zero files
        $this->assertEquals($document->files()->count(), 0);

        // The document status can not be different than processing
        $this->assertEquals($document->status, $this->getDocumentStatusIdByName('processing'));

        // If I try to change document status
        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => 'approved'
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeJsonContains(['detail' => 'Invalid document status change request'])
            ->seeStatusCode(422);
        $this->assertEquals($document->status, $this->getDocumentStatusIdByName('processing'));
    }

    /**
     * Empty document status change test
     *
     * @dataProvider validDocumentStatusProvider
     * @test
     */
    public function we_can_not_change_document_status_if_it_has_no_files($newStatus)
    {
        $document = factory(Document::class)->create(['status' => $this->getDocumentStatusIdByName('processing')]);

        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => $newStatus
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeJsonContains(['detail' => 'Invalid document status change request'])
            ->seeStatusCode(422);
        $this->assertEquals($document->status, $this->getDocumentStatusIdByName('processing'));
    }

    /**
     * @test
     */
    public function document_can_be_approved_if_all_its_files_are_either_approved_or_cpnn_status()
    {
        // Given if I have a documment
        $document = factory(Document::class)->create(['status' => $this->getDocumentStatusIdByName('processing')]);

        // With three files attached to it with status approved or cpnn
        $file1 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('cpnn')]);
        $file2 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('approved')]);
        $file3 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('cpnn')]);
        $document->files()->saveMany([$file1, $file2, $file3]);
        $this->assertEquals($document->files()->count(), 3);

        // When I try to approve the whole document
        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => 'approved'
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(200);

        // I should see it in the response
        $response = json_decode($this->response->getContent());
        $this->assertEquals($response->data->attributes->status, 'approved');

        // And in the database
        $this->seeInDatabase('documents', ['id' => $document->id, 'status' => $this->getDocumentStatusIdByName('approved')]);
    }

    /**
     * @test
     */
    public function document_can_not_be_approved_if_at_least_one_of_its_files_is_not_in_approved_or_cpnn_status()
    {
        // Given if I have a documment
        $document = factory(Document::class)->create(['status' => $this->getDocumentStatusIdByName('processing')]);

        // With three files attached to it with status approved or cpnn
        $file1 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('cpnn')]);
        $file2 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('processing')]);
        $file3 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('rejected')]);
        $document->files()->saveMany([$file1, $file2, $file3]);
        $this->assertEquals($document->files()->count(), 3);

        // When I try to approve the whole document
        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => 'approved'
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(422);

        // I should see it in the response
        $response = json_decode($this->response->getContent());
        $this->seeJsonContains(['detail' => 'Invalid document status change request']);

        // And in the database
        $this->seeInDatabase('documents', ['id' => $document->id, 'status' => $this->getDocumentStatusIdByName('processing')]);
    }

    /**
     * @dataProvider inValidDocumentStatusProvider
     * @test
     */
    public function we_can_not_make_mistake_with_wrong_document_status($invalidStatus)
    {
        $document = factory(Document::class)->create();

        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => $invalidStatus
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(422);
    }

    /**
     * @test
     */
    public function if_file_status_changes_on_approved_document_the_document_returns_automatically_to_processing()
    {
        // Given if I have an approved documment
        $document = factory(Document::class)->create(['status' => $this->getDocumentStatusIdByName('approved')]);

        // With three files attached to it with status approved or cpnn
        $file1 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('cpnn')]);
        $file2 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('approved')]);
        $document->files()->saveMany([$file1, $file2]);
        $this->assertEquals($document->files()->count(), 2);

        // When I try to change file status
        $data = ['data' => [
            'type' => 'file',
            'id' => $file1->id,
            'attributes' => [
                'status' => 'approved'
            ]
        ]];

        $this->json('PATCH', "api/v1/files/{$file1->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(200)
            // I should see document status is processing
            ->seeInDatabase('documents', ['id' => $document->id, 'status' => $this->getDocumentStatusIdByName('processing')]);

    }

    /**
     * @test
     */
    public function if_document_is_deactivated_all_its_files_will_keep_their_statuses()
    {
        // Given if I have an approved documment
        $document = factory(Document::class)->create(['status' => $this->getDocumentStatusIdByName('approved')]);

        // With three files attached to it with status approved or cpnn
        $file1 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('cpnn')]);
        $file2 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('approved')]);
        $file3 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('processing')]);
        $file4 = factory(File::class)->create(['status' => $this->getFileStatusIdByName('rejected')]);
        $document->files()->saveMany([$file1, $file2, $file3, $file4]);
        $this->assertEquals($document->files()->count(), 4);

        // When I try to change file status
        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => 'deactivated'
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(200)
            // I should see document status is processing
            ->seeInDatabase('documents', ['id' => $document->id, 'status' => $this->getDocumentStatusIdByName('deactivated')]);

        $this->assertEquals($file1->status, $this->getFileStatusIdByName('cpnn'));
        $this->assertEquals($file2->status, $this->getFileStatusIdByName('approved'));
        $this->assertEquals($file3->status, $this->getFileStatusIdByName('processing'));
        $this->assertEquals($file4->status, $this->getFileStatusIdByName('rejected'));
    }

    public function validDocumentStatusProvider()
    {
        return [
            ['approved'],
            ['rejected'],
            ['processing']
        ];
    }

    public function inValidDocumentStatusProvider()
    {
        return [
            ['verifiedd'],
            [1],
            [array()],
            [new stdClass()],
            [null],
            [true]
        ];
    }
}
