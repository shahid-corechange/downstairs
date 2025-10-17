<?php

use App\Models\Product;
use App\Models\Translation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('addon_id')->nullable();
        });

        $this->migrateProductsToAddOns();
        $this->addCategoryToProducts();

        Translation::where('translationable_type', Product::class)
            ->whereIn('translationable_id', [8, 9])
            ->forceDelete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('addon_id');
        });
    }

    private function migrateProductsToAddOns(): void
    {
        $products = Product::where('category_id', 1)->get();

        DB::transaction(function () use ($products) {
            foreach ($products as $product) {
                // Create addon using DB statement
                $addonId = DB::table('addons')->insertGetId([
                    'fortnox_article_id' => $product->fortnox_article_id,
                    'unit' => $product->unit,
                    'price' => $product->price,
                    'credit_price' => $product->credit_price,
                    'vat_group' => $product->vat_group,
                    'has_rut' => $product->has_rut,
                    'thumbnail_image' => $product->thumbnail_image,
                    'color' => $product->color,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // add category to addon using DB statement
                DB::table('categoryables')->insert([
                    'category_id' => config('downstairs.categories.cleaning.id'),
                    'categoryable_type' => 'App\\Models\\Addon',
                    'categoryable_id' => $addonId,
                ]);

                // update by db statement
                DB::statement("UPDATE products SET addon_id = $addonId WHERE id = $product->id");

                foreach ($product->translations as $translation) {
                    // add translations using DB statement
                    DB::table('translations')->insert([
                        'translationable_type' => 'App\\Models\\Addon',
                        'translationable_id' => $addonId,
                        'key' => $translation->key,
                        'en_US' => $translation->en_US,
                        'nn_NO' => $translation->nn_NO,
                        'sv_SE' => $translation->sv_SE,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    //delete translation
                    $translation->forceDelete();
                }

                foreach ($product->tasks as $task) {
                    // add tasks using DB statement
                    $customTaskId = DB::table('custom_tasks')->insertGetId([
                        'taskable_type' => 'App\\Models\\Addon',
                        'taskable_id' => $addonId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($task->translations as $translation) {
                        DB::table('translations')->insert([
                            'translationable_type' => 'App\\Models\\Task',
                            'translationable_id' => $customTaskId,
                            'key' => $translation->key,
                            'en_US' => $translation->en_US,
                            'nn_NO' => $translation->nn_NO,
                            'sv_SE' => $translation->sv_SE,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        //delete translation
                        $translation->forceDelete();
                    }

                    //delete task
                    $task->delete();
                }

                // delete all meta records associated with the model
                $product->purgeMeta();

                // attach services using DB statement
                DB::table('service_addons')->insert([
                    'addon_id' => $addonId,
                    'service_id' => $product->service_id,
                ]);
            }
        });
    }

    private function addCategoryToProducts(): void
    {
        // add category to products from default miscellaneous cleaning
        $products = Product::where('category_id', 2)->get();

        DB::transaction(function () use ($products) {
            $categoryId = config('downstairs.categories.miscellaneous.id');

            // add category to products using DB statement
            $productIds = $products->pluck('id')->toArray();

            foreach ($productIds as $productId) {
                DB::table('categoryables')->insertOrIgnore([
                    'category_id' => $categoryId,
                    'categoryable_type' => 'App\\Models\\Product',
                    'categoryable_id' => $productId,
                ]);
            }

            foreach ($products as $product) {
                // delete all meta records associated with the model
                $product->purgeMeta();
            }
        });
    }
};
