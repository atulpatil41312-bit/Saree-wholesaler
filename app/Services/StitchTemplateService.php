<?php

namespace App\Services;

use App\Models\GeneratedTemplateDraft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class StitchTemplateService
{
    public function generate(GeneratedTemplateDraft $draft): void
    {
        $apiKey = config('services.stitch.key');
        $projectId = config('services.stitch.project_id');

        if (! is_string($apiKey) || trim($apiKey) === '' || ! is_string($projectId) || trim($projectId) === '') {
            throw new RuntimeException('Stitch is not configured. Add STITCH_API_KEY and STITCH_PROJECT_ID to the server environment.');
        }

        $relativeDirectory = 'stitch-drafts/'.$draft->id;
        $outputDirectory = Storage::disk('local')->path($relativeDirectory);
        Storage::disk('local')->makeDirectory($relativeDirectory);

        $process = new Process([
            (string) config('services.stitch.node_binary', 'node'),
            base_path('scripts/stitch-generate.mjs'),
            '--project-id',
            $projectId,
            '--prompt',
            $draft->prompt,
            '--device',
            strtoupper($draft->device_type),
            '--output-dir',
            $outputDirectory,
        ], base_path(), [
            'STITCH_API_KEY' => $apiKey,
            'STITCH_PROJECT_ID' => $projectId,
        ], null, 300);

        $process->run();

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput());

            if ($message === '') {
                $message = trim($process->getOutput());
            }

            throw new RuntimeException(Str::limit($message !== '' ? $message : 'The Stitch generation process failed.', 1200));
        }

        $result = json_decode(trim($process->getOutput()), true);

        if (! is_array($result) || ! is_string($result['screenId'] ?? null)) {
            throw new RuntimeException('Stitch returned an incomplete generation response.');
        }

        $htmlPath = $relativeDirectory.'/screen.html';
        $imagePath = $relativeDirectory.'/screen.png';

        if (! Storage::disk('local')->exists($htmlPath)) {
            throw new RuntimeException('Stitch did not return downloadable HTML for this draft.');
        }

        $draft->update([
            'screen_id' => $result['screenId'],
            'status' => 'ready',
            'html_path' => $htmlPath,
            'image_path' => Storage::disk('local')->exists($imagePath) ? $imagePath : null,
            'error_message' => null,
        ]);
    }
}
