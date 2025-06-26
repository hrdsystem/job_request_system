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
        Schema::create('job_request_uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id');
            $table->integer('document_id');
            $table->integer('uploader');
            $table->dateTime('date_uploaded');
            $table->datetime('send_date');
            $table->smallInteger('latest');
            $table->integer('viewed_by')->nullable();
            $table->dateTime('date_viewed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_request_uploads');
    }
};
