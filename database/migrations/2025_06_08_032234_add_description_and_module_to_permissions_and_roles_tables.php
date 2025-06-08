<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar colunas description e module à tabela permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable()->after('guard_name');
            $table->text('description')->nullable()->after('module');
        });

        // Adicionar coluna description à tabela roles
        Schema::table('roles', function (Blueprint $table) {
            $table->text('description')->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['module', 'description']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
