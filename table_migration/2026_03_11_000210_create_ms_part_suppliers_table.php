<?php

use App\Traits\BaseModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use BaseModel;

    public function up(): void
    {
        if (Schema::hasTable('ms_part_supplier')) {
            return;
        }

        Schema::create('ms_part_supplier', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('part_id');
            // $table->foreign('part_id')->references('id')->on('ms_part');

            $table->unsignedBigInteger('supplier_id');
            // TODO: aktifkan setelah ms_supplier tersedia
            // $table->foreign('supplier_id')->references('id')->on('ms_supplier');

            $table->integer('delivery_time');
            $table->unsignedBigInteger('delivery_unit_id');
            // $table->foreign('delivery_unit_id')->references('id')->on('ms_unit');

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_part_supplier');
    }
};
