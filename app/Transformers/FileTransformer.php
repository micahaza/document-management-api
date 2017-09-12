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
use App\Models\File;
use App\Traits\StatusTrait;
use Webpatser\Uuid\Uuid;
use Cache;

class FileTransformer extends TransformerAbstract
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
    protected $defaultIncludes = [];

    /**
     * @param User $user
     * @return array
     */
    public function transform(File $file)
    {
        $status = $this->getFileStatusById($file->status);
        return [
            'id'            => $file->id,
            'original_name' => $file->original_name,
            'uploaded_name' => $file->uploaded_name,
            'mime_type'     => $file->mime_type,
            'tag'           => $file->tag,
            'status'        => $this->getFileStatusById($file->status),
            'created_at'    => $file->created_at->toDateTimeString(),
            'updated_at'    => $file->updated_at->toDateTimeString(),
            'links'   => [
                [
                    'self' => env('APP_URL')."/api/v1/files/{$file->id}",
                    'url'  => $this->generateOneTimeUrl($file)
                ]
            ]
        ];
    }

    private function generateOneTimeUrl(File $file)
    {
        if(request()->file_cache_key){
            $oneTimeHash = Cache::get(request()->file_cache_key);
        } else {
            $oneTimeHash = Uuid::generate();
            Cache::put((string)$oneTimeHash, $file->id, env('FILE_CACHE_TIMEOUT'));
        }
        return env('APP_URL')."/api/v1/file/{$oneTimeHash}";
    }

}
