<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->text('thumbnail_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createCategory();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

    private function createCategory(): void
    {
        foreach ($this->listOfCategories() as $item) {
            $categoryId = DB::table('categories')->insertGetId([
                'thumbnail_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('translations')->insert([
                'translationable_type' => 'App\\Models\\Category',
                'translationable_id' => $categoryId,
                'key' => $item['key'],
                'en_US' => $item['en_US'],
                'sv_SE' => $item['sv_SE'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function listOfCategories(): array
    {
        return [
            [
                'key' => 'name',
                'sv_SE' => 'Rengöring',
                'en_US' => 'Cleaning',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Övrigt rengöring',
                'en_US' => 'Miscellaneous Cleaning',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Tvätt',
                'en_US' => 'Laundry',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Butik Produkter',
                'en_US' => 'Store Products',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Skjortor & Blusar',
                'en_US' => 'Shirts & Blouses',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Kemtvätt & Övrigt',
                'en_US' => 'Dry Cleaning & Miscellaneous',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Skrädderi',
                'en_US' => 'Tailoring',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Skomakeri',
                'en_US' => 'Shoe Repair',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Grov Kem',
                'en_US' => 'Heavy Duty Dry Cleaning',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Diverse',
                'en_US' => 'Miscellaneous',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Mangling',
                'en_US' => 'Mangling',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Mattor',
                'en_US' => 'Rugs & Carpets',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Rena Plagg',
                'en_US' => 'Clean Garments',
            ],
            [
                'key' => 'name',
                'sv_SE' => 'Restaurangtvätt',
                'en_US' => 'Restaurant Laundry',
            ],
        ];
    }
};
