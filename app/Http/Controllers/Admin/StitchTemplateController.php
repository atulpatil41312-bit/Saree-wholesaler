<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneratedTemplateDraft;
use App\Services\StitchTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class StitchTemplateController extends Controller
{
    public function index(): View
    {
        $drafts = GeneratedTemplateDraft::query()->latest()->paginate(12);
        $stitchConfigured = filled(config('services.stitch.key')) && filled(config('services.stitch.project_id'));

        return view('admin.stitch.index', compact('drafts', 'stitchConfigured'));
    }

    public function store(Request $request, StitchTemplateService $stitch): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'prompt' => ['required', 'string', 'min:20', 'max:5000'],
            'device_type' => ['required', Rule::in(['DESKTOP', 'MOBILE', 'TABLET', 'AGNOSTIC'])],
        ]);

        $draft = GeneratedTemplateDraft::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'stitch_project_id' => (string) (config('services.stitch.project_id') ?: 'not-configured'),
            'status' => 'pending',
        ]);

        try {
            $stitch->generate($draft);
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
            $apiKey = config('services.stitch.key');

            if (is_string($apiKey) && $apiKey !== '') {
                $message = str_replace($apiKey, '[redacted]', $message);
            }

            $draft->update([
                'status' => 'failed',
                'error_message' => Str::limit($message, 1200),
            ]);

            return redirect()->route('admin.stitch.index')->withErrors([
                'stitch' => 'The draft could not be generated. Review the failed draft for details.',
            ]);
        }

        return redirect()->route('admin.stitch.index')->with('status', 'Stitch generated a new private design draft.');
    }

    public function show(GeneratedTemplateDraft $draft): View
    {
        $html = null;

        if ($draft->html_path && Storage::disk('local')->exists($draft->html_path)) {
            $html = Storage::disk('local')->get($draft->html_path);
        }

        return view('admin.stitch.show', compact('draft', 'html'));
    }

    public function image(GeneratedTemplateDraft $draft): BinaryFileResponse
    {
        abort_unless($draft->image_path && Storage::disk('local')->exists($draft->image_path), 404);

        return response()->file(Storage::disk('local')->path($draft->image_path), [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function downloadHtml(GeneratedTemplateDraft $draft): BinaryFileResponse
    {
        abort_unless($draft->html_path && Storage::disk('local')->exists($draft->html_path), 404);

        return response()->download(
            Storage::disk('local')->path($draft->html_path),
            (Str::slug($draft->title) ?: 'stitch-draft').'.html',
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}
