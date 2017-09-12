<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CommentableTrait;

class Document extends Model
{
    use SoftDeletes;
    use CommentableTrait;

    protected $fillable = [
        'user_id',
        'actor_id',
        'client_id',
        'tag',
        'status'
    ];

    public function files()
    {
        return  $this->hasMany(File::class);
    }

    public function ownedBy($client_id)
    {
        return $this->client_id == $client_id;
    }

}
