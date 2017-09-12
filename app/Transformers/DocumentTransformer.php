<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.12.
 * Time: 10:41
 */

namespace App\Transformers;

use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App\Models\Document;
use App\Traits\StatusTrait;

class DocumentTransformer extends TransformerAbstract
{
    use StatusTrait;

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = ['files'];

    /**
     * @param User $user
     * @return array
     */
    public function transform(Document $document)
    {
        $status = $this->getDocumentStatusById($document->status);
        return [
            'id'            => $document->id,
            'user_id'       => $document->user_id,
            'actor_id'      => $document->actor_id,
            'tag'           => $document->tag,
            'status'        => $this->getDocumentStatusById($document->status),
            'created_at'    => $document->created_at->toDateTimeString(),
            'updated_at'    => $document->updated_at->toDateTimeString(),
            'links'   => [
                [
                    'self' => env('APP_URL')."/api/v1/documents/{$document->id}"
                ]
            ]
        ];
    }

    public function includeFiles(Document $document)
    {
        if($document->files()->count() > 0){
            $files = $document->files()->get();
            return $this->collection($files, new FileTransformer(), 'file');
        }
    }

}
