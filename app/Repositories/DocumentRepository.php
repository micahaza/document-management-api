<?php

/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.15.
 * Time: 13:25
 */
namespace App\Repositories;

use App\Models\Document;
use App\Models\File;
use App\Traits\StatusTrait;
use Validator;
use App\Exceptions\ValidationException;
use App\Repositories\FileRepository;
use App\Exceptions\InvalidStatusChangeException;

class DocumentRepository
{
    use StatusTrait;

    public function createDocument($data)
    {
        $validator = Validator::make($data, [
            'type'                     => 'required|string|in:document',
            'attributes.user_id'       => 'required|integer',
            'attributes.actor_id'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Invalid input format', 422, null, [$validationErrors = $validator->errors()->all()]);
        }

        // we have to translate the default status name
        $data['attributes']['status'] = $this->getDocumentStatusIdByName('processing');
        $data['attributes']['client_id'] = request()->client_id;

        $document = Document::create($data['attributes']);
        $document->save();
        $fileRepository = new FileRepository();
        foreach($data['relationships']['files'] as $fileData) {
            $fileName = $fileRepository->storeUploadedFile($data['attributes']['user_id'], $fileData['data']['attributes']);
            $file = new File($fileData['data']['attributes']);
            $file->uploaded_name = $fileName;
            $document->files()->save($file);
        }
        return $document;
    }

    public function updateDocumentStatus(Document $document, $data)
    {
        $validator = Validator::make($data, [
            'type'                  => 'required|string|in:document',
            'id'                    => 'required|integer',
            'attributes.status'   => 'required|string|in:processing,approved,rejected,cpnn,deactivated',
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Invalid input format', 422, null, [$validationErrors = $validator->errors()->all()]);
        }
        $status = $this->getDocumentStatusIdByName($data['attributes']['status']);

        $this->isAllowedStatusChange($document, $data['attributes']['status']);

        $document->status = $status;
        $document->save();

        return $document;
    }

    public function deleteDocument($client_id, Document $document)
    {
        if($document->ownedBy($client_id)){
            if(!$document->delete()){
                app()->abort(404, 'Resource has not been found.');
            }
        } else {
            app()->abort(403, 'Unauthorized');
        }
    }

    /**
     * @param Document $document
     * @param $statusName
     * @return bool
     * @throws InvalidStatusChangeException
     */
    protected function isAllowedStatusChange(Document $document, $statusName)
    {
        if($document->files()->count() == 0) {
            throw new InvalidStatusChangeException('Invalid document status change request', 422, null);
        }
        switch($statusName) {
            case 'approved':
                foreach($document->files as $file) {
                    if(!in_array($file->status, [$this->getFileStatusIdByName('approved'), $this->getFileStatusIdByName('cpnn')])) {
                        throw new InvalidStatusChangeException('Invalid document status change request', 422, null);
                    }
                }
                break;
            case 'deactivated':
            case 'processing':
            case 'cpnn':
            case 'rejected':
                break;

        }
       return true;
    }

    public function addFileToDocument(Document $document, $fileData)
    {
        $fileRepository = new FileRepository();
        $fileName = $fileRepository->storeUploadedFile($document->user_id, $fileData['attributes']);
        $file = new File($fileData['attributes']);
        $file->uploaded_name = $fileName;
        $document->files()->save($file);

        return $file;
    }
}