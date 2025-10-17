<?php

namespace App\Http\Controllers\CashierOrder;

use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\CustomerDiscount;
use App\Models\FixedPrice;
use App\Models\LaundryOrder;
use App\Models\LaundryPreference;
use App\Models\Product;
use App\Models\User;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use Inertia\Inertia;
use Inertia\Response;

class CashierOrderCartController extends BaseUserController
{
    use ResponseTrait;

    public function __construct(
        private LaundryOrderHistoryService $historyService
    ) {
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
    public function index(User $user): Response
    {
        $storeId = request()->session()->get('store_id');
        $excludeCategories = [
            config('downstairs.categories.cleaning.id'),
            config('downstairs.categories.miscellaneous.id'),
            config('downstairs.categories.store.id'),
        ];

        $queries = $this->getQueries(
            defaultFilter: [
                'stores_id_eq' => $storeId,
                'categories_id_notIn' => implode(',', $excludeCategories),
            ],
            size: -1,
        );
        $paginatedData = Product::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        $laundryOrder = null;
        $laundryOrderId = request('laundryOrderId');
        if ($laundryOrderId) {
            $laundryOrder = LaundryOrder::where('id', $laundryOrderId)
                ->where('user_id', $user->id)
                ->first();

            if ($laundryOrder) {
                $includes = [
                    'products',
                ];
                $onlys = [
                    'id',
                    'products.id',
                    'products.productId',
                    'products.name',
                    'products.note',
                    'products.quantity',
                    'products.price',
                    'products.priceWithVat',
                    'products.discount',
                    'products.vatGroup',
                    'products.hasRut',
                ];

                $laundryOrder = LaundryOrderResponseDTO::transformData(
                    $laundryOrder,
                    $includes,
                    onlys: $onlys
                );
            }
        }

        return Inertia::render('CashierCustomer/Cart/Overview/index', [
            'laundryOrder' => $laundryOrder,
            'products' => ProductResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'customer' => $this->getCustomer($user),
            'discount' => $this->getDiscount($user),
            'fixedPrice' => $this->getFixedPrice($user),
        ]);
    }

    /**
     * Display the checkout view.
     */
    public function indexCheckout(User $user): Response
    {
        return Inertia::render('CashierCustomer/Cart/Checkout/index', [
            'laundryPreferences' => $this->getPreferences(),
            'customer' => $this->getCustomer($user),
        ]);
    }

    /**
     * Get the customer.
     */
    public function getCustomer(User $user)
    {
        $includes = [
            'customers', 'info',
        ];

        $onlys = [
            'id',
            'fullname',
            'identityNumber',
            'formattedCellphone',
            'customers.id',
            'customers.membershipType',
            'customers.type',
            'info.notificationMethod',
        ];

        return UserResponseDTO::transformData($user, $includes, onlys: $onlys);
    }

    /**
     * Get the laundry preferences.
     */
    public function getPreferences()
    {
        $onlys = [
            'id',
            'name',
            'description',
            'vat_group',
            'hours',
            'price_with_vat',
            'price',
            'percentage',
        ];
        $query = LaundryPreference::selectWithRelations($onlys);

        $laundryPreferences = $query->get();

        return LaundryPreferenceResponseDTO::collection($laundryPreferences)
            ->only(...$onlys);
    }

    private function getDiscount(User $user)
    {
        /** @var CustomerDiscount $discount */
        $discount = $user->laundryDiscounts()
            ->hasAvailableUsage()
            ->period(now(), now()->endOfMonth())
            ->orderBy('value', 'desc')
            ->first();

        if ($discount) {
            return $discount->value;
        }

        return 0;
    }

    private function getFixedPrice(User $user)
    {
        $includes = [
            'rows',
            'laundryProducts',
        ];
        $onlys = [
            'rows.type',
            'rows.priceWithVat',
            'laundryProducts.id',
        ];

        $fixedPrice = FixedPrice::getActive(
            $user->id,
            [FixedPriceTypeEnum::Laundry(), FixedPriceTypeEnum::CleaningAndLaundry()],
            now(),
            now()->endOfMonth()
        );

        return $fixedPrice
            ? FixedPriceResponseDTO::transformData(
                $fixedPrice,
                $includes,
                onlys: $onlys,
            )
            : null;
    }
}
