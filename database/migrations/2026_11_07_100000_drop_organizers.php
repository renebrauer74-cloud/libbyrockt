<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Libby Rockt! veranstaltet immer selbst – externe Veranstalter gibt es nicht mehr.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizer_id');
        });

        Schema::dropIfExists('organizers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_person', 120)->nullable();
            $table->string('street', 150)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('website', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->after('venue_id')->constrained()->nullOnDelete();
        });
    }
};
