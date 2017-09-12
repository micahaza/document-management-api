<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\File;
use App\Models\Document;
use Storage;
use App\Traits\StatusTrait;

class FileTest extends TestCase
{
    use DatabaseTransactions;

    use StatusTrait;

    /**
     * @expectedException \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @test
     */
    public function we_can_delete_file()
    {
        // Given if I have a document with a file
        $file = factory(File::class, 'with-physical-file')->create();
        // When I try to delete this file
        $this->json('DELETE', "api/v1/files/{$file->id}", [], $this->validHttpHeader)
            ->see('')
            // I should see HTTP 204
            ->seeStatusCode(204);
        Storage::disk('uploads')->get("{$file->document()->first()->user_id}/{$file->uploaded_name}");
    }

    /**
     * @test
     */
    public function customer_can_not_delete_other_customers_files()
    {
        // Given if I have a document with a file with a client id
        $document = factory(Document::class)->create(['client_id' => 313131]);
        $file = factory(File::class)->make();
        $document->files()->save($file);

        // When I try to delete this file
        $this->json('DELETE', "api/v1/files/{$file->id}", [], $this->validHttpHeader)
            // I should see HTTP 204
            ->seeStatusCode(403);
    }

    /**
     * Customers can see only files which belongs to them.
     *
     * @test
     */
    public function no_customer_allowed_to_see_other_customers_files()
    {
        // Given if I have a file with client_id 100
        $document = factory(Document::class)->create(['client_id' => 641]);
        $file = factory(File::class)->make();
        $document->files()->save($file);

        // And I request this resource
        $this->json('GET', "api/v1/files/{$file->id}", [], $this->validHttpHeader)
            ->seeStatusCode(403);

    }

    /**
     * @test
     */
    public function if_we_delete_one_file_from_an_approved_document_the_document_status_should_fall_back_to_processing()
    {
        // Given if I have an approved documment
        $file1 = factory(File::class, 'with-physical-file')->create();
        $file2 = factory(File::class, 'with-physical-file')->create();
        $document = $file1->document()->first();
        $document->files()->saveMany([$file1, $file2]);

        $this->assertEquals($document->files()->count(), 2);


        // When I try to delete one file
        $this->json('DELETE', "api/v1/files/{$file1->id}", [], $this->validHttpHeader)
            ->see('')
            // I should see HTTP 204
            ->seeStatusCode(204)
            // I should see document status is processing
            ->seeInDatabase('documents', ['id' => $document->id, 'status' => $this->getDocumentStatusIdByName('processing')]);


    }
}
