<?php

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admission numbers become the duplicate-detection key, so they must be unique before the index is added
        $duplicates = DB::table('students')
            ->whereNotNull('admission_number')
            ->groupBy('admission_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('admission_number');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Duplicate admission numbers must be fixed before this migration: '.$duplicates->implode(', '));
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unique('admission_number');
        });

        Schema::table('academic_classes', function (Blueprint $table) {
            // Position in the school ladder (Class 1 = 1); promotion moves students to the next one
            $table->unsignedSmallInteger('sort_order')->nullable()->after('name')->index();
        });

        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicClass::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['academic_class_id', 'name']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // A subject taught to a whole class (section_id null) or to one section only
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicClass::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Section::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Explicit short names: MySQL limits identifiers to 64 characters
            $table->unique(['academic_class_id', 'section_id', 'subject_id'], 'subject_assignments_placement_unique');
        });

        // One row per placement; rows are closed (never deleted) when a student moves on, so history is kept
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AcademicSession::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(AcademicClass::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Section::class)->nullable()->constrained()->restrictOnDelete();
            $table->string('roll_number')->nullable();
            $table->string('status')->index();
            $table->date('enrolled_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['academic_session_id', 'academic_class_id', 'section_id'], 'enrollments_placement_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('subject_assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('academic_sessions');

        Schema::table('academic_classes', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['admission_number']);
        });
    }
};
