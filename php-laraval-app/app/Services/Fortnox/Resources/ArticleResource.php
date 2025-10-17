<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\Article\ArticleDTO;
use App\DTOs\Fortnox\Article\ArticlePriceDTO;
use App\DTOs\Fortnox\Article\CreateArticlePriceRequestDTO;
use App\DTOs\Fortnox\Article\CreateArticleRequestDTO;
use App\DTOs\Fortnox\Article\UpdateArticlePriceRequestDTO;
use App\DTOs\Fortnox\Article\UpdateArticleRequestDTO;
use App\Enums\Product\ProductUnitEnum;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait ArticleResource
{
    /**
     * Get list of articles.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Article\ArticleDTO>
     */
    public function getArticles(
        string $filter = null,
        string $articleNumber = null,
        string $description = null,
        string $ean = null,
        string $supplierNumber = null,
        string $manufacturer = null,
        string $manufacturerArticleNumber = null,
        string $webshop = null,
        string $lastModified = null,
        string $sortBy = null,
    ) {
        $query = [
            'filter' => $filter,
            'articlenumber' => $articleNumber,
            'description' => $description,
            'ean' => $ean,
            'suppliernumber' => $supplierNumber,
            'manufacturer' => $manufacturer,
            'manufacturerarticlenumber' => $manufacturerArticleNumber,
            'webshop' => $webshop,
            'lastmodified' => $lastModified,
            'sortby' => $sortBy,
        ];
        $response = $this->sendRequest('articles', 'GET', query: $query);

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                $query = array_filter($query, function ($value) {
                    return $value !== null;
                });

                throw new OperationFailedException(
                    __(
                        'failed to get articles',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode($query),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return ArticleDTO::collection($response->json('Articles'));
    }

    public function deleteArticle(string $articleNumber): void
    {
        $response = $this->sendRequest("articles/{$articleNumber}", 'DELETE');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_NO_CONTENT) {
            throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
        }
    }

    public function createArticle(CreateArticleRequestDTO $data): ArticleDTO
    {
        $response = $this->sendRequest(
            'articles',
            'POST',
            body: ['Article' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create article',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'description' => $data->description,
                                'housework' => $data->housework,
                                'housework_type' => $data->housework_type,
                                'type' => $data->type,
                                'unit' => $data->unit,
                                'VAT' => $data->VAT,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return ArticleDTO::from($response->json('Article'));
    }

    public function createArticlePrice(CreateArticlePriceRequestDTO $data): ArticlePriceDTO
    {
        $response = $this->sendRequest(
            'prices',
            'POST',
            body: ['Price' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create article price',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode($data->toArray()),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return ArticlePriceDTO::from($response->json('Price'));
    }

    public function updateArticle(int $articleNumber, UpdateArticleRequestDTO $data): ArticleDTO
    {
        $response = $this->sendRequest(
            "articles/$articleNumber",
            'PUT',
            body: ['Article' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update article',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'article_number' => $articleNumber,
                                'description' => $data->description,
                                'housework' => $data->housework,
                                'housework_type' => $data->housework_type,
                                'type' => $data->type,
                                'unit' => $data->unit,
                                'VAT' => $data->VAT,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return ArticleDTO::from($response->json('Article'));
    }

    public function updateArticlePrice(
        string $priceList,
        int $articleNumber,
        float $fromQuantity,
        UpdateArticlePriceRequestDTO $data
    ): ArticlePriceDTO {
        $response = $this->sendRequest(
            "prices/$priceList/$articleNumber/$fromQuantity",
            'PUT',
            body: ['Price' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update article price',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                ...$data->toArray(),
                                'price_list' => $priceList,
                                'article_number' => $articleNumber,
                                'from_quantity' => $fromQuantity,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return ArticlePriceDTO::from($response->json('Price'));
    }

    /**
     * Sync existing service without fortnox_id to Fortnox.
     *
     * @param  \App\Models\Service  $service
     */
    public function syncService($service): void
    {
        $response = $this->createArticle(CreateArticleRequestDTO::from([
            'description' => $service->name,
            'housework' => $service->has_rut,
            'housework_type' => $service->has_rut ? 'CLEANING' : '',
            'note' => $service->description ?? '',
            'type' => 'SERVICE',
            'unit' => ProductUnitEnum::Piece(),
            'VAT' => $this->getVat($service->vat_group),
            'sales_account' => $this->getSalesAccount($service->vat_group),
        ]));

        if ($response) {
            $this->createArticlePrice(CreateArticlePriceRequestDTO::from([
                'article_number' => $response->article_number,
                'from_quantity' => 0,
                'price' => $service->price,
                'price_list' => '1',
            ]));

            $service->update([
                'fortnox_article_id' => $response->article_number,
            ]);
        }
    }

    /**
     * Sync existing product without fortnox_id to Fortnox.
     *
     * @param  \App\Models\Product  $product
     */
    public function syncProduct($product): void
    {
        $type = $product->categories->contains(config('downstairs.categories.store.id')) ?
            'STOCK' : 'SERVICE';
        $response = $this->createArticle(CreateArticleRequestDTO::from([
            'description' => $product->name,
            'housework' => $product->has_rut,
            'housework_type' => $product->has_rut ? 'CLEANING' : '',
            'note' => $product->description ?? '',
            'type' => $type,
            'unit' => $product->unit,
            'VAT' => $this->getVat($product->vat_group),
            'sales_account' => $this->getSalesAccount($product->vat_group),
        ]));

        if ($response) {
            $this->createArticlePrice(CreateArticlePriceRequestDTO::from([
                'article_number' => $response->article_number,
                'from_quantity' => 0,
                'price' => $product->price,
                'price_list' => '1',
            ]));

            $product->update([
                'fortnox_article_id' => $response->article_number,
            ]);
        }
    }

    /**
     * Sync existing addon without fortnox_id to Fortnox.
     *
     * @param  \App\Models\Addon  $addon
     */
    public function syncAddon($addon): void
    {
        $response = $this->createArticle(CreateArticleRequestDTO::from([
            'description' => $addon->name,
            'housework' => $addon->has_rut,
            'housework_type' => $addon->has_rut ? 'CLEANING' : '',
            'note' => $addon->description ?? '',
            'type' => 'SERVICE',
            'unit' => $addon->unit,
            'VAT' => $this->getVat($addon->vat_group),
            'sales_account' => $this->getSalesAccount($addon->vat_group),
        ]));

        if ($response) {
            $this->createArticlePrice(CreateArticlePriceRequestDTO::from([
                'article_number' => $response->article_number,
                'from_quantity' => 0,
                'price' => $addon->price,
                'price_list' => '1',
            ]));

            $addon->update([
                'fortnox_article_id' => $response->article_number,
            ]);
        }
    }

    /**
     * On the production use a sales account based on the VAT group.
     * Sales account to defined specifically VAT group.
     */
    public function getSalesAccount(int $vat)
    {
        if (app()->environment() !== 'production') {
            return '';
        }

        $salesAccount = config('services.fortnox.sales_account');
        $accounts = json_decode($salesAccount, true);
        $key = "vat{$vat}";

        return $accounts[$key] ?? '';
    }

    /**
     * On the production use the sales account to define the VAT group.
     * Leave it empty on the production.
     * The sales account will automatically define it.
     */
    public function getVat(int $vat)
    {
        return app()->environment() !== 'production' ? $vat : '';
    }
}
