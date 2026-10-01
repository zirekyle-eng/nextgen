<?php

namespace App\Console\Commands;

use App\Models\LessonFile;
use App\Services\CurriculumFileTextExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractLessonFileContent extends Command
{
    protected $signature = 'lesson-files:extract-content {--all : Reprocess all lesson files, even those that already have content_text}';
    protected $description = 'Extract text from lesson files and store it in content_text for AI tutor use.';

    private CurriculumFileTextExtractor $textExtractor;

    public function __construct(CurriculumFileTextExtractor $textExtractor)
    {
        parent::__construct();
        $this->textExtractor = $textExtractor;
    }

    public function handle()
    {
        $query = LessonFile::query();
        if (!$this->option('all')) {
            $query->where(function ($q) {
                $q->whereNull('content_text')
                  ->orWhere('content_text', '');
            });
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No lesson files need content extraction. Use --all to reprocess all files.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(50, function ($files) use (&$bar, &$processed, &$skipped, &$failed) {
            foreach ($files as $file) {
                $absolutePath = $this->resolvePublicFilePath($file->file_path);
                if ($absolutePath === null || !is_file($absolutePath)) {
                    $this->warn("\nFile not found for lesson_file id={$file->id}, path={$file->file_path}");
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
                $content = trim($this->textExtractor->extract($absolutePath, $extension));
                if ($content === '') {
                    $skipped++;
                }

                $file->content_text = $content;
                $file->save();

                $processed++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Processed: {$processed}");
        $this->info("Skipped (empty extraction): {$skipped}");
        $this->info("Failed (missing file): {$failed}");

        return 0;
    }

    private function resolvePublicFilePath(string $relativePath): ?string
    {
        $relativePath = trim(str_replace('\\', '/', trim($relativePath)));
        if ($relativePath === '') {
            return null;
        }

        $candidates = [$relativePath];
        if (stripos($relativePath, 'public/') === 0) {
            $candidates[] = substr($relativePath, 7);
        }
        if (stripos($relativePath, 'storage/') === 0) {
            $candidates[] = substr($relativePath, 8);
        }

        foreach ($candidates as $candidate) {
            $candidate = ltrim($candidate, '/');
            if ($candidate === '') {
                continue;
            }

            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }

            $publicStoragePath = public_path('storage/' . $candidate);
            if (is_file($publicStoragePath)) {
                return $publicStoragePath;
            }

            $storageAppPublicPath = storage_path('app/public/' . $candidate);
            if (is_file($storageAppPublicPath)) {
                return $storageAppPublicPath;
            }

            $publicPath = public_path($candidate);
            if (is_file($publicPath)) {
                return $publicPath;
            }
        }

        return null;
    }
}
