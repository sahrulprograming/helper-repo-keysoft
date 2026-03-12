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
        if (Schema::hasTable('ms_part_unit')) {
            return;
        }

        Schema::create('ms_part_unit', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('part_id');
            // $table->foreign('part_id')->references('id')->on('ms_part');

            $table->unsignedBigInteger('unit_id');
            // $table->foreign('unit_id')->references('id')->on('ms_unit');

            $table->decimal('conversion', 20, 6);

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_part_unit');
    }
};
