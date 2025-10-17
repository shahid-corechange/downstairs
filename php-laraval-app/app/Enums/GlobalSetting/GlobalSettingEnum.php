<?php

namespace App\Enums\GlobalSetting;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: MaxMonthShow, MaxBannerShow, RequestTimeoutInterval,
 * ResendOtpCounter, CreditRefundTimeWindow, CreditMinutePerCredit,
 * StartJobMaxDistance, OtpLength, StartJobLateTime, EndJobLateTime,
 * ScheduleStartReminderMinutes, ScheduleEndReminderMinutes,
 * DefaultEmailSubject, InvoiceSentDate, InvoiceDueDays,
 * CreditExpirationDays, SubscriptionRefillSequence, UrlDownstairsSupport,
 * UrlDownstairsPrivacyPolicy, UrlDownstairsTermsOfService, UrlDownstairsLegal,
 * EmailCancelSubscription, EndJobEarlyTime, DefaultShownTeam,
 * DefaultMinHourShow, DefaultMaxHourShow
 */
enum GlobalSettingEnum: string
{
    use InvokableCases;
    use Values;

    case MaxMonthShow = 'MaxMonthShow'; // app customer
    case MaxBannerShow = 'MaxBannerShow'; // app customer
    case RequestTimeoutInterval = 'RequestTimeoutInterval'; // app customer & employee
    case ResendOtpCounter = 'ResendOtpCounter'; // app customer & employee
    case CreditRefundTimeWindow = 'CreditRefundTimeWindow'; // app customer
    case CreditMinutePerCredit = 'CreditMinutePerCredit'; // app customer & backend
    case CreditExpirationDays = 'CreditExpirationDays'; // backend
    case StartJobMaxDistance = 'StartJobMaxDistance'; // app employee
    case OtpLength = 'OtpLength'; // all
    case StartJobLateTime = 'StartJobLateTime'; // app employee & backend
    case EndJobLateTime = 'EndJobLateTime'; // app employee & backend
    case ScheduleStartReminderMinutes = 'ScheduleStartReminderMinutes'; // backend
    case ScheduleEndReminderMinutes = 'ScheduleEndReminderMinutes'; // backend
    case DefaultEmailSubject = 'DefaultEmailSubject'; // app customer
    case InvoiceSentDate = 'InvoiceSentDate'; // backend
    case InvoiceDueDays = 'InvoiceDueDays'; // backend
    case SubscriptionRefillSequence = 'SubscriptionRefillSequence'; // bakcend
    case UrlDownstairsSupport = 'UrlDownstairsSupport'; // app customer
    case UrlDownstairsPrivacyPolicy = 'UrlDownstairsPrivacyPolicy'; // app customer
    case UrlDownstairsTermsOfService = 'UrlDownstairsTermsOfService'; // app customer
    case UrlDownstairsLegal = 'UrlDownstairsLegal'; // app customer
    case EmailCancelSubscription = 'EmailCancelSubscription'; // app customer
    case EndJobEarlyTime = 'EndJobEarlyTime'; // app employee & backend
    case DefaultShownTeam = 'DefaultShownTeam'; // portal
    case DefaultMinHourShow = 'DefaultMinHourShow'; // portal
    case DefaultMaxHourShow = 'DefaultMaxHourShow'; // portal
    case AbsenceRescheduling = 'AbsenceRescheduling'; // portal
    case MaxProductAddTime = 'MaxProductAddTime'; // app customer
}
