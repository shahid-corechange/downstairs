<?php

namespace Database\Seeders;

use App\Enums\TranslationEnum;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionProduct;
use App\Models\SubscriptionStaffDetails;
use App\Models\Team;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    protected array $teamNames = [
        'Lion', 'Tiger', 'Elephant', 'Monkey', 'Snake',
        'Bear', 'Wolf', 'Fox', 'Horse', 'Deer',
        'Rabbit', 'Panda', 'Penguin', 'Dolphin', 'Shark',
        'Whale', 'Eagle', 'Owl', 'Parrot', 'Dove',
    ];

    protected int $workerCounter = 1;

    /**
     * Run the database seeds.
     */
    public function run(SubscriptionService $subscriptionService): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 3 : 2;
        $users = $this->getUsers();

        foreach ($users as $user) {
            Subscription::factory()
                ->count($numberOfInstances)
                ->hasDetails(1)
                ->forUser($user)
                ->create()
                ->each(function (Subscription $subscription) use ($subscriptionService) {
                    $team = $this->getTeam();
                    $subscription->update(['team_id' => $team->id]);

                    if ($team->users) {
                        foreach ($team->users as $user) {
                            SubscriptionStaffDetails::create([
                                'subscription_id' => $subscription->id,
                                'user_id' => $user->id,
                                'quarters' => $subscription->quarters,
                            ]);
                        }
                    }
                    //get two rundom products where service id is equal to subscription service id
                    $products = Product::inRandomOrder()
                        ->where('service_id', $subscription->service_id)->take(2)->get();

                    foreach ($products as $product) {
                        SubscriptionProduct::create([
                            'subscription_id' => $subscription->id,
                            'product_id' => $product->id,
                            'quantity' => 1,
                        ]);
                    }

                    $task = fake()->randomElement($this->getTasks());
                    $customTask = $subscription->tasks()->create();

                    $customTask->setName($task['name_sv_se'], TranslationEnum::Swedish());
                    $customTask->setDescription($task['description_sv_se'], TranslationEnum::Swedish());
                    $customTask->setName($task['name_en_us'], TranslationEnum::English());
                    $customTask->setDescription($task['description_en_us'], TranslationEnum::English());

                    $subscriptionService->createInitialSchedules($subscription, 0);
                });
        }
    }

    private function getTasks()
    {
        return [
            [
                'name_en_us' => 'Floor dirt cleaning',
                'description_en_us' => 'Remove dirt from floor',
                'name_sv_se' => 'Golvsmutsrengöring',
                'description_sv_se' => 'Ta bort smuts från golvet',
            ],
            [
                'name_en_us' => 'Floor dust cleaning',
                'description_en_us' => 'Remove dust from floor',
                'name_sv_se' => 'Golvdammrengöring',
                'description_sv_se' => 'Ta bort damm från golvet',
            ],
            [
                'name_en_us' => 'Floor stain cleaning',
                'description_en_us' => 'Remove stain from floor',
                'name_sv_se' => 'Golvfläckrengöring',
                'description_sv_se' => 'Ta bort fläck från golvet',
            ],
            [
                'name_en_us' => 'Floor unwanted substance cleaning',
                'description_en_us' => 'Remove unwanted substance from floor',
                'name_sv_se' => 'Golv oönskad ämnesrengöring',
                'description_sv_se' => 'Ta bort oönskat ämne från golvet',
            ],
        ];
    }

    private function getTeam(): Team
    {
        $worker = $this->getWorker();
        $team = Team::create([
            'name' => $this->getTeamName(),
            'color' => fake()->safeHexColor(),
            'description' => fake()->sentence(),
        ]);
        $team->users()->attach($worker->id);

        return $team;
    }

    /**
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getUsers()
    {
        return User::whereIn('email', [
            'customer1@email.com',
            'customer2@email.com',
            'company1@email.com',
            'company2@email.com',
            'company3@email.com',
        ])->orderBy('id')->get();
    }

    private function getWorker(): ?User
    {
        $user = User::where('email', "worker{$this->workerCounter}@email.com")->first();
        $this->workerCounter = $this->workerCounter === 3 ? 1 : $this->workerCounter + 1;

        return $user;
    }

    private function getTeamName()
    {
        return array_pop($this->teamNames);
    }
}
