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
        if (Schema::hasTable('ms_part_specification')) {
            return;
        }

        Schema::create('ms_part_specification', function (Blueprint $table) {
            $table->id();
            $table->string('code', 255)->unique();
            $table->string('name', 255);
            $table->string('notes', 255)->nullable();
            $table->integer('as_batch')->default(0);

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_part_specification');
    }
};
