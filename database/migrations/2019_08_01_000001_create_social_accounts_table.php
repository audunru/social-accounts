<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialAccountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $tableName = config('social-accounts.table_names.social_accounts');
        $userTableName = config('social-accounts.table_names.users');
        $foreignKey = config('social-accounts.column_names.foreign_key');
        $primaryKey = config('social-accounts.column_names.primary_key');

        Schema::create($tableName, function (Blueprint $table) use ($foreignKey, $primaryKey, $userTableName) {
            $table->bigIncrements('id');
            $table->string('provider');
            $table->string('provider_user_id');
            // The primary key on the User model is usually either an unsigned integer og unsigned bigInteger.
            // By getting the column type and signedness, we can create a column that can be referenced as a foreign key.
            $type = DB::getDoctrineColumn($userTableName, $primaryKey)->getType()->getName();
            $unsigned = DB::getDoctrineColumn($userTableName, $primaryKey)->getUnsigned();
            $table->addColumn($type, $foreignKey, compact('unsigned'));
            // Delete entries in this table if user is deleted from the users table
            $table->foreign($foreignKey)->references($primaryKey)->on($userTableName)->onDelete('cascade');
            // Two different users can not share the same social login (ie. a Google account can only belong to one user)
            $table->unique(['provider', 'provider_user_id']);
            // User can only social account per provider (ie. two Google accounts for one user is not allowed)
            $table->unique(['provider', $foreignKey]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('social_accounts');
    }
}
