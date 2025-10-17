<?php

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\SettingTypeEnum;
use App\Models\Addon;
use App\Models\Category;
use App\Models\FixedPriceRow;
use App\Models\GlobalSetting;
use App\Models\Order;
use App\Models\OrderFixedPriceRow;
use App\Models\Product;
use App\Models\SubscriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Symfony\Component\Console\Output\ConsoleOutput;

if (! function_exists('generate_filename')) {
    /**
     * Generate unique filename.
     *
     * @param  string  $prefix
     * @param  string  $extension
     * @return string
     */
    function generate_filename($prefix, $extension)
    {
        return $prefix.'_'.uniqid().'_'.now()->format('Y-m-d_H:i:s').'.'.$extension;
    }
}

if (! function_exists('vite_assets')) {
    function vite_assets(): HtmlString
    {
        $devServer = null;

        if (app()->environment('local')) {
            try {
                $devServer = file_get_contents(public_path('hot'));
            } catch (Exception) {
            }
        }

        if ($devServer) {
            return new HtmlString(<<<'HTML'
            <script type="module" src="http://127.0.0.1:5173/@vite/client"></script>
            <script type="module" src="http://127.0.0.1:5173/resources/js/app.tsx"></script>
        HTML);
        }

        $manifest = Http::get('nginx/build/manifest.json')->json();
        $cssStrings = '';

        foreach ($manifest['resources/js/app.tsx']['css'] as $css) {
            $cssStrings .= '<link rel="stylesheet" href="/build/'.$css.'">';
        }

        return new HtmlString(<<<HTML
        $cssStrings
        <script type="module" src="/build/{$manifest['resources/js/app.tsx']['file']}"></script>
    HTML);
    }
}

if (! function_exists('middleware_tags')) {
    function middleware_tags(string $middleware, string ...$tags)
    {
        return $middleware.':'.implode('|', $tags);
    }
}

if (! function_exists('write_output_done')) {
    function write_output_done(string $name, int $time)
    {
        $output = new ConsoleOutput();
        $number = number_format($time);
        $dots = 140 - strlen($name) - strlen($number);
        $info = $name.' '.str_repeat('.', $dots).' '.$number;
        $output->write($info.' ms <fg=green;options=bold>DONE</>'.PHP_EOL);
    }
}

if (! function_exists('scoped_localize')) {
    function scoped_localize(?string $locale, callable $callback)
    {
        if (is_null($locale)) {
            return $callback();
        }

        $originalLocale = app()->getLocale();

        app()->setLocale($locale);

        $result = $callback();

        app()->setLocale($originalLocale);

        return $result;
    }
}

if (! function_exists('get_cache')) {
    /**
     * Get cache based on tag and key,
     * if cache not found, return the callback result and cache it.
     *
     * @template TCacheValue
     *
     * @param  TCacheValue|(\Closure(): TCacheValue)  $default
     * @param  int|null  $ttl
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    function get_cache(string $tag, string $key, $default = null, $ttl = null)
    {
        $hash = hash('sha256', $key);
        $data = Cache::tags($tag)->get($hash);

        if (! is_null($data)) {
            return $data;
        }

        $data = value($default);
        $ttl = $ttl ?? config('downstairs.cache.ttl');
        Cache::tags($tag)->put($hash, $data, $ttl);

        return $data;
    }
}

if (! function_exists('calculate_calendar_quarters')) {
    /**
     * Calculate calendar quarters of the subscription or schedule
     * based on the total quarters and total workers.
     */
    function calculate_calendar_quarters(int $totalQuarters, int $totalWorkers = 1): int
    {
        return (int) ceil($totalQuarters / ($totalWorkers ?: 1));
    }
}

if (! function_exists('calculate_end_time')) {
    /**
     * Calculate the end time of the subscription or schedule
     * based on the start time and the calendar quarters.
     *
     * @param  string|\Illuminate\Support\Carbon  $startTime
     */
    function calculate_end_time($startTime, int $calendarQuarters, string $format = 'H:i:s'): string
    {
        if (! $startTime instanceof Carbon) {
            try {
                $startTime = Carbon::createFromTimeString($startTime);
            } catch (\Carbon\Exceptions\InvalidFormatException) {
                $startTime = Carbon::parse($startTime);
            }
        }

        return $startTime->addMinutes($calendarQuarters * 15)
            ->format($format);
    }
}

if (! function_exists('expect_json')) {
    /**
     * Check if request expect a JSON response.
     */
    function expect_json(Request $request): bool
    {
        return $request->expectsJson() && ! $request->hasHeader('X-Inertia');
    }
}

