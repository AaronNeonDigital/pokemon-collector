<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->index();
            $table->string('super_type')->index()->nullable();
            $table->integer('hp')->index();
            $table->string('evolves_from')->index()->nullable();
            $table->string('evolves_to')->index()->nullable();
            $table->integer('converted_retreat_cost')->index()->nullable();
            $table->string('set_number')->index();
            $table->string('artist')->index()->nullable();
            $table->string('rarity')->index()->nullable();
            $table->string('flavor_text')->index()->nullable();
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
        Schema::dropIfExists('pokemon');
    }
};
