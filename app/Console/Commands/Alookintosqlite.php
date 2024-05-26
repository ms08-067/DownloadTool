<?php

namespace App\Console\Commands;

//use App\Repositories\someRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Schema;

class Alookintosqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alookintosqlite:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan sqlite table from command line.';
    //protected $someRepository;

    /**
     * Alookintosqlite constructor.
     * @param someRepository $someRepository
     */
    public function __construct(
        //someRepository $someRepository
    )
    {
        parent::__construct();

        //$this->someRepository = $someRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->someRepository->inner_function();

        $show_tables_with_column_details = DB::select('SELECT * FROM sqlite_master WHERE type="table"');
        /**dd($show_tables_with_column_details);*/
        $show_columns = [];
        foreach($show_tables_with_column_details as $item_number_key => $item_details){
            $show_columns[$item_number_key] = DB::select('PRAGMA table_info('.$item_details->tbl_name.')');
        }
        dd($show_columns);
    }
}
