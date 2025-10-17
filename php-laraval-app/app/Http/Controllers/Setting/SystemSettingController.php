<?php

namespace App\Http\Controllers\Setting;

use App\DTOs\Notification\NotificationGlobalSettingPayloadDTO;
use App\DTOs\Setting\GlobalSettingResponseDTO;
use App\DTOs\Setting\UpdateSystemSettingRequestDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\GlobalSettingTrait;
use App\Http\Traits\ResponseTrait;
use App\Jobs\BroadcastNotificationJob;
use App\Models\GlobalSetting;
use App\Models\ScheduleCleaning;
use App\Models\Team;
use Cache;
use DB;
use Inertia\Inertia;
use Inertia\Response;
use Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SystemSettingController extends Controller
{
    use GlobalSettingTrait;
    use ResponseTrait;

    /**
     * Setting that are used in the employee app
     */
    protected array $employeeAppSettings;

    /**
     * Setting that are used in the customer app
     */
    protected array $customerAppSettings;

    public function __construct()
    {
        $this->employeeAppSettings = [
            GlobalSettingEnum::RequestTimeoutInterval(),
            GlobalSettingEnum::ResendOtpCounter(),
            GlobalSettingEnum::StartJobMaxDistance(),
            GlobalSettingEnum::OtpLength(),
            GlobalSettingEnum::StartJobLateTime(),
            GlobalSettingEnum::EndJobLateTime(),
            GlobalSettingEnum::EndJobEarlyTime(),
        ];

        $this->customerAppSettings = [
            GlobalSettingEnum::MaxMonthShow(),
            GlobalSettingEnum::MaxBannerShow(),
            GlobalSettingEnum::RequestTimeoutInterval(),
            GlobalSettingEnum::ResendOtpCounter(),
            GlobalSettingEnum::CreditRefundTimeWindow(),
            GlobalSettingEnum::CreditMinutePerCredit(),
            GlobalSettingEnum::OtpLength(),
            GlobalSettingEnum::DefaultEmailSubject(),
            GlobalSettingEnum::UrlDownstairsSupport(),
            GlobalSettingEnum::UrlDownstairsPrivacyPolicy(),
            GlobalSettingEnum::UrlDownstairsTermsOfService(),
            GlobalSettingEnum::UrlDownstairsLegal(),
            GlobalSettingEnum::EmailCancelSubscription(),
            GlobalSettingEnum::MaxProductAddTime(),
        ];
    }

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1);
        $paginatedData = GlobalSetting::applyFilterSortAndPaginate($queries);

        return Inertia::render('SystemSetting/index', [
            'settings' => GlobalSettingResponseDTO::transformCollection($paginatedData->data),
            'teams' => $this->getTeams(),
            'refillSequences' => $this->getRefillSequences(),
        ]);
    }

    private function getTeams()
    {
        $onlys = ['id', 'name'];
        $teams = Team::selectWithRelations($onlys)->orderBy('name')->get();

        return TeamResponseDTO::collection($teams)->only(...$onlys);
    }

    private function getRefillSequences()
    {
        $refillSequences = [];
        foreach (SubscriptionRefillSequenceEnum::options() as $key => $value) {
            $refillSequences[$value] = __($key);
        }

        return $refillSequences;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSystemSettingRequestDTO $request)
    {
        $setting = GlobalSetting::where('key', $request->key)->first();

        if (! $setting) {
            throw new NotFoundHttpException();
        }

        DB::transaction(function () use ($setting, $request) {
            $this->updateAction($setting, $request);

            /** @var GlobalSetting $setting */
            $setting->update([
                'value' => $this->transformValue($request->value, $setting->type),
            ]);
        });

        $studlyKey = Str::studly(strtolower($request->key));

        if (in_array($studlyKey, $this->employeeAppSettings)) {
            $this->broadcastNotif(NotificationHubEnum::Employee(), $setting);
        }

        if (in_array($studlyKey, $this->customerAppSettings)) {
            $this->broadcastNotif(NotificationHubEnum::Customer(), $setting);
        }

        // Clear the cache
        Cache::forget('global_settings:'.Str::snake(strtolower($request->key)));

        return back()->with('success', __('system setting updated successfully'));
    }

    private function broadcastNotif(string $hub, GlobalSetting $setting)
    {
        scoped_localize('sv_SE', function () use ($setting, $hub) {
            BroadcastNotificationJob::dispatchAfterResponse(
                $hub,
                NotificationTypeEnum::SettingUpdated(),
                __('notification title setting updated'),
                __('notification body setting updated'),
                NotificationGlobalSettingPayloadDTO::from([
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $this->transform($setting->value, $setting->type),
                ])->toArray()
            );
        });
    }

    private function updateAction(GlobalSetting $setting, UpdateSystemSettingRequestDTO $request)
    {
        $key = Str::studly(Str::lower($setting->key));

        if ($key === GlobalSettingEnum::SubscriptionRefillSequence()) {
            $value = $this->transform($setting->value, $setting->type);

            if ($request->value !== $value) {
                $days = weeks_to_days($request->value);

                ScheduleCleaning::withTrashed()
                    ->where('start_at', '>', now()->addDays($days))
                    ->forceDelete();
            }
        }
    }
}
