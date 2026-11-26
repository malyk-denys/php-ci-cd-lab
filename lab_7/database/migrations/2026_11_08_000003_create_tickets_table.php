<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('seat_number', 20);
            $table->decimal('price', 10, 2);
            $table->string('customer_email');
            $table->string('status')->default('available');
            $table->dateTime('sold_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'seat_number']);
            $table->index(['status', 'price']);
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
