<?php

namespace App\Console\Commands;

use App\Contracts\StorageService;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use File;
use Illuminate\Console\Command;

class BackupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup all logs to Azure Blob Storage';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageservice)
    {
        $schedulerLogs = File::files(storage_path('logs'));
        $sharedLogs = File::allFiles(storage_path('share/logs'));

        $date = now()->format('Y-m-d');

        foreach ($schedulerLogs as $log) {
            $storageservice->upload(
                BlobStorageContainerEnum::Logs(),
                BlobStorageUploadSourceEnum::Local(),
                "logs/{$log->getRelativePathname()}",
                "$date/scheduler/{$log->getRelativePathname()}",
            );
        }

        foreach ($sharedLogs as $log) {
            $storageservice->upload(
                BlobStorageContainerEnum::Logs(),
                BlobStorageUploadSourceEnum::Local(),
                "share/logs/{$log->getRelativePathname()}",
                "$date/{$log->getRelativePathname()}",
            );
        }

        // At the end of today, delete all logs in the storage
        if (now()->hour === 23) {
            File::cleanDirectory(storage_path('logs'));
            File::cleanDirectory(storage_path('share/logs'));
        }
    }
}
