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
        if (Schema::hasTable('ms_unit')) {
            return;
        }

        Schema::create('ms_unit', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->string('notes', 255)->nullable();

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_unit');
    }
};
