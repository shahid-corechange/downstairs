<?php

namespace App\Enums\Azure\BlobStorage;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: images, logs
 */
enum BlobStorageContainerEnum: string
{
    use InvokableCases;
    use Values;

    case Images = 'images';
    case Logs = 'logs';
}
