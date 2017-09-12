<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Document;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class CreateDocumentTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        // removing all directories under public/uploads
        $dirs = Storage::disk('uploads')->directories();
        foreach($dirs as $dir){
            Storage::disk('uploads')->deleteDirectory($dir);
        }
    }

    /**
     * It proves that we can create document wiht one file attached
     * No phisical file existence check
     *
     * @dataProvider validDataProviderOneFile
     * @test
     */
    public function we_can_create_document_with_one_file($fileData)
    {
        // Given if I have a well-formed input array
        $document = factory(Document::class)->make();

        $data = [
            'data' => [
                'type' => 'document',
                'attributes' => [
                    'user_id'       => $document->user_id,
                    'actor_id'      => $document->actor_id,
                    'tag'           => $document->tag,
                ],
                'relationships' => [
                    'files' => [
                        ['data' => [
                            'type' => 'file',
                            'attributes' => $fileData['attributes']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // If I try to create a document
        $this->json('POST', "api/v1/documents", $data, $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(201)
            ->seeJsonContains(['user_id' => $document->user_id])
            ->seeJsonContains(['actor_id' => $document->actor_id])
            ->seeJsonContains(['tag' => $document->tag])
            ->seeJsonContains(['tag' => $fileData['attributes']['tag']])
            ->seeJsonContains(['original_name' => $fileData['attributes']['original_name']])
            ->seeJsonContains(['mime_type' => $fileData['attributes']['mime_type']])
            // And also in the database
            ->seeInDatabase('documents', [
                'user_id'   => $document->user_id,
                'actor_id'  => $document->actor_id,
                'client_id' => Cache::get($this->validHttpHeader['X-API-KEY']),
                'tag'       => $document->tag,
                'status'    => 1
            ])
            ->seeInDatabase('files', [
                'original_name'     => $fileData['attributes']['original_name'],
                'mime_type'         => $fileData['attributes']['mime_type'],
                'tag'               => $fileData['attributes']['tag'],
                'status'    => 1
            ]);


        // check if file was uploaded successfully under public/uploads/user_id
        //$response = json_decode($this->response->content());
        //$fileName = "{$document->user_id}/{$response->included['0']->attributes->uploaded_name}";
        //$this->assertTrue(Storage::disk('uploads')->exists($fileName));
    }

    /**
     * @param $file1
     * @param $file2
     *
     * @dataProvider validDataProviderTwoFile
     * @test
     */
    public function we_can_create_document_with_two_files($file1, $file2)
    {
        // Given if I have a well-formed input array
        $document = factory(Document::class)->make();
        $data = [
            'data' => [
                'type' => 'document',
                'attributes' => [
                    'user_id'       => $document->user_id,
                    'actor_id'      => $document->actor_id,
                    'tag'           => $document->tag,
                    'client_id'     => $document->client_id,
                ],
                'relationships' => [
                    'files' => [
                            ['data' => [
                                'type' => 'file',
                                'attributes' => $file1
                                ]
                            ],
                            ['data' => [
                                'type' => 'file',
                                'attributes' => $file2
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        // If I try to create a document
        $this->json('POST', "api/v1/documents", $data, $this->validHttpHeader)
            // I should see it in the response
            ->seeStatusCode(201)
            ->seeJsonContains(['user_id' => $document->user_id])
            ->seeJsonContains(['actor_id' => $document->actor_id])
            ->seeJsonContains(['tag' => $document->tag])
            ->seeJsonContains(['tag' => $file1['tag']])
            ->seeJsonContains(['original_name' => $file1['original_name']])
            ->seeJsonContains(['mime_type' => $file1['mime_type']])
            ->seeInDatabase('files', [
                'original_name'     => $file1['original_name'],
                'mime_type'         => $file1['mime_type'],
                'tag'               => $file1['tag'],
                'status'    => 1
            ])
            ->seeInDatabase('files', [
                'original_name'     => $file2['original_name'],
                'mime_type'         => $file2['mime_type'],
                'tag'               => $file2['tag'],
                'status'    => 1
            ]);

        // check if both files were uploaded successfully under public/uploads/user_id
        $response = json_decode($this->response->content());

        $fileName = "{$document->user_id}/{$response->included['0']->attributes->uploaded_name}";
        $this->assertTrue(Storage::disk('uploads')->exists($fileName));

        $fileName = "{$document->user_id}/{$response->included['1']->attributes->uploaded_name}";
        $this->assertTrue(Storage::disk('uploads')->exists($fileName));
    }

    public function validDataProviderOneFile()
    {
        return [
            ['png' => [
                "attributes" => [
                    'actor_id'      => 13,
                    'original_name' => 'bug.png',
                    'mime_type'     => 'image/png',
                    'document_type' => 'idcard-pic',
                    'tag'           => 'neteller',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/bug.png'))
                    ]
                ]
            ],
            ['bmp' => [
                "attributes" => [
                    'actor_id'      => 10,
                    'original_name' => 'test.bmp',
                    'mime_type'     => 'image/bmp',
                    'document_type' => 'some-pic',
                    'tag'           => 'bank-pic',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.bmp'))
                    ]
                ]
            ],
            ['pdf' => [
                "attributes" => [
                    'actor_id'      => 44,
                    'original_name' => 'test.pdf',
                    'mime_type'     => 'application/pdf',
                    'document_type' => 'idcard-pic',
                    'tag'           => 'idcard-pic-front',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.pdf'))
                    ]
                ]
            ],
            ['doc' => [
                "attributes" => [
                    'actor_id'      => 33,
                    'original_name' => 'test.doc',
                    'mime_type'     => 'application/msword',
                    'document_type' => 'my-life',
                    'tag'           => 'idcard-pic-back',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.doc'))
                    ]
                ]
            ],
            ['docx' => [
                "attributes" => [
                    'actor_id'      => 33,
                    'original_name' => 'test.docx',
                    'mime_type'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'document_type' => 'my-life-short-story',
                    'tag'           => 'passport-pic-front',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.docx'))
                    ]
                ]
            ],
            ['odt' => [
                "attributes" => [
                    'actor_id'      => 33,
                    'original_name' => 'test.odt',
                    'mime_type'     => 'application/vnd.oasis.opendocument.text',
                    'document_type' => 'my-life-long-story',
                    'tag'           => 'passport-pic-back',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.odt'))
                    ]
                ]
            ],
            ['rtf' => [
                "attributes" => [
                    'actor_id'      => 33,
                    'original_name' => 'test.rtf',
                    'mime_type'     => 'application/rtf',
                    'document_type' => 'my-life-long-story',
                    'tag'           => 'passport-pic-back',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.rtf'))
                    ]
                ]
            ],
            ['tiff' => [
                "attributes" => [
                    'actor_id'      => 33,
                    'original_name' => 'test.tiff',
                    'mime_type'     => 'image/tiff',
                    'document_type' => 'my-life-long-story',
                    'tag'           => 'passport-pic-back',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.tiff'))
                    ]
                ]
            ],
            ['jpg' => [
                "attributes" => [
                    'actor_id'      => 31,
                    'original_name' => 'bug.jpg',
                    'mime_type'     => 'image/jpeg',
                    'document_type' => 'my-life-long-story-jpg',
                    'tag'           => 'passport-pic-back',
                    'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/bug.jpg'))
                    ]
                ]
            ]
        ];
    }

    public function validDataProviderTwoFile()
    {
        return [[
            "file1" => [
                'actor_id'      => 13,
                'original_name' => 'bug.jpeg',
                'mime_type'     => 'image/jpeg',
                'document_type' => 'idcard-pic-front',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/bug.jpg'))
            ],
            "file2" => [
                'actor_id'      => 13,
                'original_name' => 'bug.png',
                'mime_type'     => 'image/png',
                'document_type' => 'idcard-pic-back',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/bug.png'))
            ]
        ],[
            "file1" => [
                'actor_id'      => 13,
                'original_name' => 'test.bmp',
                'mime_type'     => 'image/bmp',
                'document_type' => 'idcard-pic-front',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.bmp'))
            ],
            "file2" => [
                'actor_id'      => 13,
                'original_name' => 'test.doc',
                'mime_type'     => 'application/msword',
                'document_type' => 'idcard-pic-back',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.doc'))
            ]
        ],[
            "file1" => [
                'actor_id'      => 13,
                'original_name' => 'test.pdf',
                'mime_type'     => 'application/pdf',
                'document_type' => 'idcard-pic-front',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.pdf'))
            ],
            "file2" => [
                'actor_id'      => 13,
                'original_name' => 'test.rtf',
                'mime_type'     => 'application/rtf',
                'document_type' => 'idcard-pic-back',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.rtf'))
            ]
        ],[
            "file1" => [
                'actor_id'      => 13,
                'original_name' => 'test.odt',
                'mime_type'     => 'application/vnd.oasis.opendocument.text',
                'document_type' => 'idcard-pic-front',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.odt'))
            ],
            "file2" => [
                'actor_id'      => 13,
                'original_name' => 'test.tiff',
                'mime_type'     => 'image/tiff',
                'document_type' => 'idcard-pic-back',
                'tag'           => 'idcard',
                'encoded_data'  => base64_encode(file_get_contents(realpath(dirname(__FILE__)).'/testfiles/test.tiff'))
            ]
        ]];
    }
}
