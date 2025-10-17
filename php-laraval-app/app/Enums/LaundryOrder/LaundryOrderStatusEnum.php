<?php

namespace App\Enums\LaundryOrder;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: created, in_progress_pickup, in_progress_store,
 * in_progress_laundry, in_progress_delivery, delivered, picked_up, invoiced, done
 */
enum LaundryOrderStatusEnum: string
{
    use InvokableCases;
    use Values;

    /**
     * When the order is pending and not started yet.
     */
    case Pending = 'pending';

    /**
     * When the order is in progress pickup from the customer by the pickup team.
     */
    case InProgressPickup = 'in_progress_pickup';

    /**
     * When the order is picked up from the customer.
     */
    case PickedUp = 'picked_up';

    /**
     * When the order is in progress store.
     */
    case InProgressStore = 'in_progress_store';

    /**
     * When the order is in progress laundry at the store.
     */
    case InProgressLaundry = 'in_progress_laundry';

    /**
     * When the order is in progress delivery to the customer by the delivery team.
     */
    case InProgressDelivery = 'in_progress_delivery';

    /**
     * When the order is delivered to the customer.
     */
    case Delivered = 'delivered';

    /**
     * When the schedule is done.
     */
    case Done = 'done';

    /**
     * When the order is paid.
     */
    case Paid = 'paid';

    /**
     * When the order is closed and the order cannot be modified.
     */
    case Closed = 'closed';
}
