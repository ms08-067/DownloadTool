<?php

namespace App\Repositories;

use App\Models\SiteDeveloper;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers;
use Exception;
use Session;
use Debugbar;

/**
 * Class SiteDeveloperRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class SiteDeveloperRepository extends Repository
{
    /**
     * @var SiteDeveloper
     */
    protected $sitedeveloper;

    /**
     * Create a new repository instance.
     * @param SiteDeveloper $sitedeveloper
     */
    public function __construct(
    	SiteDeveloper $sitedeveloper
    ){
    	$this->sitedeveloper = $sitedeveloper;
    }

    /**
     * Get all any
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
    	return $this->sitedeveloper->getAll();
    	//
    }

    /**
     * Get all 
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getSiteDeveloperArray()
    {
    	return $this->sitedeveloper->getSiteDeveloperArray();
    	//
    }
}
