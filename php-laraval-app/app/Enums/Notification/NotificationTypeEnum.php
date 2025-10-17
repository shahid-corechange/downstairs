<?php

namespace App\Enums\Notification;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: CREDIT_REFUND, SCHEDULE_START, SCHEDULE_END,
 * SCHEDULE_CANCEL, SCHEDULE_UPDATED, UPCOMING_SCHEDULE_START,
 * UPCOMING_SCHEDULE_END, START_SCHEDULE_LATE, END_SCHEDULE_LATE,
 * CHANGE_REQUEST_APPROVED, CHANGE_REQUEST_REJECTED
 */
enum NotificationTypeEnum: string
{
    use InvokableCases;
    use Values;

    case CreditRefund = 'CREDIT_REFUND';
    case SubscriptionAdded = 'SUBSCRIPTION_ADDED';
    case SubscriptionUpdated = 'SUBSCRIPTION_UPDATED';
    case SubscriptionPaused = 'SUBSCRIPTION_PAUSED';
    case SubscriptionContinued = 'SUBSCRIPTION_CONTINUED';
    case ScheduleStart = 'SCHEDULE_START';
    case ScheduleEnd = 'SCHEDULE_END';
    case ScheduleCancel = 'SCHEDULE_CANCEL';
    case ScheduleUpdated = 'SCHEDULE_UPDATED';
    case ScheduleAdded = 'SCHEDULE_ADDED';
    case ScheduleDeleted = 'SCHEDULE_DELETED';
    case UpcomingScheduleStart = 'UPCOMING_SCHEDULE_START';
    case UpcomingScheduleEnd = 'UPCOMING_SCHEDULE_END';
    case StartScheduleLate = 'START_SCHEDULE_LATE';
    case EndScheduleLate = 'END_SCHEDULE_LATE';
    case ChangeRequestApproved = 'CHANGE_REQUEST_APPROVED';
    case ChangeRequestRejected = 'CHANGE_REQUEST_REJECTED';
    case SettingUpdated = 'SETTING_UPDATED';
    case OrderLaundryCreated = 'ORDER_LAUNDRY_CREATED';
    case OrderLaundryUpdated = 'ORDER_LAUNDRY_UPDATED';
}
