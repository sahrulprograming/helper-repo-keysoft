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
        if (Schema::hasTable('ms_part_serial')) {
            return;
        }

        Schema::create('ms_part_serial', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('part_id');
            // $table->foreign('part_id')->references('id')->on('ms_part');

            $table->string('serial_no', 255);

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_part_serial');
    }
};
