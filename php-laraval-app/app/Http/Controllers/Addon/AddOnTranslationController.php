<?php

namespace App\Http\Controllers\Addon;

use App\DTOs\Translation\UpdateTranslationRequestDTO;
use App\Enums\TranslationEnum;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateAddonArticleJob;
use App\Models\Addon;
use App\Models\Translation;
use DB;
use Illuminate\Http\RedirectResponse;

class AddOnTranslationController extends Controller
{
    /**
     * Update custom translation.
     */
    public function update(
        Addon $addon,
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
            UpdateAddonArticleJob::dispatchAfterResponse($addon);
        }

        return back()->with('success', __('translation updated successfully'));
    }
}
