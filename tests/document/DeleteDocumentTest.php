<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;

class DeleteDocumentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function we_can_delete_existing_document()
    {
        // Given if I have a document
        $document = factory(Document::class)->create(['client_id' => 100]);

        // If I call the delete url
        $this->json('DELETE', "api/v1/documents/{$document->id}", [], $this->validHttpHeader)
            // I should see 204 response code
            ->seeStatusCode(204);
    }

    /**
     * @test
     */
    public function we_can_not_delete_existing_user_nonexisting_document()
    {
        // Given if I have a document
        $document = factory(Document::class)->create();
        $id = $document->id + 303030;

        // If I call the delete url
        $this->json('DELETE', "api/v1//documents/{$id}", [], $this->validHttpHeader)
            // I should see 204 response code
            ->seeStatusCode(404);

    }

    /**
     * @test
     */
    public function we_can_not_delete_non_existent_document_for_non_existing_user()
    {
        $this->json('DELETE', "v1/ASDFAASDFASDFASDFASDFASDFASDF/documents/12312312", [], $this->validHttpHeader)
            // I should see 204 response code
            ->seeStatusCode(404);
    }

    /**
     * @test
     */
    public function customer_can_not_delete_other_customers_documents()
    {
        // Given if I have a document with a file with a client id
        $document = factory(Document::class)->create(['client_id' => 313131]);

        // When I try to delete this file
        $this->json('DELETE', "api/v1/documents/{$document->id}", [], $this->validHttpHeader)
            // I should see HTTP 204
            ->seeStatusCode(403);

    }

}
