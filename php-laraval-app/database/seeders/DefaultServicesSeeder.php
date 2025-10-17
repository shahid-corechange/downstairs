<?php

namespace Database\Seeders;

use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\TranslationEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DefaultServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getNames() as $value) {
            $service = Service::create([
                'membership_type' => $value['type'],
                'type' => $this->getServiceType($value),
                'price' => 122.4,
                'has_rut' => $value['has_rut'],
                'vat_group' => VatNumbersEnum::TwentyFive(),
                'thumbnail_image' => $value['thumbnail_image'] ?
                    'https://storagestagingdownstairs.blob.core.windows.net/images/'.
                    $value['thumbnail_image'] : null,
            ]);

            $service->translations()->createMany([
                [
                    'key' => 'name',
                    ...$value['name'],
                ],
                [
                    'key' => 'description',
                    ...$value['description'],
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
                'thumbnail_image' => 'home-cleaning.png',
                'name' => [
                    'en_US' => 'Home Cleaning',
                    'nn_NO' => 'Hjemmevask',
                    'sv_SE' => 'Hemstädning',
                ],
                'type' => MembershipTypeEnum::Private(),
                'has_rut' => true,
                'task_en_us' => 'Standar Home Cleaning',
                'task_desc_en_us' => 'Standard cleaning for home',
                'task_sv_se' => 'Standard Hemstädning',
                'task_desc_sv_se' => 'Standardstädning för hemmet',
                'description' => [
                    'en_US' => 'Home cleaning is the process of tidying and sanitizing a '.
                        'residence to maintain a clean and healthy living environment.',
                    'nn_NO' => 'Hjemmevask er prosessen med å rydde og '.
                        'rense en bolig for å opprettholde et rent og sunt bomiljø.',
                    'sv_SE' => 'Hemstädning är processen att städa och sanera en '.
                        'bostad för att upprätthålla en ren och hälsosam livsmiljö.',
                ],
            ],
            [
                'thumbnail_image' => null,
                'name' => [
                    'en_US' => 'Laundry',
                    'nn_NO' => 'Klesvask',
                    'sv_SE' => 'Tvätt',
                ],
                'type' => MembershipTypeEnum::Private(),
                'has_rut' => true,
                'task_en_us' => 'Laundry',
                'task_desc_en_us' => 'Laundry clothes',
                'task_sv_se' => 'Tvätt',
                'task_desc_sv_se' => 'Tvätta kläder',
                'description' => [
                    'en_US' => 'Cleaning and washing clothing, linens, '.
                        'and other textiles to remove dirt, stains, odors, and other impurities.',
                    'nn_NO' => 'rengjøring og vask av klær, sengetøy og '.
                        'andre tekstiler for å fjerne skitt, flekker, lukt og andre urenheter.',
                    'sv_SE' => 'rengöring och tvätt av kläder, sängkläder och '.
                        'andra textilier för att ta bort smuts, fläckar, lukter och andra föroreningar.',
                ],
            ],
            [
                'thumbnail_image' => 'office-cleaning.png',
                'name' => [
                    'en_US' => 'Office Cleaning',
                    'nn_NO' => 'Kontorvask',
                    'sv_SE' => 'Kontorstädning',
                ],
                'type' => MembershipTypeEnum::Company(),
                'has_rut' => false,
                'task_en_us' => 'Office Cleaning',
                'task_desc_en_us' => 'Standard cleaning for office',
                'task_sv_se' => 'Kontorsstädning',
                'task_desc_sv_se' => 'Standardstädning för kontor',
                'description' => [
                    'en_US' => 'Office cleaning is the process of tidying and sanitizing a '.
                        'office to maintain a clean and healthy working environment.',
                    'nn_NO' => 'Kontorvask er prosessen med å rydde og '.
                        'rense et kontor for å opprettholde et rent og sunt arbeidsmiljø.',
                    'sv_SE' => 'Kontorstädning är processen att städa och sanera ett '.
                        'kontor för att upprätthålla en ren och hälsosam arbetsmiljö.',
                ],
            ],
            [
                'thumbnail_image' => null,
                'name' => [
                    'en_US' => 'Company Laundry',
                    'nn_NO' => 'Firmavask',
                    'sv_SE' => 'Företagstvätt',
                ],
                'type' => MembershipTypeEnum::Company(),
                'has_rut' => false,
                'task_en_us' => 'Laundry',
                'task_desc_en_us' => 'Laundry clothes',
                'task_sv_se' => 'Tvätt',
                'task_desc_sv_se' => 'Tvätta kläder',
                'description' => [
                    'en_US' => 'Cleaning and washing clothing, linens, '.
                        'and other textiles to remove dirt, stains, odors, and other impurities.',
                    'nn_NO' => 'rengjøring og vask av klær, sengetøy og '.
                        'andre tekstiler for å fjerne skitt, flekker, lukt og andre urenheter.',
                    'sv_SE' => 'rengöring och tvätt av kläder, sängkläder och '.
                        'andra textilier för att ta bort smuts, fläckar, lukter och andra föroreningar.',
                ],
            ],
        ];
    }

    private function getServiceType(array $value): string
    {
        // Check if this is a laundry service based on the name
        $name = $value['name']['en_US'] ?? '';
        if (str_contains(strtolower($name), 'laundry')) {
            return ServiceTypeEnum::Laundry();
        }

        return ServiceTypeEnum::Cleaning();
    }
}
