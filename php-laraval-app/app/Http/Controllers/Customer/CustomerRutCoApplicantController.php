<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\RutCoApplicant\CreateRutCoApplicantRequestDTO;
use App\DTOs\RutCoApplicant\PauseRutCoApplicantRequestDTO;
use App\DTOs\RutCoApplicant\RutCoApplicantResponseDTO;
use App\DTOs\RutCoApplicant\UpdateRutCoApplicantRequestDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\RutCoApplicant;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class CustomerRutCoApplicantController extends BaseUserController
{
    use ResponseTrait;

    /**
     *  Get customer RUT Co-applicant
     */
    public function jsonIndex(User $user): JsonResponse
    {
        if (! $user->hasRole(['Customer'])) {
            return $this->errorResponse(
                __('not found'),
                Response::HTTP_NOT_FOUND
            );
        }

        $queries = $this->getQueries(
            [
                'userId_eq' => $user->id,
            ],
        );
        $paginatedData = RutCoApplicant::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            RutCoApplicantResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        User $user,
        CreateRutCoApplicantRequestDTO $request
    ): RedirectResponse {
        $data = $request->toArray();

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,\App\Models\RutCoApplicant> */
        $coApplicants = $user->rutCoApplicants()
            ->withTrashed()
            ->get();
        $totalEnabledCoApplicants = $coApplicants->filter(
            fn (RutCoApplicant $coApplicant) => $coApplicant->is_enabled
        )->count();

        $existingCoApplicant = $coApplicants->first(
            fn (RutCoApplicant $coApplicant) => $coApplicant->identity_number === $data['identity_number']
        );

        if ($existingCoApplicant && ! $existingCoApplicant->trashed()) {
            return back()->with(
                'error',
                __('customer rut co applicant already exists')
            );
        }

        $phones = explode(' ', $request->phone);
        $dialCode = str_replace('+', '', $phones[0]);
        $isEnabled = $totalEnabledCoApplicants < 2;

        DB::transaction(function () use ($data, $user, $phones, $dialCode, $isEnabled, $existingCoApplicant) {
            // Reuse the deleted co-applicant if exists with the same identity number
            if ($existingCoApplicant) {
                $existingCoApplicant->update([
                    ...$data,
                    'phone' => $dialCode.$phones[1],
                    'dial_code' => $dialCode,
                    'is_enabled' => $isEnabled,
                    'deleted_at' => null,
                ]);
            } else {
                $user->rutCoApplicants()->create([
                    ...$data,
                    'phone' => $dialCode.$phones[1],
                    'dial_code' => $dialCode,
                    'is_enabled' => $isEnabled,
                ]);
            }
        });

        return back()->with('success', __('customer rut co applicant created successfully'));
    }

    /**
     * Enable the specified resource in storage.
     */
    public function enable(
        User $user,
        RutCoApplicant $rutCoApplicant,
    ): RedirectResponse {
        $totalEnabledCoApplicants = $user->rutCoApplicants()
            ->whereIsEnabled(true)
            ->count();

        if ($totalEnabledCoApplicants >= 2) {
            return back()->with(
                'error',
                __('customer rut co applicant enable limit reached')
            );
        }

        $rutCoApplicant->update([
            'is_enabled' => true,
        ]);

        return back()->with('success', __('customer rut co applicant enabled successfully'));
    }

    /**
     * Disable the specified resource in storage.
     */
    public function disable(
        User $user,
        RutCoApplicant $rutCoApplicant,
    ): RedirectResponse {
        $rutCoApplicant->update([
            'pause_start_date' => null,
            'pause_end_date' => null,
            'is_enabled' => false,
        ]);

        return back()->with('success', __('customer rut co applicant disabled successfully'));
    }

    /**
     * Pause the specified resource in storage.
     */
    public function pause(
        User $user,
        RutCoApplicant $rutCoApplicant,
        PauseRutCoApplicantRequestDTO $request,
    ): RedirectResponse {
        if (! $rutCoApplicant->is_enabled) {
            return back()->with(
                'error',
                __('customer rut co applicant not enabled')
            );
        }

        $rutCoApplicant->update($request->toArray());

        return back()->with('success', __('customer rut co applicant pause data updated successfully'));
    }

    /**
     * Unpause the specified resource in storage.
     */
    public function continue(
        User $user,
        RutCoApplicant $rutCoApplicant,
    ): RedirectResponse {
        $rutCoApplicant->update([
            'pause_start_date' => null,
            'pause_end_date' => null,
        ]);

        return back()->with('success', __('customer rut co applicant continued successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        User $user,
        RutCoApplicant $rutCoApplicant,
        UpdateRutCoApplicantRequestDTO $request,
    ): RedirectResponse {
        $data = $request->toArray();

        $isExists = $user->rutCoApplicants()
            ->whereIdentityNumber($data['identity_number'])
            ->where('id', '!=', $rutCoApplicant->id)
            ->exists();

        if ($isExists) {
            return back()->with(
                'error',
                __('customer rut co applicant already exists')
            );
        }

        $phones = $request->isNotOptional('phone') ? explode(' ', $request->phone) : [];
        $dialCode = $request->isNotOptional('phone') ?
            str_replace('+', '', $phones[0]) : $rutCoApplicant->dial_code;

        DB::transaction(function () use ($request, $rutCoApplicant, $phones, $dialCode, $data) {
            $rutCoApplicant->update([
                ...$data,
                'phone' => $request->isNotOptional('phone') ? $dialCode.$phones[1] : $rutCoApplicant->phone,
                'dial_code' => $dialCode,
            ]);
        });

        return back()->with('success', __('customer rut co applicant updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        User $user,
        RutCoApplicant $rutCoApplicant,
    ): RedirectResponse {
        $rutCoApplicant->softDelete();

        return back()->with('success', __('customer rut co applicant deleted successfully'));
    }
}
