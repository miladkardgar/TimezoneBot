<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('e_id');
            $table->integer('year');
            $table->integer('dayoff');
            $table->string('type');
            $table->string('category');
            $table->string('occasion');
            $table->dateTime('datetime');
            $table->integer('dayPr');
            $table->integer('mountPr');
            $table->integer('dayEn');
            $table->integer('mountEn');
            $table->softDeletes();
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
        Schema::dropIfExists('events');
    }
}
