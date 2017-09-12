<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');     // user id
            $table->integer('actor_id');       // user_id of the actor
            $table->integer('client_id')->index();  // the unique identifier of a client  26: Company1, 27: Company2
            $table->string('tag')->nullable(); // bank-pic, proof-of-address, id-card
            $table->unsignedTinyInteger('status')->default(1)->index();
            $table->index(['user_id', 'client_id']);
            $table->index(['actor_id', 'client_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('documents');
    }
}
