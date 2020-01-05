<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDoubleEntryLedgerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_entry_ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('account_id');
            $table->morphs('ledgerable');
            $table->date('issued_at');
            $table->string('entry_type');
            $table->double('debit', 15, 4)->nullable();
            $table->double('credit', 15, 4)->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_entry_ledger');
    }
}
