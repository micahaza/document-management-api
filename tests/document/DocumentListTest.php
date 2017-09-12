<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;

class DocumentListTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @test
     */
    public function it_proves_we_can_get_one_user_documents()
    {
        // Given if I have a document with a specified username
        $document = factory(Document::class)->create(['client_id' => 100]);

        // And I request this resource
        $this->json('GET', "api/v1/{$document->user_id}/documents", [], $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(200)
            ->seeJsonContains(['user_id' => (string)$document->user_id])
            ->seeJsonContains(['actor_id' => (string)$document->actor_id]);
    }

    /**
     * @test
     */
    public function it_proves_that_we_can_get_all_the_files_for_a_specified_existing_user()
    {
        // Given if I have three files attached to a document
        $document = factory(Document::class)->create(['client_id' => 100]);
        $file1 = factory(File::class)->make();
        $file2 = factory(File::class)->make();
        $file3 = factory(File::class)->make();
        $document->files()->save($file1);
        $document->files()->save($file2);
        $document->files()->save($file3);

        // And I request this resource
        $this->json('GET', "api/v1/{$document->user_id}/documents", [], $this->validHttpHeader)
            ->seeStatusCode(200)
            // I should see it in the response
            ->seeJsonContains(['user_id' => (string)$document->user_id])

            ->seeJsonContains(['original_name' => $file1->original_name])
            ->seeJsonContains(['uploaded_name' => $file1->uploaded_name])
            ->seeJsonContains(['mime_type' => $file1->mime_type])

            ->seeJsonContains(['original_name' => $file2->original_name])
            ->seeJsonContains(['uploaded_name' => $file2->uploaded_name])
            ->seeJsonContains(['mime_type' => $file2->mime_type])

            ->seeJsonContains(['original_name' => $file3->original_name])
            ->seeJsonContains(['uploaded_name' => $file3->uploaded_name])
            ->seeJsonContains(['mime_type' => $file3->mime_type]);
    }

    /**
     * @test
     */
    public function we_can_get_one_document_for_one_user()
    {
        // Given if I have a document
        $document = factory(Document::class)->create(['client_id' => 100]);

        // And I request this resource
        $this->json('GET', "api/v1/documents/{$document->id}", [], $this->validHttpHeader)
            ->seeStatusCode(200)

            // I should see it in the response
            ->seeJsonContains(['id' => (string)$document->id])
            ->seeJsonContains(['user_id' => (string)$document->user_id])
            ->seeJsonContains(['actor_id' => (string)$document->actor_id]);
    }

    /**
     * @test
     */
    public function we_will_return_proper_error_message_if_we_request_non_existent_document()
    {
        $document = factory(Document::class)->create();
        $this->json('GET', "v1/{$document->user_id}/documents/21", [], $this->validHttpHeader)
            ->seeStatusCode(404);
            //->seeJsonContains(['status_code' => 404]);
    }

    /**
     * @test
     */
    public function we_will_return_proper_error_message_if_we_request_non_existent_user()
    {
        $this->json('GET', "v1/NonExistentUser.Name.For.Sure.34/documents", [], $this->validHttpHeader)
            ->seeStatusCode(404);
            //->seeJsonContains(['status_code' => 404]);
    }

    /**
     * Customers can see only documents which belongs to them.
     *
     * @test
     */
    public function no_customer_allowed_to_see_other_customers_documents()
    {
        // Given if I have a document
        $document = factory(Document::class)->create(['client_id' => 101]);

        // And I request this resource
        $this->json('GET', "api/v1/documents/{$document->id}", [], $this->validHttpHeader)
            ->seeStatusCode(403);

    }

    /**
     * @test
     */
    public function we_can_get_all_files_of_a_document()
    {
        // Given if I have a document with two files attached to it
        $document = factory(Document::class)->create(['client_id' => 100]);
        $file1 = factory(File::class)->make();
        $file2 = factory(File::class)->make();
        $document->files()->saveMany([$file1, $file2]);

        $this->assertEquals($document->files()->count(), 2);

        $this->json('GET', "api/v1/documents/{$document->id}/files", [], $this->validHttpHeader)
            ->seeStatusCode(200)
            ->seeJsonContains(['original_name' => $file1->original_name])
            ->seeJsonContains(['original_name' => $file2->original_name]);
    }

}
