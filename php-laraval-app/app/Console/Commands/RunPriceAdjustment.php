<?php

namespace App\Console\Commands;

use App\Enums\CacheEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentRowStatusEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentStatusEnum;
use App\Models\Addon;
use App\Models\FixedPrice;
use App\Models\PriceAdjustment;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Service;
use Cache;
use DB;
use Illuminate\Console\Command;
use Log;

class RunPriceAdjustment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price-adjustment:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For update some price based on price adjustment';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get price adjustment that will be executed
        $priceAdjustments = PriceAdjustment::notDone()
            ->where('execution_date', '<=', now()->format('Y-m-d'))
            ->with('rows')
            ->get();

        foreach ($priceAdjustments as $priceAdjustment) {
            $rows = $priceAdjustment->rows->filter(function ($row) {
                return $row->status === PriceAdjustmentRowStatusEnum::Pending();
            });
            $totalRows = $rows->count();
            $totalRowsPending = $totalRows;

            foreach ($rows as $row) {
                if ($row->adjustable) {
                    try {
                        DB::transaction(function () use ($priceAdjustment, $row, &$totalRowsPending) {
                            $this->processRow($priceAdjustment, $row);
                            $row->update(['status' => PriceAdjustmentRowStatusEnum::Done()]);
                            $totalRowsPending--;
                        });
                    } catch (\Exception $e) {
                        $this->logError($priceAdjustment, $row, $e);
                    }
                } else {
                    $this->logNotFound($priceAdjustment, $row);
                }
            }

            $totalRowsDone = $totalRows - $totalRowsPending;

            // Update price adjustment status
            if ($totalRowsDone > 0) {
                $priceAdjustment->update([
                    'status' => $totalRowsPending === 0 ?
                        PriceAdjustmentStatusEnum::Done() : PriceAdjustmentStatusEnum::Partial(),
                ]);
                $this->clearCache($priceAdjustment);
            }

            $info = "Price adjustment executed, total row: {$totalRows}, done row: {$totalRowsDone}";

            // Send log to daily log
            $this->logEnd($priceAdjustment, $info);
        }

        return 0;
    }

    /**
     * Process price adjustment row
     *
     * @param  PriceAdjustment  $priceAdjustment
     * @param  PriceAdjustmentRow  $row
     * @return void
     *
     * @throws \Exception
     */
    private function processRow($priceAdjustment, $row)
    {
        switch ($priceAdjustment->type) {
            case 'service':
                /** @var Service $service */
                $service = $row->adjustable;
                $service->update(['price' => $row->price]);
                break;
            case 'addon':
                /** @var Addon $addon */
                $addon = $row->adjustable;
                $addon->update(['price' => $row->price]);
                break;
            case 'product':
                /** @var Product $product */
                $product = $row->adjustable;
                $product->update(['price' => $row->price]);
                break;
            case 'fixed_price':
                /** @var FixedPrice $fixedPrice */
                $fixedPrice = $row->adjustable;
                // Fixed price only support dynamic percentage
                foreach ($fixedPrice->rows as $row) {
                    $row->update([
                        'price' => $row->price + ($row->price * $row->price_adjustment->price / 100),
                    ]);
                }
                break;
        }
    }

    /**
     * Clear cache
     *
     * @param  PriceAdjustment  $priceAdjustment
     * @return void
     */
    private function clearCache($priceAdjustment)
    {
        switch ($priceAdjustment->type) {
            case 'service':
                Cache::tags(CacheEnum::Services())->flush();
                break;
            case 'addon':
                Cache::tags(CacheEnum::Addons())->flush();
                break;
            case 'product':
                Cache::tags(CacheEnum::Products())->flush();
                break;
            case 'fixed_price':
                Cache::tags([
                    CacheEnum::FixedPrices(),
                    CacheEnum::CompanyFixedPrices(),
                ])->flush();
                break;
        }

        // Clear other caches
        Cache::tags([
            CacheEnum::Subscriptions(),
            CacheEnum::CompanySubscriptions(),
            CacheEnum::Schedules(),
            CacheEnum::ScheduleDeviations(),
        ])->flush();
    }

    /**
     * Log end
     *
     * @param  PriceAdjustment  $priceAdjustment
     * @param  string  $info
     * @return void
     */
    private function logEnd($priceAdjustment, $info)
    {
        Log::channel('daily')->info($info, [
            'id' => $priceAdjustment->id,
            'type' => $priceAdjustment->type,
            'price_type' => $priceAdjustment->price_type,
            'price' => $priceAdjustment->price,
            'execution_date' => $priceAdjustment->execution_date,
        ]);
    }

    /**
     * Log error
     *
     * @param  PriceAdjustment  $priceAdjustment
     * @param  PriceAdjustmentRow  $row
     * @param  \Exception  $error
     * @return void
     */
    private function logError($priceAdjustment, $row, $error)
    {
        Log::channel('daily')->error('Price adjustment failed', [
            'price_adjustment' => [
                'id' => $priceAdjustment->id,
                'type' => $priceAdjustment->type,
                'price_type' => $priceAdjustment->price_type,
                'price' => $priceAdjustment->price,
                'execution_date' => $priceAdjustment->execution_date,
            ],
            'row' => [
                'id' => $row->id,
                'adjustable_type' => $row->adjustable_type,
                'adjustable_id' => $row->adjustable_id,
            ],
            'error' => $error->getMessage(),
        ]);
    }

    /**
     * Log start
     *
     * @param  PriceAdjustment  $priceAdjustment
     * @return void
     */
    private function logNotFound($priceAdjustment, $row)
    {
        Log::channel('daily')->error('Price adjustment row not found', [
            'price_adjustment' => [
                'id' => $priceAdjustment->id,
                'type' => $priceAdjustment->type,
                'price_type' => $priceAdjustment->price_type,
                'price' => $priceAdjustment->price,
                'execution_date' => $priceAdjustment->execution_date,
            ],
            'row' => [
                'id' => $row->id,
                'adjustable_type' => $row->adjustable_type,
                'adjustable_id' => $row->adjustable_id,
            ],
        ]);
    }
}
