<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ms_part', function (Blueprint $table) {
            $table->foreign('inventory_type_id')->references('id')->on('ms_inventory_type');
            $table->foreign('category_id')->references('id')->on('ms_part_category');
            $table->foreign('specification_id')->references('id')->on('ms_part_specification');
            $table->foreign('variant_id')->references('id')->on('ms_part_variant');
            $table->foreign('deferred_warehouse_id')->references('id')->on('ms_warehouse');
            $table->foreign('volume_unit_id')->references('id')->on('ms_unit');
            $table->foreign('weight_unit_id')->references('id')->on('ms_unit');
        });

        Schema::table('ms_part_unit', function (Blueprint $table) {
            $table->foreign('part_id')->references('id')->on('ms_part');
            $table->foreign('unit_id')->references('id')->on('ms_unit');
        });

        Schema::table('ms_part_supplier', function (Blueprint $table) {
            $table->foreign('part_id')->references('id')->on('ms_part');
            $table->foreign('supplier_id')->references('id')->on('ms_supplier');
            $table->foreign('delivery_unit_id')->references('id')->on('ms_unit');
        });

        Schema::table('ms_part_serial', function (Blueprint $table) {
            $table->foreign('part_id')->references('id')->on('ms_part');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('part_id')->references('id')->on('ms_part');
            $table->foreign('warehouse_id')->references('id')->on('ms_warehouse');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
            $table->dropForeign(['warehouse_id']);
        });

        Schema::table('ms_part_supplier', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['delivery_unit_id']);
        });

        Schema::table('ms_part_serial', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
        });

        Schema::table('ms_part_unit', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
            $table->dropForeign(['unit_id']);
        });

        Schema::table('ms_part', function (Blueprint $table) {
            $table->dropForeign(['inventory_type_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['specification_id']);
            $table->dropForeign(['variant_id']);
            $table->dropForeign(['deferred_warehouse_id']);
            $table->dropForeign(['volume_unit_id']);
            $table->dropForeign(['weight_unit_id']);
        });
    }
};
