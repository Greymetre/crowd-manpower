<?php

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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id(); // shown as "Sr No"
            $table->string('name');
            $table->string('id_no', 50)->nullable()->index();
            $table->string('working', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('village', 100)->nullable();
            $table->string('tehsil', 100)->nullable();
            $table->string('district', 100)->nullable()->index();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->date('dob')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('gender', 10)->nullable()->index();
            $table->string('education', 100)->nullable();
            $table->date('applied_date')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
