<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use Illuminate\Console\Command;

class BackfillVerificationCodes extends Command
{
    protected $signature = 'certificates:backfill-codes';
    protected $description = 'Generate verification codes for existing certificates that lack one';

    public function handle(): int
    {
        $certificates = Certificate::whereNull('verification_code')->get();

        if ($certificates->isEmpty()) {
            $this->info('All certificates already have verification codes.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($certificates->count());

        foreach ($certificates as $certificate) {
            $certificate->verification_code = Certificate::generateVerificationCode();
            $certificate->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Backfilled {$certificates->count()} certificate(s) with verification codes.");

        return self::SUCCESS;
    }
}
