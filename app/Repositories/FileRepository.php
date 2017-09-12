<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.21.
 * Time: 14:58
 */

namespace App\Repositories;

use App\Models\File;
use App\Traits\StatusTrait;
use App\Exceptions\ValidationException;
use Webpatser\Uuid\Uuid;
use Validator;
use Storage;

class FileRepository
{
    use StatusTrait;

    public function updateFileStatus(File $file, $data)
    {
        $validator = Validator::make($data, [
            'type'                      => 'required|string|in:file',
            'id'                        => 'required|integer',
            'attributes.status'         => 'required|string|in:processing,approved,rejected',
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Invalid input format', 422, null, [$validationErrors = $validator->errors()->all()]);
        }
        $document = $file->document()->first()->update(['status' => $this->getDocumentStatusIdByName('processing')]);

        $file->status = $this->getFileStatusIdByName($data['attributes']['status']);
        $file->save();
        return $file;
    }

    public function replaceFile(File $file, $data)
    {
        $validator = Validator::make($data, [
            'type'                      => 'required|string|in:file',
            'id'                        => 'required|integer',
            'attributes.original_name'  => 'required|string',
            'attributes.tag'            => 'required|string',
            'attributes.status'         => 'required|string|in:processing,approved,rejected',
            'attributes.encoded_data'   => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Invalid input format', 422, null, [$validationErrors = $validator->errors()->all()]);
        }
        // Delete old file
        $user_id = $file->document()->first()->user_id;
        $fileName = $file->uploaded_name;
        Storage::disk('uploads')->delete("{$user_id}/{$fileName}");
        // Create new file
        // update File object

    }

    public function storeUploadedFile($user_id, array $data)
    {
        $uuid = Uuid::generate(4);
        $fileContent = base64_decode($data['encoded_data']);
        $fileName = "{$uuid}.".pathinfo($data['original_name'])['extension'];
        if(Storage::disk('uploads')->put("{$user_id}/{$fileName}", $fileContent)) {
            return $fileName;
        }
    }
}