if (! function_exists('generate_swedish_ssn')) {
    /**
     * Generate a random Swedish social security number (personnummer).
     */
    function generate_swedish_ssn($year = null, $month = null, $day = null): string
    {
        // Set default date values if not provided
        $year = $year ?? rand(1900, 2020);
        $month = $month ?? rand(1, 12);
        $day = $day ?? rand(1, 28); // Choosing up to 28 to avoid dealing with month-specific days

        // Format the date part as YYYYMMDD
        $datePart = sprintf('%04d%02d%02d', $year, $month, $day);

        // Generate the first three random digits of the serial number
        $serialPart = sprintf('%03d', rand(0, 999));

        // Combine date part and serial part
        $partialSSN = $datePart.$serialPart;

        // Calculate the check digit using the Luhn algorithm
        $sum = 0;
        $checkDigit = substr($partialSSN, 2);
        $length = strlen($checkDigit);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $checkDigit[$length - $i - 1];
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        // Combine the parts to form the full Swedish social security number
        $ssn = $partialSSN.$checkDigit;

        return $ssn;
    }
}

if (! function_exists('get_transport')) {
    /**
     * Get the transport product.
     *
     * @return Product
     */
    function get_transport()
    {
        return Cache::rememberForever('transport', function () {
            return Product::find(config('downstairs.products.transport.id'));
        });
    }
}

if (! function_exists('get_material')) {
    /**
     * Get the material product.
     *
     * @return Product
     */
    function get_material()
    {
        return Cache::rememberForever('material', function () {
            return Product::find(config('downstairs.products.material.id'));
        });
    }
}

if (! function_exists('array_keys_to_camel_case')) {
    /**
     * Change associative array keys to camel case.
     *
     * @return array
     */
    function array_keys_to_camel_case(array $data)
    {
        $results = [];

        foreach ($data as $key => $val) {
            $results[Str::camel($key)] = is_array($val) ? array_keys_to_camel_case($val) : $val;
        }

        return $results;
    }
}

if (! function_exists('array_keys_to_snake_case')) {
    /**
     * Change associative array keys to snake case.
     *
     * @return array
     */
    function array_keys_to_snake_case(array $data)
    {
        $results = [];

        foreach ($data as $key => $val) {
            $results[Str::snake($key)] = $val;
        }

        return $results;
    }
}

if (! function_exists('enum_to_options')) {
    /**
     * Transform enum to options.
     *
     * @return array
     */
    function enum_to_options(array $data, bool $translation = true)
    {
        return array_reduce($data, function ($acc, $value) use ($translation) {
            $acc[$value] = $translation ? __($value) : $value;

            return $acc;
        }, []);
    }
}

if (! function_exists('transform_settings_value')) {
    /**
     * Transform the global settings value based on the type.
     */
    function transform_settings_value(string $value, string $type)
    {
        switch ($type) {
            case SettingTypeEnum::Integer():
                return intval($value);
            case SettingTypeEnum::Boolean():
                return $value == 'true';
            case SettingTypeEnum::Float():
                return floatval($value);
            default:
                return $value;
        }
    }
}

if (! function_exists('get_addons')) {
    /**
     * Get the addons.
     * Mostly used for the unassign subscription and
     * need forget when update addon or service.
     *
     * @return \Illuminate\Database\Eloquent\Collection<array-key,Addon>
     */
    function get_addons()
    {
        return Cache::rememberForever('addons', function () {
            return Addon::with('translations')
                ->withTrashed()
                ->get();
        });
    }
}

if (! function_exists('get_products')) {
    /**
     * Get the products.
     * Mostly used for the unassign subscription and
     * need forget when update product or service.
     *
     * @return \Illuminate\Database\Eloquent\Collection<array-key,Product>
     */
    function get_products()
    {
        return Cache::rememberForever('products', function () {
            return Product::with('translations')
                ->withTrashed()
                ->get();
        });
    }
}

if (! function_exists('get_setting')) {
    /**
     * Get the global setting value.
     *
     * @template TCacheValue
     *
     * @param  TCacheValue|(\Closure(): TCacheValue)  $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    function get_setting(string $key, $default = null)
    {
        $key = Str::snake($key);
        /** @var array|null */
        $data = Cache::get("global_settings:$key");

        if (! is_null($data)) {
            return transform_settings_value($data['value'], $data['type']);
        }

        $data = GlobalSetting::where('key', strtoupper($key))->first();

        if (is_null($data)) {
            return value($default);
        }

        $value = transform_settings_value($data->value, $data->type);
        Cache::forever(
            "global_settings:$key",
            [
                'value' => $value,
                'type' => $data->type,
            ]
        );

        return $value;
    }
}

