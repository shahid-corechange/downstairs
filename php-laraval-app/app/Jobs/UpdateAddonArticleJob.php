<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Article\UpdateArticlePriceRequestDTO;
use App\DTOs\Fortnox\Article\UpdateArticleRequestDTO;
use App\Models\Addon;
use App\Services\Fortnox\FortnoxCustomerService;

class UpdateAddonArticleJob extends BaseJob
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
        protected Addon $addon,
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
                $this->addon->fortnox_article_id,
                UpdateArticleRequestDTO::from([
                    'description' => $this->addon->name,
                    'housework' => $this->addon->has_rut,
                    'housework_type' => $this->addon->has_rut ? 'CLEANING' : '',
                    'note' => $this->addon->description,
                    'type' => 'SERVICE',
                    'unit' => $this->addon->unit,
                    'VAT' => $fortnoxService->getVat($this->addon->vat_group),
                    'sales_account' => $fortnoxService->getSalesAccount($this->addon->vat_group),
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
                        'price' => $this->addon->price,
                        'price_list' => '1',
                    ])
                );
            }
        });
    }
}
