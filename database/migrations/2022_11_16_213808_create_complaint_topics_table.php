<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_topics', function (Blueprint $table) {
            $table->id();
            $table->string('en');
            $table->string('th');
            $table->string('complaintType');
            $table->string('iconName')->default('crosshairs-question');
            $table->string('iconType')->default('material-community');
            $table->string('targetRole')->nullable();
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
        Schema::dropIfExists('complaint_topics');
    }
}
