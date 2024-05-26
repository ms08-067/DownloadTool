<?php

namespace App\Console\Commands;

use App\Repositories\S3Repository;
use Illuminate\Console\Command;

class S3JobInitiateSpecialExampleZipNotPresentFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:download_special {caseId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiate special example zip not present function';
    protected $s3Repository;

    /**
     * S3JobInitiateSpecialExampleZipNotPresentFunction constructor.
     *
     * @param S3Repository $s3Repository
     */
    public function __construct(S3Repository $s3Repository)
    {
        parent::__construct();

        $this->s3Repository = $s3Repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $caseId = $this->argument('caseId');
        if($caseId){
            $customer_number = $this->s3Repository->get_customer_number_from_xml($caseId, false);
            dump('$customer_number == ' . $customer_number);
            if($customer_number == '132558' || $customer_number == '200070' || $customer_number == '200219'){
                dump('special example zip not present function for roof tile customer');
                $this->s3Repository->initiate_special_example_zip_not_present_function($caseId);
            }
        }
    }
}
