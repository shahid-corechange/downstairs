<?php

namespace Database\Seeders;

use App\Models\ServiceQuarter;
use Illuminate\Database\Seeder;

class DefaultServiceQuartersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = app()->environment() !== 'testing' ? $this->getValues() : $this->getTestValues();

        $values = array_reduce($data, function ($carry, $item) {
            $values = explode(',', $item);
            $carry[] = [
                'service_id' => $values[0],
                'min_square_meters' => $values[1],
                'max_square_meters' => $values[2],
                'quarters' => $values[3],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            return $carry;
        }, []);

        ServiceQuarter::insert($values);
    }

    private function getValues(): array
    {
        return [
            '1,0,50,8',
            '1,51,75,10',
            '1,76,100,12',
            '1,101,120,14',
            '1,121,150,16',
            '1,151,175,18',
            '1,176,200,20',
            '1,201,240,22',
            '1,241,280,24',
            '1,281,320,28',
            '1,321,400,32',
            '1,401,500,36',
            '1,501,600,40',

            '3,0,50,8',
            '3,51,75,10',
            '3,76,100,12',
            '3,101,120,14',
            '3,121,150,16',
            '3,151,175,18',
            '3,176,200,20',
            '3,201,240,22',
            '3,241,280,24',
            '3,281,320,28',
            '3,321,400,32',
            '3,401,500,36',
            '3,501,600,40',
        ];
    }

    private function getTestValues(): array
    {
        return [
            '1,0,50,8',
            '1,51,75,10',
            '1,76,100,12',

            '3,0,50,8',
            '3,51,75,10',
            '3,76,100,12',
        ];
    }
}
