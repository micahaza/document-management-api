<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Document;
use App\Models\File;
use App\Models\Comment;
use App\Traits\StatusTrait;

class AppServiceProvider extends ServiceProvider
{

    use StatusTrait;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Document::created(function ($document) {
            $document->comment(
                $document->actor_id,
                $document->client_id,
                "Document has been created for user: {$document->user_id}"
            );
        });

        Document::deleted(function ($document) {
            $document->comment(
                $document->actor_id,
                $document->client_id,
                "Document has been deleted."
            );
        });

        File::created(function ($file) {
            $document = $file->document()->first();
            $file->comment(
                $document->actor_id,
                $document->client_id,
                "File has been created."
            );
        });

        File::deleted(function ($file) {
            $document = $file->document()->first();
            $file->comment(
                $document->actor_id,
                $document->client_id,
                "File has been deleted."
            );
        });

        Document::updated(function ($document) {
            $status = $this->getDocumentStatusById($document->status);
            $document->comment(
                $document->actor_id,
                $document->client_id,
                "Document status has been updated to: {$status}"
            );
        });

        File::updated(function ($file) {
            $status = $this->getFileStatusById($file->status);
            $document = $file->document()->first();
            $file->comment(
                $document->actor_id,
                $document->client_id,
                "File status has been updated to: {$status}"
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
