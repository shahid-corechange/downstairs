<?php

namespace App\Http\Controllers\Service;

use App\DTOs\Translation\UpdateTranslationRequestDTO;
use App\Enums\TranslationEnum;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateServiceArticleJob;
use App\Models\Service;
use App\Models\Translation;
use DB;
use Illuminate\Http\RedirectResponse;

class ServiceTranslationController extends Controller
{
    /**
     * Update custom translation.
     */
    public function update(
        Service $service,
        UpdateTranslationRequestDTO $request,
    ): RedirectResponse {

        DB::transaction(function () use ($request) {
            foreach ($request->translations as $translation) {
                $instance = Translation::find($translation['id']);
                $instance->update([
                    $request->language => $translation['value'],
                ]);
            }
        });

        if ($request->language === TranslationEnum::Swedish()) {
            UpdateServiceArticleJob::dispatchAfterResponse($service);
        }

        return back()->with('success', __('translation updated successfully'));
    }
}
