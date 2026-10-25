<?php

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A teacher's job in one session: class teacher of a class/section (subject_id null) or teacher of one subject there
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AcademicSession::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(AcademicClass::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Section::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Subject::class)->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            // Explicit short names: MySQL limits identifiers to 64 characters
            $table->index(['academic_session_id', 'academic_class_id', 'section_id'], 'teacher_assignments_placement_index');
            $table->index(['user_id', 'academic_session_id'], 'teacher_assignments_user_session_index');
        });

        // One mark per student placement per school day; tied to the enrollment so transfers keep correct history
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Enrollment::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status');
            $table->string('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['enrollment_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('teacher_assignments');
    }
};
