<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;
use App\Traits\StatusTrait;

class DocumentApproveTest extends TestCase
{
    use StatusTrait;
    use DatabaseTransactions;

    /**
     * @test
     */
    public function if_a_document_is_approved_all_its_files_status_should_not_be_changed()
    {
        $processingDocumentStatus = $this->getDocumentStatusIdByName('processing');
        $approvedDocumentStatus = $this->getDocumentStatusIdByName('approved');


        // Given if I have a document
        $document = factory(Document::class)->create(['client_id' => 100, 'status' => $processingDocumentStatus]);

        // And three files attached to it
        $file1 = factory(File::class)->make(['status' => $this->getFileStatusIdByName('cpnn')]);
        $file2 = factory(File::class)->make(['status' => $this->getFileStatusIdByName('approved')]);
        $file3 = factory(File::class)->make(['status' => $this->getFileStatusIdByName('approved')]);
        $document->files()->save($file1);
        $document->files()->save($file2);
        $document->files()->save($file3);

        // When I approve that document
        $data = ['data' => [
            'type' => 'document',
            'id' => $document->id,
            'attributes' => [
                'status' => 'approved'
            ]
        ]];

        $this->json('PATCH', "api/v1/documents/{$document->id}", $data, $this->validHttpHeader)
            ->seeJson()
            // The document should be approved and I should see it in the database
            ->seeInDatabase('documents',['id' => $document->id, 'status' => $approvedDocumentStatus])
            ->seeStatusCode(200);

        // and all its files must be approved as well
        $this->assertEquals($file1->status, $this->getFileStatusIdByName('cpnn'));
        $this->assertEquals($file2->status, $this->getFileStatusIdByName('approved'));
        $this->assertEquals($file3->status, $this->getFileStatusIdByName('approved'));

    }
}
