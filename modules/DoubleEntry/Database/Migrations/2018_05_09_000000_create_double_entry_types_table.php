<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDoubleEntryTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_entry_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('class_id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->index('class_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_entry_types');
    }
}
