<?php

namespace App\Http\Controllers\Category;

use App\DTOs\Translation\UpdateTranslationRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Translation;
use DB;
use Illuminate\Http\RedirectResponse;

class CategoryTranslationController extends Controller
{
    /**
     * Update custom translation.
     */
    public function update(
        UpdateTranslationRequestDTO $request,
    ): RedirectResponse {
        DB::transaction(function () use ($request) {
            foreach ($request->translations as $translation) {
                $instance = Translation::find($translation['id']);
                if ($instance) {
                    $instance->update([
                        $request->language => $translation['value'],
                    ]);
                }
            }
        });

        return back()->with('success', __('translation updated successfully'));
    }
}
