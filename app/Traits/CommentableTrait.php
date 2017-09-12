<?php

/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.03.09.
 * Time: 6:33
 */

namespace App\Traits;

use App\Models\Comment;

/**
 * With Laravel's polimorphic relations it gives you a power to
 * make a comment on basically every Models what you're using.
 * Just add:
 * use CommentableTrait to your model
 *
 * Class CommentableTrait
 * @package App\Traits
 */
trait CommentableTrait
{

    /**
     * Returns all the comments on this Eloquent model
     *
     * @return mixed
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Comment this Eloquent model by User
     *
     * @param User $user
     * @param $commentText
     */
    public function comment($actor_id, $client_id, $comment_text)
    {
        $comment = new Comment(['actor_id' => $actor_id, 'client_id' => $client_id, 'comment' => $comment_text]);
        $this->comments()->save($comment);
        return $comment;
    }

    /**
     * Returns true/false if this model is commented or not
     *
     * @param $actor_id
     * @return bool
     */
    public function isCommented($actor_id, $client_id)
    {
        return !! $this->comments()
            ->where('actor_id', $actor_id)
            ->where('client_id', $client_id)
            ->count();
    }

    /**
     * It deletes one comment from this Eloquent entity
     *
     * @param $actor_id
     * @param Comment $comment
     */
    public function deleteComment($actor_id, $client_id, Comment $comment)
    {
        $this->comments()
            ->where('actor_id', $actor_id)
            ->where('client_id', $client_id)
            ->where('id', $comment->id)
            ->where('commentable_id', $this->id)->delete();
    }

    /**
     * Returns the count of all comments
     *
     * @return mixed
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Returns the count of comments by User
     *
     * @param User $user
     * @return mixed
     */
    public function commentsCountByUser($actor_id, $client_id)
    {
        return $this->comments()
            ->where('actor_id', $actor_id)
            ->where('client_id', $client_id)
            ->count();
    }

    /**
     * Query scope for filtering clients
     * Usage: Document::client(26)
     *
     * @param $query
     * @param $clientId
     * @return mixed
     */
    public function scopeClient($query, $client_id)
    {
        return $query->where('client_id', $client_id);
    }

}