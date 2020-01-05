<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDoubleEntryJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_entry_journals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->date('paid_at');
            $table->double('amount', 15, 4);
            $table->text('description');
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_entry_journals');
    }
}
