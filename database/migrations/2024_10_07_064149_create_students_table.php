<?php

use App\Models\AcademicClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicClass::class)->nullable()->constrained()->nullOnDelete();
            $table->string('admission_number')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('father_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_scholarship')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
