<?php

namespace App\Services\LaundryOrder;

use App\Enums\LaundryOrder\LaundryOrderHistoryTypeEnum;
use App\Enums\TranslationEnum;
use App\Models\LaundryOrder;
use App\Models\User;
use Spatie\LaravelData\DataCollection;

class LaundryOrderHistoryService
{
    /**
     * Create a new history for the laundry order.
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  string  $type
     * @param  string  $note
     * @param  User|null  $causer
     */
    public function create($laundryOrder, $type, $note, $causer = null)
    {
        $causerId = $causer ? $causer->id : auth()->user()->id;

        $laundryOrder->histories()->create([
            'causer_id' => $causerId,
            'type' => $type,
            'note' => $note,
        ]);
    }

    /**
     * Add history when creating a laundry order.
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  DataCollection|Collection|null  $products
     * @param  User|null  $causer
     */
    public function addCreateHistory($laundryOrder, $products = null, $causer = null)
    {
        scoped_localize(TranslationEnum::Swedish(), function () use ($laundryOrder, $products, $causer) {
            $this->create($laundryOrder, LaundryOrderHistoryTypeEnum::Order, 'Order skapad', $causer);

            if ($products && $products->count() > 0) {
                $productsNote = $products->reduce(function ($carry, $product) {
                    return "{$carry} {$product['quantity']} x {$product['name']}, ";
                }, 'Produkter tillagda: ');

                $this->create($laundryOrder, LaundryOrderHistoryTypeEnum::Product, $productsNote, $causer);
            }
        });
    }

    /**
     * Add history when updating a laundry order.
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  DataCollection|null  $products
     * @param  User|null  $causer
     */
    public function addUpdateHistory($laundryOrder, $products, $causer = null)
    {
        scoped_localize(TranslationEnum::Swedish(), function () use ($laundryOrder, $products, $causer) {
            $this->create($laundryOrder, LaundryOrderHistoryTypeEnum::Order, 'Order uppdaterad', $causer);

            if ($products && $products->count() > 0) {
                $productsNote = $products->reduce(function ($carry, $product) {
                    return "{$carry} {$product['quantity']} x {$product['name']}, ";
                }, '');

                // search the product note to prevent duplicate history
                $existingHistory = $laundryOrder->histories()
                    ->where('type', LaundryOrderHistoryTypeEnum::Product)
                    ->where('note', 'LIKE', "%{$productsNote}%")
                    ->first();

                if (! $existingHistory) {
                    $productsNote = 'Uppdaterade produkter: '.$productsNote;
                    $this->create($laundryOrder, LaundryOrderHistoryTypeEnum::Product, $productsNote, $causer);
                }
            }
        });
    }
}
