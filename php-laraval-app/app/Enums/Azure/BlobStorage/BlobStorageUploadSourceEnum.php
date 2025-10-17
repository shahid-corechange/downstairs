<?php

namespace App\Enums\Azure\BlobStorage;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: local, request
 */
enum BlobStorageUploadSourceEnum: string
{
    use InvokableCases;
    use Values;

    case Local = 'local';
    case Request = 'request';
}
