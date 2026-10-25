<?php

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamPaper;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One examination in a session, e.g. "Mid-term"; locked once marking is finished
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicSession::class)->constrained()->restrictOnDelete();
            $table->string('name');
            $table->date('held_on')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['academic_session_id', 'name']);
        });

        // A subject sat by one class in an exam, with its maximum and pass marks
        Schema::create('exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Exam::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AcademicClass::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Subject::class)->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('total_marks');
            $table->unsignedSmallInteger('pass_marks');
            $table->timestamps();

            $table->unique(['exam_id', 'academic_class_id', 'subject_id']);
        });

        // One student's mark on one paper; tied to the enrollment like attendance, so transfers keep correct history
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamPaper::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Enrollment::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('obtained_marks')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->string('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_paper_id', 'enrollment_id']);
        });

        // Published exam results land in the existing results table, which the public results page reads
        Schema::table('examination_results', function (Blueprint $table) {
            $table->foreignIdFor(Exam::class)->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('examination_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_id');
        });

        Schema::dropIfExists('exam_marks');
        Schema::dropIfExists('exam_papers');
        Schema::dropIfExists('exams');
    }
};
