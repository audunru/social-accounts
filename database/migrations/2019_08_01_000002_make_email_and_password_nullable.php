<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeEmailAndPasswordNullable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (true !== config('social-accounts.automatically_create_users')) {
            return;
        }

        $tableName = config('social-accounts.table_names.users');

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('email')->nullable(true)->change();
            $table->string('password')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $tableName = config('social-accounts.table_names.users');

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
}
