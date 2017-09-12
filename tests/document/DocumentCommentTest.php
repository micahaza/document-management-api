<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\Comment;

class DocumentCommentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function we_can_get_document_comments()
    {
        // Given if I have a document
        $document = factory(Document::class)->create();
        $comment = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $document->comments()->save($comment);

        // When I try to comment on it
        $this->json('GET', "api/v1/documents/{$document->id}/comments", [], $this->validHttpHeader)
            ->seeStatusCode(200)

            // I should see the comment in results
            ->seeJsonContains(['actor_id' => (string)$comment->actor_id])
            ->seeJsonContains(['comment' => $comment->comment]);
    }

    /**
     * @test
     */
    public function we_can_get_list_of_document_comments()
    {
        // Given if I have a document
        $document = factory(Document::class)->create();
        $comment1 = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $comment2 = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $comment3 = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $document->comments()->saveMany([$comment1, $comment2, $comment3]);

        // When I try to comment on it
        $this->json('GET', "api/v1/documents/{$document->id}/comments", [], $this->validHttpHeader)
            ->seeStatusCode(200)

            // I should see the comment in results
            ->seeJsonContains(['comment' => $comment1->comment])
            ->seeJsonContains(['comment' => $comment2->comment])
            ->seeJsonContains(['comment' => $comment3->comment]);
    }

    /**
     * @test
     */
    public function we_can_comment_on_existing_documents()
    {
        // Given if I have a document
        $document = factory(Document::class)->create();

        // When I try to comment on it
        $data = [
            'data' => [
                'type' => 'document-comment',
                'attributes' => [
                    'comment' => 'Awesome something',
                    'actor_id' => 123
                ]
        ]];

        $this->json('POST', "api/v1/documents/{$document->id}/comments", $data, $this->validHttpHeader)
            ->seeStatusCode(201);
        // It should be okay and I should see the comment in the response
    }

    /**
     * @test
     */
    public function we_can_delete_document_comment()
    {
        // Given if I have a document
        $document = factory(Document::class)->create(['client_id' => 100]);

        // With a comment on it
        $comment = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $document->comments()->save($comment);

        // If I try to delete it
        $this->json('DELETE', "api/v1/comments/{$comment->id}", [], $this->validHttpHeader)
            // I should see 204
            ->seeStatusCode(204);
    }

    /**
     * @test
     */
    public function customer_can_not_delete_other_customers_document_comment()
    {
        // Given if I have a document with a file
        $document = factory(Document::class)->create(['client_id' => 3131]);
        $comment = factory(Comment::class)->make(['client_id' => 3131]);
        $document->comments()->save($comment);

        // I need it because the automatic comments
        $commentsCount = $document->commentsCount;

        // If I try to delete this comment
        $this->json('DELETE', "api/v1/comments/{$comment->id}", [], $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(403);

        // And that file should have zero comments
        $this->assertEquals($document->commentsCount, $commentsCount);
    }

}
