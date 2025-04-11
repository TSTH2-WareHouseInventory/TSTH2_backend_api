<?php

use App\Models\JenisBarang;
use App\Models\Satuan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenisbarang_id')->nullable()->constrained('jenis_barangs')->onDelete('cascade');
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->onDelete('cascade');
            $table->foreignId('barangcategory_id')->nullable()->constrained('barang_categories')->onDelete('cascade');
            $table->string('barang_kode')->unique();
            $table->string('barang_nama');
            $table->string('barang_slug')->unique();
            $table->decimal('barang_harga', 10, 2)->default(0);
            $table->string('barang_gambar')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
