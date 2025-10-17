<?php

namespace Database\Seeders;

use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\TranslationEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getNames() as $value) {
            $service = Service::create([
                'membership_type' => $value['type'],
                'type' => ServiceTypeEnum::Cleaning(),
                'price' => 122.4,
                'has_rut' => $value['has_rut'],
                'vat_group' => VatNumbersEnum::TwentyFive(),
                'thumbnail_image' => 'https://storagestagingdownstairs.blob.core.windows.net/images/'.
                    $value['thumbnail_image'],
            ]);

            $service->translations()->createMany([
                [
                    'key' => 'name',
                    ...$value['name'],
                ],
                [
                    'key' => 'description',
                    'en_US' => fake()->paragraph,
                    'nn_NO' => fake()->paragraph,
                    'sv_SE' => fake()->paragraph,
                ],
            ]);

            $task = $service->tasks()->create([]);

            $task->setName($value['task_sv_se'], TranslationEnum::Swedish());
            $task->setDescription($value['task_desc_sv_se'], TranslationEnum::Swedish());
            $task->setName($value['task_en_us'], TranslationEnum::English());
            $task->setDescription($value['task_desc_en_us'], TranslationEnum::English());
        }
    }

    private function getNames(): array
    {
        return [
            [
                'thumbnail_image' => 'garden-cleaning.png',
                'name' => [
                    'en_US' => 'Garden Cleaning',
                    'nn_NO' => 'Hagevask',
                    'sv_SE' => 'Trädgårdsstädning',
                ],
                'type' => MembershipTypeEnum::Private(),
                'has_rut' => true,
                'task_en_us' => 'Standar Garden Cleaning',
                'task_desc_en_us' => 'Standard cleaning for garden',
                'task_sv_se' => 'Standard Trädgårdsstädning',
                'task_desc_sv_se' => 'Standard städning för trädgård',
            ],
            [
                'thumbnail_image' => 'window-cleaning.png',
                'name' => [
                    'en_US' => 'Window Cleaning',
                    'nn_NO' => 'Vindusvask',
                    'sv_SE' => 'Fönsterputsning',
                ],
                'type' => MembershipTypeEnum::Private(),
                'has_rut' => true,
                'task_en_us' => 'Standar Window Cleaning',
                'task_desc_en_us' => 'Standard cleaning for window',
                'task_sv_se' => 'Standard Fönsterputsning',
                'task_desc_sv_se' => 'Standard städning för fönster',
            ],
        ];
    }
}
