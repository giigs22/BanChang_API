<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImgComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('img_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('file');
            $table->bigInteger('comp_id')->unsigned();
            $table->timestamps();

            $table->foreign('comp_id')->references('id')->on('complaints')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('img_complaints');
    }
}
