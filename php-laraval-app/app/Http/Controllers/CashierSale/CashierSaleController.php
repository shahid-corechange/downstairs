<?php

namespace App\Http\Controllers\CashierSale;

use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\StoreSale\CreateStoreSaleRequestDTO;
use App\DTOs\StoreSale\StoreSaleProductRequestDTO;
use App\DTOs\StoreSale\StoreSaleResponseDTO;
use App\Enums\StoreSale\StoreSaleStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StoreSale;
use App\Services\Order\OrderStoreSaleService;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashierSaleController extends BaseUserController
{
    public function __construct(
        public OrderStoreSaleService $orderService
    ) {
        //
    }

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'categories',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'name',
        'description',
        'unit',
        'priceWithVat',
        'creditPrice',
        'vatGroup',
        'hasRut',
        'status',
        'thumbnailImage',
        'color',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'categories.id',
        'categories.name',
        'translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'stores_id_eq' => $storeId,
                'categories_id_eq' => config('downstairs.categories.store.id'),
                'id_neq' => config('downstairs.products.productSalesMisc.id'),
            ],
            size: -1,
        );
        $paginatedData = Product::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('DirectSale/Overview/index', [
            'products' => ProductResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
        ]);
    }

    public function checkout(): Response
    {
        return Inertia::render('DirectSale/Checkout/Overview/index');
    }

    public function cardPayment(): Response
    {
        return Inertia::render('DirectSale/Checkout/CardPayment/index');
    }

    /**
     * Store a new order laundry
     */
    public function store(CreateStoreSaleRequestDTO $request): RedirectResponse
    {
        $includes = [
            'store',
            'causer',
            'products',
        ];

        $onlys = [
            'id',
            'status',
            'paymentMethod',
            'totalToPay',
            'totalPriceWithVat',
            'totalPriceWithDiscount',
            'totalDiscount',
            'totalVat',
            'roundedTotalToPay',
            'roundAmount',
            'createdAt',
            'store.id',
            'store.name',
            'causer.id',
            'causer.fullname',
            'products.name',
            'products.quantity',
            'products.price',
            'products.discount',
            'products.priceWithVat',
            'products.discountAmount',
            'products.vatAmount',
            'products.priceWithDiscount',
            'products.vatGroup',
        ];

        $storeId = request()->session()->get('store_id');
        $data = $request->toArray();
        $sourceProducts = $this->getNotModifiedProducts($request->products, $storeId);
        $customer = Customer::find($request->customer_id);
        $storeSaleProducts = [];

        foreach ($data['products'] as $product) {
            /** @var Product|null $sourceProduct */
            $sourceProduct = $sourceProducts->firstWhere('id', $product['product_id']);

            // update product id to product sales misc if is modified and product id is 0
            if ($product['is_modified'] || $product['product_id'] === 0) {
                $product['product_id'] = config('downstairs.products.productSalesMisc.id');
            } elseif ($sourceProduct) {
                $product['has_rut'] = $customer->isPrivate() ? $sourceProduct->has_rut : false;
                $product['vat_group'] = $sourceProduct->vat_group;
                $product['price'] = $sourceProduct->price;
            } elseif (! $sourceProduct) {
                // don't include product if product is not found
                continue;
            }

            $storeSaleProducts[] = $product;
        }

        $storeSale = DB::transaction(function () use ($data, $storeId, $storeSaleProducts) {
            $storeSale = StoreSale::create([
                ...$data,
                'store_id' => $storeId,
                'status' => StoreSaleStatusEnum::Closed(),
                'causer_id' => auth()->user()->id,
            ]);

            $storeSale->products()->createMany($storeSaleProducts);

            // Create order and invoice
            scoped_localize('sv_SE', function () use ($storeSale, $data) {
                [$order, $invoice] = $this->orderService->createOrder(
                    $storeSale,
                    $data['user_id'],
                    $data['customer_id'],
                );
                $this->orderService->createOrderRows($order, $storeSale);
                UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
            });

            return $storeSale;
        });

        return back()->with([
            'success' => __('sale created successfully'),
            'successPayload' => [
                'storeSale' => StoreSaleResponseDTO::transformData($storeSale, $includes, onlys: $onlys),
            ],
        ]);
    }

    /**
     * Get not modified product ids
     *
     * @param  DataCollection  $products
     * @param  int  $storeId
     */
    private function getNotModifiedProducts($products, $storeId)
    {
        // Get not modified product ids
        $notModifiedProductIds = $products->reduce(function ($carry, StoreSaleProductRequestDTO $product) {
            if (! $product->is_modified) {
                $carry[] = $product->product_id;
            }

            return $carry;
        }, []);

        return Product::whereIn('id', $notModifiedProductIds)
            ->whereHas('stores', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->get();
    }
}
