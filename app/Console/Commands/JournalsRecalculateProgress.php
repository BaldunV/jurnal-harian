<?php

namespace App\Console\Commands;

use App\Models\Journal;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('journals:recalculate-progress {--dry-run : Show what would be updated without making changes}')]
#[Description('Recalculate completed_count and is_fully_completed for all journals based on qualifiedHabits()')]
class JournalsRecalculateProgress extends Command
{
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $totalScanned = 0;
        $totalUpdated = 0;
        $totalUnchanged = 0;
        $totalErrors = 0;

        $journals = Journal::query()
            ->select(['id', 'user_id', 'date', 'bangun_pagi', 'bangun_pagi_time', 'beribadah', 'ibadah_details', 'berolahraga', 'makan_sehat', 'gemar_belajar', 'bermasyarakat', 'tidur_cepat', 'tidur_note', 'completed_count', 'is_fully_completed', 'is_submitted'])
            ->cursor();

        $this->output->progressStart();

        foreach ($journals as $journal) {
            $totalScanned++;

            $qualified = $journal->qualifiedHabits();
            $newCompletedCount = count(array_filter($qualified));
            $newIsFullyCompleted = $newCompletedCount === 7;

            $needsUpdate = $journal->completed_count !== $newCompletedCount
                || $journal->is_fully_completed !== $newIsFullyCompleted;

            if ($needsUpdate) {
                if (! $dryRun) {
                    try {
                        DB::transaction(function () use ($journal, $newCompletedCount, $newIsFullyCompleted) {
                            $journal->updateQuietly([
                                'completed_count' => $newCompletedCount,
                                'is_fully_completed' => $newIsFullyCompleted,
                            ]);
                        });
                    } catch (\Throwable $e) {
                        $totalErrors++;
                        $this->error("Error updating journal {$journal->id}: ".$e->getMessage());

                        continue;
                    }
                }
                $totalUpdated++;
                $this->line("<fg=green>✓</> Journal {$journal->id} ({$journal->date}): {$journal->completed_count}/7 → {$newCompletedCount}/7, is_fully_completed: ".($journal->is_fully_completed ? 'true' : 'false').' → '.($newIsFullyCompleted ? 'true' : 'false'));
            } else {
                $totalUnchanged++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine(2);

        $this->info('📊 Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Scanned', $totalScanned],
                ['Updated', $totalUpdated],
                ['Unchanged', $totalUnchanged],
                ['Errors', $totalErrors],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return $totalErrors > 0 ? 1 : 0;
    }
}
