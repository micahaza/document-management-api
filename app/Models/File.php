<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CommentableTrait;

class File extends Model
{
    use SoftDeletes;

    use CommentableTrait;

    protected $fillable = [
        'original_name',
        'uploaded_name',
        'mime_type',
        'status',
        'tag'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function ownedBy($client_id)
    {
        return $this->document()->first()->client_id == $client_id;
    }
}
