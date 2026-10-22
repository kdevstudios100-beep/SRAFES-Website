<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_results', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['academic_class_id']);

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('academic_class_id')->references('id')->on('academic_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('examination_results', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['academic_class_id']);

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('academic_class_id')->references('id')->on('academic_classes');
        });
    }
};