if (! function_exists('obscure_email')) {
    /**
     * Obscure the email address.
     */
    function obscure_email(string $email): string
    {
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1];
        $username = substr($username, 0, 2).'***'.substr($username, -1);

        return $username.'@'.$domain;
    }
}

if (! function_exists('obscure_phone_number')) {
    /**
     * Obscure the phone number.
     */
    function obscure_phone_number(string $phone): string
    {
        return substr($phone, 0, 3).'***'.substr($phone, -3);
    }
}

if (! function_exists('get_credit_refund_description')) {
    /**
     * Get the credit refund description.
     */
    function get_credit_refund_description(): string
    {
        return ' *(Se info nedan)';
    }
}

if (! function_exists('get_laundry_row_description')) {
    /**
     * Get the laundry row description laundry products fixed price.
     *
     * @param  OrderFixedPriceRow|FixedPriceRow  $row
     */
    function get_laundry_row_description($row): string
    {
        return scoped_localize('sv_SE', function () use ($row) {
            if ($row instanceof OrderFixedPriceRow) {
                $laundryProducts = $row->fixedPrice->fixedPrice->laundryProducts;
            } else {
                $laundryProducts = $row->fixedPrice->laundryProducts;
            }

            return $laundryProducts->isNotEmpty() ?
                $laundryProducts->map(fn ($product) => $product->name)->implode(', ') : '';
        });
    }
}

if (! function_exists('price_with_vat')) {
    /**
     * Get the price with VAT.
     */
    function price_with_vat(float $price, float $vat): float
    {
        return round($price * (1 + $vat / 100), 2);
    }
}

if (! function_exists('price_without_vat')) {
    /**
     * Get the price without VAT.
     */
    function price_without_vat(float $price, float $vat): float
    {
        return round($price / (1 + $vat / 100), 2);
    }
}

if (! function_exists('get_fixed_price_article_ids')) {
    /**
     * Get the fixed price article ids.
     */
    function get_fixed_price_article_ids(Order $order): array
    {
        if (! $order->order_fixed_price_id || ! $order->subscription || ! $order->service) {
            return [];
        }

        /** @var \App\Models\Subscription */
        $subscription = $order->subscription;
        $subscriptionProductsArticleIds = $subscription->items->map(
            function (SubscriptionItem $item) {
                return $item->itemable->fortnox_article_id;
            }
        )->filter()->toArray();

        // Laundry doesn't have transport and material
        if ($subscription->isLaundry()) {
            return array_merge(
                [$order->service->fortnox_article_id],
                $subscriptionProductsArticleIds
            );
        }

        $transport = get_transport();
        $material = get_material();

        return array_merge(
            [
                $order->service->fortnox_article_id,
                $transport->fortnox_article_id,
                $material->fortnox_article_id,
            ],
            $subscriptionProductsArticleIds
        );
    }
}

if (! function_exists('placeholder')) {
    /**
     * Get placeholder value for string.
     */
    function placeholder(?string $value): string
    {
        return $value ?? '';
    }
}

if (! function_exists('weeks_to_days')) {
    /**
     * Convert weeks to days.
     */
    function weeks_to_days(int $weeks): int
    {
        return $weeks * 7 + ($weeks / 52);
    }
}

if (! function_exists('to_translation')) {
    /**
     * transform data to translation
     */
    function to_translation(string $key, array $data): array
    {
        return [...$data, 'key' => $key];
    }
}

if (! function_exists('products_request_to_array')) {
    /**
     * transform products request dto to array, in order to sync to subscription item
     *
     * @param  array<int, array>  $products
     */
    function products_request_to_array($products): array
    {
        return array_reduce(
            $products,
            function ($carry, $product) {
                if ($product['id']) {
                    $carry[$product['id']] = $product;
                }
            },
            []
        );
    }
}

if (! function_exists('get_store_category')) {
    /**
     * Get the store category.
     *
     * @return Category
     */
    function get_store_category()
    {
        return Cache::rememberForever('store_category', function () {
            return Category::find(config('downstairs.categories.store.id'));
        });
    }
}

if (! function_exists('max_schedule_days')) {
    /**
     * To get the end of the schedule days.
     */
    function max_schedule_days(): int
    {
        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        return weeks_to_days($refillSequence);
    }
}
