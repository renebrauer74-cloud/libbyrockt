<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datenblatt der Location: frei zusammengestellte Angaben wie Bühnengröße oder Strom.
     */
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->json('details')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
