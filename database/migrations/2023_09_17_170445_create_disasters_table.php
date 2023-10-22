<?php

use App\Models\Disaster;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disasters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district');
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('type');
            $table->longText('moreinfo');
            $table->string('lng');
            $table->string('ltd');
            $table->enum('status', array_keys(Disaster::$status))->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disasters');
    }
};
