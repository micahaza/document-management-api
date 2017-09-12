<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\File;
use App\Models\Document;

class FileStatusChangeTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * A basic test example.
     *
     * @dataProvider validFileStatusProvider
     * @test
     */
    public function we_can_change_one_file_status($newStatus)
    {
        $document = factory(Document::class)->create();
        $file = factory(File::class)->make();
        $document->files()->save($file);

        $data = ['data' => [
            'type' => 'file',
            'id' => $file->id,
            'attributes' => [
                'status' => $newStatus
            ]
        ]];
        
        $this->json('PATCH', "api/v1/files/{$file->id}", $data, $this->validHttpHeader)
            ->seeJsonContains(['status' => $newStatus])
            ->seeStatusCode(200);
    }

    /**
     * @dataProvider inValidFileStatusProvider
     * @test
     */
    public function we_can_not_make_mistake_with_wrong_file_status($invalidStatus)
    {
        $document = factory(Document::class)->create();
        $file = factory(File::class)->make();
        $document->files()->save($file);

        $data = ['data' => [
            'type' => 'file',
            'id' => $file->id,
            'attributes' => [
                'status' => $invalidStatus
            ]
        ]];

        $this->json('PATCH', "api/v1/files/{$file->id}", $data, $this->validHttpHeader)
            ->seeStatusCode(422);
    }

    public function validFileStatusProvider()
    {
        return [
            ['approved'],
            ['rejected'],
            ['processing']
        ];
    }

    public function inValidFileStatusProvider()
    {
        return [
            ['verifiedd'],
            [1],
            [array()],
            [new stdClass()],
            [null],
            [true]
        ];
    }
}
