<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Fee;
use Carbon\Carbon;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $fee   = Fee::first();

        if (!$admin || !$fee) {
            return; // Nothing to seed against.
        }

        AuditLog::create([
            'user_id'        => $admin->id,
            'auditable_type' => Fee::class,
            'auditable_id'   => $fee->fee_id,
            'action'         => 'created',
            'new_values'     => [
                'student_id'    => $fee->student_id,
                'amount_due'    => '2500.00',
                'status'        => 'Pending',
                'term'          => 'Term 1',
                'academic_year' => now()->year,
            ],
            'reason'         => 'Seeded initial fee',
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'Seeder',
            'created_at'     => Carbon::now()->subDays(5),
        ]);

        AuditLog::create([
            'user_id'        => $admin->id,
            'auditable_type' => Fee::class,
            'auditable_id'   => $fee->fee_id,
            'action'         => 'updated',
            'old_values'     => ['amount_due' => '2500.00'],
            'new_values'     => ['amount_due' => '2700.00'],
            'reason'         => 'Added Examination fee',
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'Seeder',
            'created_at'     => Carbon::now()->subDays(2),
        ]);
    }
}
