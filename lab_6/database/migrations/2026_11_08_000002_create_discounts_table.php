<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('percentage');
            $table->decimal('value', 10, 2);
            $table->dateTime('active_from')->nullable();
            $table->dateTime('active_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'active_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
