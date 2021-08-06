<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_id', 20)->unique();
            $table->unsignedInteger('price')->default(0);
            $table->string('name', 50);
            $table->unsignedBigInteger('type_id');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('type_id')
                ->references('id')
                ->on('item_types')
                ->onDelete('restrict')
                //->onDelete('set null')
                ->onUpdate('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
