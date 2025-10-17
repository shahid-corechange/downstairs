<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class CacheTableSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the information of all tables in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $DB_NAME = config('database.connections.mysql.database');

        $rows = DB::select("
            SELECT table_name as `table_name`, column_name as `column_name`, data_type as `data_type` 
            FROM information_schema.columns
            WHERe table_schema = '$DB_NAME'
        ");

        $data = [];

        foreach ($rows as $row) {
            if (! isset($data[$row->table_name])) {
                $data[$row->table_name] = [];
            }

            $data[$row->table_name][$row->column_name] = $row->data_type;
        }

        $content = '<?php return '.var_export($data, true).';';
        $path = base_path('bootstrap/cache/table_schema.php');

        // Write the content to the file
        file_put_contents($path, $content);

        $this->info('Table schema cache file generated successfully.');
    }
}
