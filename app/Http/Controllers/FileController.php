<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Transformers\FileTransformer;
use Cyvelnet\Laravel5Fractal\Facades\Fractal;
use App\Traits\StatusTrait;
use App\Repositories\FileRepository;
use Cache;
use Storage;

class FileController extends Controller
{
    use StatusTrait;

    public function updateFileStatus(Request $request, File $file)
    {
        $repository = new FileRepository();
        $file = $repository->updateFileStatus($file, $request->data);
        return Fractal::item($file, new FileTransformer(), 'file')->responseJson(Response::HTTP_OK);
    }

    public function deleteFile(Request $request, File $file)
    {
        if($file->ownedBy($request->client_id)){
            $document = $file->document()->first();
            $document->status = $this->getDocumentStatusIdByName('processing');
            $document->save();
            $file->delete();
            Storage::disk('uploads')->delete("{$file->document()->first()->user_id}/{$file->uploaded_name}");
        } else {
            app()->abort(403, 'Unauthorized');
        }
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function getFile(Request $request, File $file)
    {
        if(!$file->ownedBy($request->client_id)){
            app()->abort(403, 'Unauthorized');
        }
        return Fractal::item($file, new FileTransformer(), 'file')->responseJson(Response::HTTP_OK);
    }

    public function replaceFile(Request $request, File $file)
    {
        $repository = new FileRepository();
        $file = $repository->replaceFile($file, $request->data);
        return Fractal::item($file, new FileTransformer(), 'file')->responseJson(Response::HTTP_OK);
    }

    public function getFileByCacheKey(Request $request)
    {
        $cacheKey = $request->file_cache_key;
        $file_id = Cache::get($cacheKey);
        $file = File::findOrFail(intval($file_id));
        $realFile = Storage::disk('uploads')->get("{$file->document()->first()->user_id}/{$file->uploaded_name}");

        return (new Response($realFile, 200))->header('Content-Type', $file->mime_type);
    }

}
