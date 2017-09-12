<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\File;
use App\Models\Document;
use App\Models\Comment;

class FileCommentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @test
     */
    public function we_can_get_file_comments()
    {
        // Given if I have a file the in database
        $document = factory(Document::class)->create();
        $file = factory(File::class)->make();
        $document->files()->save($file);

        $comment = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $file->comments()->save($comment);

        // When I try to get this file's comments
        $this->json('GET', "api/v1/files/{$file->id}/comments", [], $this->validHttpHeader)
            ->seeStatusCode(200)
            // I should see the comment in results
            ->seeJsonContains(['actor_id' => (string)$comment->actor_id])
            ->seeJsonContains(['comment' => $comment->comment]);
    }

    /**
     * @test
     */
    public function we_can_delete_file_comment()
    {
        // Given if I have a file in the database
        $document = factory(Document::class)->create(['client_id' => 100]);
        $file = factory(File::class)->make();
        $document->files()->save($file);

        // And it has one comment
        $comment = factory(Comment::class)->make(['client_id' => $document->client_id]);
        $file->comments()->save($comment);
        $commentsCount = $file->commentsCount;

        // If I try to delete this comment
        $this->json('DELETE', "api/v1/comments/{$comment->id}", [], $this->validHttpHeader)
            // I should see it in the response
            ->see('')
            ->seeStatusCode(204);

        // And that file should have zero comments
        $this->assertEquals($file->commentsCount, $commentsCount-1);
    }

    /**
     * @test
     */
    public function we_can_comment_on_file()
    {
        // Given if I have a document with a file
        $document = factory(Document::class)->create();
        $file = factory(File::class)->make();
        $document->files()->save($file);
        // And a comment
        $comment = factory(Comment::class)->make();

        // If I try to comment on this file
        $data = [
            'data' => [
                'type' => 'file-comment',
                'attributes' => [
                    'client_id' => $comment->client_id,
                    'actor_id'  => $comment->actor_id,
                    'comment'   => $comment->comment
                ]
            ]];
        $this->json('POST', "api/v1/files/{$file->id}/comments", $data, $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(201)
            // and in the database
            ->seeInDatabase('comments', [
                'actor_id'          => $comment->actor_id,
                'commentable_id'    => $file->id,
                'comment'           => $comment->comment
            ]);
    }

    /**
     * @test
     */
    public function customer_can_not_delete_other_customers_file_comment()
    {
        // Given if I have a document with a file
        $document = factory(Document::class)->create(['client_id' => 3131]);
        $file = factory(File::class)->make();
        $document->files()->save($file);
        // And a comment
        $comment = factory(Comment::class)->make(['client_id' => 3131]);
        $file->comments()->save($comment);
        $commentsCount = $file->commentsCount;

        // If I try to delete this comment
        $this->json('DELETE', "api/v1/comments/{$comment->id}", [], $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(403);

        // And that file should have zero comments
        $this->assertEquals($file->commentsCount, $commentsCount);
    }
}
