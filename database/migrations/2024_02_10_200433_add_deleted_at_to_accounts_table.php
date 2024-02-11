<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}

