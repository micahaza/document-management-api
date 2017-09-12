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
use App\Models\Comment;

class CommentTransformer extends TransformerAbstract
{
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
    public function transform(Comment $comment)
    {
        return [
            'id' => $comment->id,
            'attributes' => [
                'actor_id'      => $comment->actor_id,
                'comment'       => $comment->comment,
                'created_at'    => $comment->created_at,
                'updated_at'    => $comment->updated_at
            ],
            'links'   => [
                [
                    'self' => env('APP_URL')."/api/v1/comments/{$comment->id}",
                ]
            ]
        ];
    }

}
