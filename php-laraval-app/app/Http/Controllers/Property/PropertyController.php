<?php

namespace App\Http\Controllers\Property;

use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Property\UpdatePropertyRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Property\PropertyStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\MetaTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Address;
use App\Models\City;
use App\Models\Customer;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\Schedule;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    use MetaTrait;
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'address.city.country',
        'keyInformation',
        'type',
        'users',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'address.fullAddress',
        'address.id',
        'address.cityId',
        'address.address',
        'address.postalCode',
        'address.latitude',
        'address.longitude',
        'address.city.name',
        'address.city.countryId',
        'address.city.country.name',
        'users.id',
        'users.fullname',
        'keyInformation.keyPlace',
        'keyInformation.frontDoorCode',
        'keyInformation.alarmCodeOff',
        'keyInformation.alarmCodeOn',
        'keyInformation.information',
        'type.name',
        'squareMeter',
        'keyDescription',
        'meta.note',
        'status',
        'deletedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            filter: [
                'membershipType_eq' => MembershipTypeEnum::Private(),
            ],
            defaultFilter: [
                'status_eq' => PropertyStatusEnum::Active(),
            ],
            pagination: 'page',
            show: 'all'
        );
        $paginatedData = Property::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Property/Overview/index', [
            'properties' => PropertyResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            filter: [
                'membershipType_eq' => MembershipTypeEnum::Private(),
            ],
        );
        $paginatedData = Property::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            PropertyResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequestDTO $request, Property $property): RedirectResponse
    {
        $isExists = Customer::where('address_id', $property->address_id)->exists();

        DB::transaction(function () use ($request, $property, $isExists) {
            // update key place
            if ($request->isNotOptional('key_information')
                && $request->key_information->isNotOptional('key_place')
                && $request->key_information->key_place !== $property->key_place) {
                KeyPlace::where('property_id', $property->id)->update(['property_id' => null]);

                if ($request->key_information->key_place) {
                    KeyPlace::where('id', $request->key_information->key_place)
                        ->update(['property_id' => $property->id]);
                }

                KeyPlace::createKeyPlaceIfFull();
            }

            $oldKeyDescription = $property->key_description;

            $property->update([
                ...$request->toArray(),
                'key_information' => $request->isNotOptional('key_information') ?
                    $request->key_information : null,
            ]);

            if ($oldKeyDescription !== $property->key_description) {
                // Update in progress and future bookings with the new key description
                Schedule::Booked()
                    ->where('property_id', $property->id)
                    ->update(['key_information' => $property->key_description]);
            }

            if ($request->isNotOptional('meta')) {
                if (($request->meta['note'] ?? '') !== $property->getMeta('note', '')) {
                    // Update in progress and future bookings with the new note
                    Schedule::Booked()
                        ->where('property_id', $property->id)
                        ->update(['note->property_note' => $request->meta['note']]);
                }

                $property->purgeMeta();
                $property->saveMeta($request->meta);
            }

            // if address is new, create new address
            if ($isExists) {
                $city = City::find($request->address->city_id);
                $address = $request->address->address;
                $postalCode = $request->address->postal_code;
                $requestAddress = "{$address}, {$city->name}, {$postalCode}, {$city->country->name}";

                if ($requestAddress !== $property->address->fullAddress) {
                    $address = Address::create($request->address->toArray());
                    $property->update(['address_id' => $address->id]);
                } else {
                    $property->address()->update($request->address->toArray());
                }
            } else {
                $property->address()->update($request->address->toArray());
            }
        });

        return back()->with('success', __('property updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $exists = $property->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('schedules', function ($query) {
                    $query->booked();
                })
                    ->orWhereNull('deleted_at');
            })
            ->exists();

        if ($exists) {
            return back()->with('error', __('property still used in schedules or subscriptions'));
        }

        DB::transaction(function () use (&$property) {
            $property->update([
                'status' => PropertyStatusEnum::Inactive(),
                'key_information' => [
                    ...$property->key_information,
                    'key_place' => null,
                ],
            ]);
            KeyPlace::where('property_id', $property->id)
                ->update(['property_id' => null]);
            $property->delete();
        });

        return back()->with('success', __('property deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Property $property): RedirectResponse
    {
        DB::transaction(function () use (&$property) {
            $property->update(['status' => PropertyStatusEnum::Active()]);
            $property->restore();
        });

        return back()->with('success', __('property restored successfully'));
    }
}
