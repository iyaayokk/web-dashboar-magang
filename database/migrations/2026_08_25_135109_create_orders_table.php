<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('id_order')->unique(); // Unique Key untuk Upsert/Merge Data
            $table->string('no_agenda')->nullable();
            $table->string('pemohon')->nullable();      // Dibuat nullable
            $table->string('status')->nullable();       // Dibuat nullable
            $table->string('sub_status')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('last_update')->nullable();
            $table->integer('total_durasi_day')->default(0);
            $table->string('paket')->nullable();
            $table->integer('daya')->default(0);
            $table->string('brand')->nullable();        
            $table->string('charger')->nullable();   
            $table->string('type')->nullable();        
            $table->string('tipe_saluran')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('distribusi')->nullable();
            $table->string('up3')->nullable();
            $table->string('ulp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};