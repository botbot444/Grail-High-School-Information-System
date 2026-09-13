<?php

namespace App\Console\Commands;

use App\Models\Fee;
use Illuminate\Console\Command;

/**
 * Phase 7 — nightly overdue detection.
 *
 * Marks any fee past its due date that still carries a balance as 'Overdue'.
 * Statuses are otherwise computed by the Fee state machine on payment; this
 * command exists because the passage of time is the one transition no user
 * action triggers.
 */
class FlagOverdueFees extends Command
{
    protected $signature = 'fees:flag-overdue {--dry-run : List what would change without saving}';

    protected $description = 'Flag fees past their due date with an outstanding balance as Overdue';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // scopeOverdue() = past due_date AND status in (Pending, Partially Paid),
        // so anything already Cleared or Overdue is left alone.
        $fees = Fee::overdue()->with('student')->get();

        if ($fees->isEmpty()) {
            $this->info('No fees need flagging.');

            return self::SUCCESS;
        }

        $flagged = 0;

        foreach ($fees as $fee) {
            $computed = $fee->computeStatus();

            // Trust the state machine: only write when it actually says Overdue.
            if ($computed !== 'Overdue') {
                continue;
            }

            $this->line(sprintf(
                '%s  %s  balance %s  due %s',
                str_pad((string) $fee->fee_id, 6),
                str_pad($fee->student?->full_name ?? 'Unknown', 28),
                str_pad(number_format((float) $fee->balance, 2), 12, ' ', STR_PAD_LEFT),
                $fee->due_date?->format('Y-m-d') ?? '—'
            ));

            if (! $dryRun) {
                $fee->audit_reason = 'Automatically flagged overdue by fees:flag-overdue';
                $fee->updateStatus();
            }

            $flagged++;
        }

        $this->newLine();
        $this->info($dryRun
            ? "{$flagged} fee(s) would be flagged as Overdue."
            : "{$flagged} fee(s) flagged as Overdue.");

        return self::SUCCESS;
    }
}
