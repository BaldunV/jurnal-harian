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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            // 7 Kebiasaan Baik
            $table->boolean('bangun_pagi')->default(false);
            
            $table->boolean('beribadah')->default(false);
            $table->json('ibadah_details')->nullable();
            
            $table->boolean('berolahraga')->default(false);
            $table->string('olahraga_note')->nullable();
            
            $table->boolean('makan_sehat')->default(false);
            $table->string('makan_note')->nullable();
            
            $table->boolean('gemar_belajar')->default(false);
            $table->string('belajar_note')->nullable();
            
            $table->boolean('bermasyarakat')->default(false);
            $table->string('masyarakat_note')->nullable();
            
            $table->boolean('tidur_cepat')->default(false);
            $table->string('tidur_note')->nullable();
            
            $table->integer('completed_count')->default(0);
            $table->boolean('is_fully_completed')->default(false);
            
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
