<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\AttendanceTransaction\AttendanceTransactionDTO;
use App\DTOs\Fortnox\AttendanceTransaction\AttendanceTransactionRequestDTO;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait AttendanceTransactionResource
{
    /**
     * Create a new attendance transaction.
     */
    public function createAttendanceTransaction(
        AttendanceTransactionRequestDTO $data
    ): AttendanceTransactionDTO {
        $response = $this->sendRequest(
            'attendancetransactions',
            'POST',
            body: ['AttendanceTransaction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create attendance transaction',
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

        return AttendanceTransactionDTO::from($response->json('AttendanceTransaction'));
    }

    /**
     * Update an existing attendance transaction.
     */
    public function updateAttendanceTransaction(
        string $id,
        AttendanceTransactionRequestDTO $data
    ): AttendanceTransactionDTO {
        $response = $this->sendRequest(
            "attendancetransactions/$id",
            'PUT',
            body: ['AttendanceTransaction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update attendance transaction',
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

        return AttendanceTransactionDTO::from($response->json('AttendanceTransaction'));
    }

    /**
     * Sync existing work hour without fortnox_attendance_id to Fortnox.
     *
     * @param  \App\Models\WorkHour  $workHour
     */
    public function syncWorkHour($workHour): void
    {
        if (! $workHour->user->employee->fortnox_id) {
            return;
        }

        $startAt = $workHour->date->copy()->setTimeFromTimeString($workHour->start_time);
        $endAt = $workHour->date->copy()->setTimeFromTimeString($workHour->end_time);
        $hours = ceil($startAt->diffInMinutes($endAt) / 15) / 4;

        $response = $this->createAttendanceTransaction(
            AttendanceTransactionRequestDTO::from([
                'employee_id' => $workHour->user->employee->fortnox_id,
                'cause_code' => 'TID',
                'date' => $workHour->date->format('Y-m-d'),
                'hours' => $hours + $workHour->time_adjustment_hours,
            ])
        );

        if ($response) {
            $workHour->update(['fortnox_attendance_id' => $response->id]);
        }
    }
}
