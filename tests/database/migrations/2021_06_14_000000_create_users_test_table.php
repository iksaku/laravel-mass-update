<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->boolean('can_code')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
};
