<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallPluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('install_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('');
            $table->string('alias')->default('');
            $table->string('description')->default('');
            $table->string('keywords')->default('');
            $table->string('providers')->default('');
            $table->unsignedTinyInteger('status')->default(0);
            $table->string('version')->default('');
            $table->string('logo')->default('');
            $table->json('composer')->nullable();
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
        Schema::dropIfExists('install_plugins');
    }
}
