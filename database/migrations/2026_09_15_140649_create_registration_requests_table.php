<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A parent's self-service claim of "I'm a new family, here's my child" —
 * always a new-admission request, never a claim against an already-enrolled
 * student (linking a parent to an existing student stays an admin-only
 * manual action). Nothing here is real until an admin approves it: no
 * `users` row, no `students` row exists yet. Mirrors the payment_submissions
 * review-queue shape (status/reviewed_by/reviewed_at/review_notes), with
 * created_parent_user_id/created_student_id filled in only on approval.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id('registration_request_id');

            $table->string('parent_first_name');
            $table->string('parent_last_name');
            $table->string('parent_email');
            $table->string('parent_password'); // hashed at submission time — the parent's own choice, never regenerated
            $table->string('parent_phone')->nullable();
            $table->string('parent_address')->nullable();
            $table->string('parent_occupation')->nullable();
            $table->string('parent_national_id')->nullable();

            $table->string('child_first_name');
            $table->string('child_last_name');
            $table->date('child_date_of_birth');
            $table->enum('child_gender', ['Male', 'Female']);
            $table->string('child_email');

            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Set only once approved.
            $table->foreignId('created_parent_user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('created_student_id')->nullable()->constrained('students', 'student_id')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};
