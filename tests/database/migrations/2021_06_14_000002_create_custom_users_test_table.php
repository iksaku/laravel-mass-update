<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomUsersTestTable extends Migration {
    public function up(): void
    {
        Schema::create('custom_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('custom_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::drop('custom_users');
    }
}
