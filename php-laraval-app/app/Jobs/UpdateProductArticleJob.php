<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Article\UpdateArticlePriceRequestDTO;
use App\DTOs\Fortnox\Article\UpdateArticleRequestDTO;
use App\Models\Product;
use App\Services\Fortnox\FortnoxCustomerService;

class UpdateProductArticleJob extends BaseJob
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
        protected Product $product,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxCustomerService $fortnoxService
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            app()->setLocale('sv_SE');
            $type = $this->product->categories->contains(config('downstairs.categories.store.id')) ?
                'STOCK' : 'SERVICE';
            $response = $fortnoxService->updateArticle(
                $this->product->fortnox_article_id,
                UpdateArticleRequestDTO::from([
                    'description' => $this->product->name,
                    'housework' => $this->product->has_rut,
                    'housework_type' => $this->product->has_rut ? 'CLEANING' : '',
                    'note' => $this->product->description,
                    'type' => $type,
                    'unit' => $this->product->unit,
                    'VAT' => $fortnoxService->getVat($this->product->vat_group),
                    'sales_account' => $fortnoxService->getSalesAccount($this->product->vat_group),
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
                        'price' => $this->product->price,
                        'price_list' => '1',
                    ])
                );
            }
        });
    }
}
