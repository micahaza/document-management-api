<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/
use Illuminate\Support\Facades\Storage;

$factory->define(App\Models\Document::class, function (Faker\Generator $faker) {
    return [
        'user_id'       => $faker->randomNumber(5),
        'actor_id'      => $faker->randomNumber(5),
        'status'        => $faker->randomElement([1,2,3,4,5]),
        'tag'           => $faker->randomElement(['idcard', 'proof-of-address', 'bank-trustly', 'neteller']),
        'client_id'     => 100
    ];
});

$factory->define(App\Models\File::class, function (Faker\Generator $faker) {
    return [
        'document_id'   => factory(App\Models\Document::class)->create(['client_id' => 100])->id,
        'original_name' => $faker->uuid.'.'.$faker->fileExtension,
        'uploaded_name' => $faker->uuid.'.'.$faker->fileExtension,
        'mime_type'     => $faker->mimeType,
        'status'        => $faker->randomElement([1,2,3,4]),
        'tag'           => $faker->randomElement(['idcard-front', 'idcard-back','proof-of-address','neteller','instadebit','ecopayz','skrill'])
    ];
});

// factory for creating file objects with real files

$factory->defineAs(App\Models\File::class, 'with-physical-file', function (Faker\Generator $faker) {

    $document = factory(App\Models\Document::class)->create(['client_id' => 100]);
    $uploadedName = $faker->uuid.'.'.$faker->fileExtension;
    $fileContent = file_get_contents(database_path().'/test.bmp');
    Storage::disk('uploads')->put("{$document->user_id}/{$uploadedName}", $fileContent);

    return [
        'document_id'   => $document->id,
        'original_name' => $faker->uuid.'.bmp',
        'uploaded_name' => $uploadedName,
        'mime_type'     => $faker->mimeType,
        'status'        => $faker->randomElement([1,2,3,4]),
        'tag'           => $faker->randomElement(['idcard-front', 'idcard-back','proof-of-address','neteller','instadebit','ecopayz','skrill'])
    ];
});

$factory->define(App\Models\Comment::class, function (Faker\Generator $faker) {
    return [
        'client_id'     => $faker->randomNumber(2),
        'actor_id'      => $faker->randomNumber(2),
        'comment'       => $faker->sentence
    ];
});
