<?php

use App\Models\AcademicClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained();
            $table->foreignIdFor(AcademicClass::class)->nullable()->constrained();
            $table->string('year')->nullable();
            $table->string('detail')->nullable();
            $table->integer('total_marks')->nullable();
            $table->integer('obtained_marks')->nullable();
            $table->string('status')->nullable();
            $table->string('grade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_results');
    }
};
