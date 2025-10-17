<?php

namespace App\Http\Controllers\Product;

use App\DTOs\Translation\UpdateTranslationRequestDTO;
use App\Enums\TranslationEnum;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateProductArticleJob;
use App\Models\Product;
use App\Models\Translation;
use DB;
use Illuminate\Http\RedirectResponse;

class ProductTranslationController extends Controller
{
    /**
     * Update custom translation.
     */
    public function update(
        Product $product,
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
            UpdateProductArticleJob::dispatchAfterResponse($product);
        }

        return back()->with('success', __('translation updated successfully'));
    }
}
