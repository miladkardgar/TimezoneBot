<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowToUserNotices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_notices', function (Blueprint $table) {
            $table->integer('show')->default(1);
            $table->integer('count_show')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('user_notices', function (Blueprint $table) {
            $table->dropColumn(['show']);
            $table->dropColumn(['count_show']);
        });
    }
}
