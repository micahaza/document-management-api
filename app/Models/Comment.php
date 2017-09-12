<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'actor_id',
        'commented_by',
        'comment'
    ];

    public function ownedBy($client_id)
    {
        return $this->client_id == $client_id;
    }

}
