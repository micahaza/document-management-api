<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\File;
use App\Models\Document;
use App\Models\Comment;
use Cyvelnet\Laravel5Fractal\Facades\Fractal;
use App\Transformers\CommentTransformer;

class CommentController extends Controller
{
    public function getFileComments(Request $request, File $file)
    {
        $comments = $file->comments()->get();
        return Fractal::collection($comments, new CommentTransformer(), 'comment')->responseJson(Response::HTTP_OK);
    }

    public function createFileComment(Request $request, File $file)
    {
        $data = $request->data['attributes'];
        $comment = $file->comment($data['actor_id'], $request->client_id, $data['comment']);
        return Fractal::item($comment, new CommentTransformer(), 'comment')->responseJson(Response::HTTP_CREATED);
    }

    public function getDocumentComments(Request $request, Document $document)
    {
        $comments = $document->comments()->get();
        return Fractal::collection($comments, new CommentTransformer(), 'comment')->responseJson(Response::HTTP_OK);
    }

    public function createDocumentComment(Request $request, Document $document)
    {
        $data = $request->data['attributes'];
        $comment = $document->comment($data['actor_id'], $request->client_id, $data['comment']);
        return Fractal::item($comment, new CommentTransformer(), 'comment')->responseJson(Response::HTTP_CREATED);
    }

    public function deleteComment(Request $request, Comment $comment)
    {
        if($comment->ownedBy($request->client_id)){
            if(!$comment->delete()){
                app()->abort(404, 'Resource has not been found.');
            }
        } else {
            app()->abort(403, 'Unauthorized');
        }
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
