<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDoubleEntryAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_entry_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('type_id');
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('system')->default(0);
            $table->boolean('enabled')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_entry_accounts');
    }
}
