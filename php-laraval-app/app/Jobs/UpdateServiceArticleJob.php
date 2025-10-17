<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Article\UpdateArticlePriceRequestDTO;
use App\DTOs\Fortnox\Article\UpdateArticleRequestDTO;
use App\Models\Service;
use App\Services\Fortnox\FortnoxCustomerService;

class UpdateServiceArticleJob extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Service $service,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxCustomerService $fortnoxService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            app()->setLocale('sv_SE');
            $response = $fortnoxService->updateArticle(
                $this->service->fortnox_article_id,
                UpdateArticleRequestDTO::from([
                    'description' => $this->service->name,
                    'housework' => true,
                    'housework_type' => 'CLEANING',
                    'note' => $this->service->description,
                    'type' => 'SERVICE',
                    'unit' => 'h',
                    'VAT' => $this->service->vat_group,
                ])
            );

            if ($response) {
                $fortnoxService->updateArticlePrice(
                    '1',
                    $response->article_number,
                    0,
                    UpdateArticlePriceRequestDTO::from([
                        'article_number' => $response->article_number,
                        'from_quantity' => 0,
                        'price' => $this->service->price,
                        'price_list' => 'A',
                    ])
                );
            }
        });
    }
}
