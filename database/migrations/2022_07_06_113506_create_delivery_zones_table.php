<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_zone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("delivery_id");
            $table->unsignedBigInteger("zone_id");
            $table->enum("type",['wholesale','retail'])->default('wholesale');
            $table->double("cost",'8','2')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_zones');
    }
}
