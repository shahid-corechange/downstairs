<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum CacheEnum: string
{
    use InvokableCases;
    use Values;

    case GlobalSettings = 'global-settings';
    case Options = 'options';
    case OptionsDescription = 'options-description';
    case Areas = 'areas';
    case Cities = 'cities';
    case Countries = 'countries';
    case Users = 'users';
    case Customers = 'customers';
    case Credits = 'credits';
    case ScheduleCleanings = 'schedule-cleanings';
    case Schedules = 'schedules';
    case ChangeRequests = 'change-requests';
    case ScheduleEmployees = 'schedule-employees';
    case Notifications = 'notifications';
    case Properties = 'properties';
    case Addresses = 'addresses';
    case Settings = 'settings';
    case Teams = 'teams';
    case Services = 'services';
    case Products = 'products';
    case Addons = 'addons';
    case CustomerProperties = 'customer-properties';
    case CustomerAddresses = 'customer-addresses';
    case Deviations = 'deviations';
    case Geocodes = 'geocodes';
    case Roles = 'roles';
    case Subscriptions = 'subscriptions';
    case Orders = 'orders';
    case Invoices = 'invoices';
    case Employees = 'employees';
    case ActivityLogs = 'activity-logs';
    case AuthLogs = 'auth-logs';
    case Feedbacks = 'feedbacks';
    case Companies = 'companies';
    case CompanyProperties = 'company-properties';
    case CompanyAddresses = 'company-addresses';
    case CompanyUsers = 'company-users';
    case CompanySubscriptions = 'company-subscriptions';
    case CompanyDiscounts = 'company-discounts';
    case FixedPrices = 'fixed-prices';
    case CompanyFixedPrices = 'company-fixed-prices';
    case CustomerDiscounts = 'customer-discounts';
    case ServiceQuarters = 'service-quarters';
    case KeyPlaces = 'key-places';
    case RutCoApplicants = 'rut-co-applicants';
    case WorkHours = 'work-hours';
    case UnassignSubscriptions = 'unassign-subscriptions';
    case LeaveRegistrations = 'leave-registrations';
    case TimeAdjustments = 'time-adjustments';
    case PriceAdjustments = 'price-adjustments';
    case Stores = 'stores';
    case Categories = 'categories';
    case LaundryOrders = 'laundry-orders';
    case LaundryOrderProducts = 'laundry-order-products';
    case LaundryOrderSchedules = 'laundry-order-schedules';
    case StoreSales = 'store-sales';
    case LaundryPreferences = 'laundry-preferences';
    case Blockdays = 'blockdays';
    case CashierAttendances = 'cashier-attendances';
    case ScheduleDeviations = 'schedule-deviations';
}
