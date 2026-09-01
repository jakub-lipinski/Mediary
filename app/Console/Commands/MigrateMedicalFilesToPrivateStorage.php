<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('medical-files:migrate-private')]
#[Description('Move existing medical documents from public to private storage')]
class MigrateMedicalFilesToPrivateStorage extends Command
{
    public function handle(): int
    {
        $migrated = 0;
        $missing = 0;

        File::query()
            ->select(['id', 'path'])
            ->chunkById(100, function ($files) use (&$migrated, &$missing): void {
                foreach ($files as $file) {
                    if (Storage::disk('medical')->exists($file->path)) {
                        Storage::disk('public')->delete($file->path);

                        continue;
                    }

                    $stream = Storage::disk('public')->readStream($file->path);

                    if ($stream === null) {
                        $missing++;
                        $this->warn('Missing public file for record '.$file->id.'.');

                        continue;
                    }

                    try {
                        Storage::disk('medical')->writeStream($file->path, $stream);
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }

                    Storage::disk('public')->delete($file->path);
                    $migrated++;
                }
            });

        $this->info("Migrated {$migrated} medical file(s); {$missing} missing source file(s).");

        return $missing === 0 ? self::SUCCESS : self::FAILURE;
    }
}
