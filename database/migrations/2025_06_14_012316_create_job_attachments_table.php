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
        Schema::create('job_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_request_id');
            $table->string('orig_filename');
            $table->string('file_hash');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted_by')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_attachments');
    }
};
