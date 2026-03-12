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
        if (Schema::hasTable('ms_part')) {
            return;
        }

        Schema::create('ms_part', function (Blueprint $table) {
            $table->id();
            $table->string('code', 255)->unique();
            $table->string('name', 255);
            $table->string('other_code', 255)->nullable();
            $table->boolean('is_stock');

            $table->unsignedBigInteger('inventory_type_id')->nullable();
            // $table->foreign('inventory_type_id')->references('id')->on('ms_inventory_type');

            $table->unsignedBigInteger('category_id')->nullable();
            // $table->foreign('category_id')->references('id')->on('ms_part_category');

            $table->unsignedBigInteger('specification_id')->nullable();
            // $table->foreign('specification_id')->references('id')->on('ms_part_specification');

            $table->unsignedBigInteger('variant_id')->nullable();
            // $table->foreign('variant_id')->references('id')->on('ms_part_variant');

            $table->string('pricing', 50)->nullable();
            $table->decimal('standard_cost_freight_by_volume', 20, 6)->nullable();
            $table->decimal('standard_cost_freight_by_weight', 20, 6)->nullable();

            $table->unsignedBigInteger('deferred_warehouse_id')->nullable();
            // $table->foreign('deferred_warehouse_id')->references('id')->on('ms_warehouse');

            $table->decimal('minimum_stock_buffer', 20, 6)->nullable();
            $table->boolean('with_serial_no')->nullable();
            $table->decimal('excess_tolerance', 20, 6)->nullable();
            $table->decimal('lack_tolerance', 20, 6)->nullable();
            $table->string('type_of_guarantee', 50)->nullable();
            $table->integer('limit_days_guarantee')->nullable();
            $table->string('notes', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->decimal('maximum_stock_buffer', 20, 6)->nullable();
            $table->decimal('vat', 20, 6);
            $table->decimal('gross_weight', 20, 6)->nullable();
            $table->decimal('nett_weight', 20, 6)->nullable();
            $table->decimal('gross_volume', 20, 6)->nullable();
            $table->decimal('nett_volume', 20, 6)->nullable();
            $table->decimal('purchase_price', 20, 6)->nullable();
            $table->decimal('sale_price', 20, 6)->nullable();

            $table->string('attachment', 255)->nullable();
            $table->string('hs_code', 255)->nullable();
            $table->unsignedBigInteger('volume_unit_id')->nullable();
            $table->unsignedBigInteger('weight_unit_id')->nullable();
            $table->boolean('is_auto')->default(true);

            $this->withActiveAndJson($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_part');
    }
};
