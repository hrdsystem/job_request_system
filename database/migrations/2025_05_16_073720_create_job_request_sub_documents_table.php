<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_request_sub_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('document_id');
            $table->string('required_name');
            $table->string('filling_mark');
            $table->string('header_name');
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_request_sub_documents');
    }
};
