<?php

namespace App\Http\Controllers;

use App\Repositories\FileRepository;
use App\Transformers\FileTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Transformers\DocumentTransformer;
use App\Transformers\CommentTransformer;
use App\Models\Document;
use App\Models\Comment;
use App\Models\File;
use Cyvelnet\Laravel5Fractal\Facades\Fractal;
use App\Repositories\DocumentRepository;
use App\Traits\StatusTrait;

class DocumentController extends Controller
{

    use StatusTrait;

    public function createDocument(Request $request)
    {
        $repository = new DocumentRepository();
        $document = $repository->createDocument($request->data);
        return Fractal::item($document, new DocumentTransformer(), 'document')->responseJson(Response::HTTP_CREATED);
    }

    public function getUserDocument(Request $request, Document $document)
    {
        if(!$document->ownedBy($request->client_id)){
            app()->abort(403, 'Unauthorized');
        }
        return Fractal::item($document, new DocumentTransformer(), 'document')->responseJson(Response::HTTP_OK);
    }

    public function deleteUserDocument(Request $request, Document $document)
    {
        $repository = new DocumentRepository();
        $repository->deleteDocument($request->client_id, $document);
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function getUserDocuments(Request $request)
    {
        $user_id = $request->user_id;
        $client_id = $request->client_id;
        $documents = Document::where('user_id', $user_id)
            ->where('client_id', $client_id)->get();
        if($documents->count() > 0){
            return Fractal::collection($documents, new DocumentTransformer(), 'document')->responseJson(Response::HTTP_OK);
        } else {
            app()->abort(404, 'Resource has not been found.');
        }
    }

    public function updateDocumentStatus(Request $request, Document $document)
    {
        $repository = new DocumentRepository();
        $document = $repository->updateDocumentStatus($document, $request->data);

        return Fractal::item($document, new DocumentTransformer(), 'document')->responseJson(Response::HTTP_OK);
    }

    public function getFilesOfDocument(Document $document)
    {
        $files = $document->files->all();
        return Fractal::collection($files, new FileTransformer(), 'file')->responseJson(Response::HTTP_OK);
    }

    public function addFileToDocument(Request $request, Document $document)
    {
        $repository = new DocumentRepository();
        $file = $repository->addFileToDocument($document, $request->data);
        return Fractal::item($file, new FileTransformer(), 'file')->responseJson(Response::HTTP_CREATED);
    }
}
