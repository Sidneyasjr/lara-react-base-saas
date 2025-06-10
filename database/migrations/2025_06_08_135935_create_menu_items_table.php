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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('ID do tenant - nullable para menus globais');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID do menu pai para submenus');
            $table->string('title')->comment('Título do menu');
            $table->string('route_name')->nullable()->comment('Nome da rota');
            $table->string('icon')->nullable()->comment('Ícone do menu');
            $table->string('permission_required')->nullable()->comment('Permissão necessária para acessar');
            $table->integer('order_index')->default(0)->comment('Ordem de exibição');
            $table->boolean('is_active')->default(true)->comment('Se o menu está ativo');
            $table->string('module')->comment('Módulo do menu (dashboard, users, settings, etc.)');
            $table->timestamps();

            // Índices
            $table->index(['tenant_id', 'parent_id', 'is_active']);
            $table->index(['module', 'order_index']);
            $table->index('permission_required');

            // Foreign keys (se necessário futuramente)
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
