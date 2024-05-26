<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Team
 *
 * @author tolawho
 * @package App\Models
 */
class Team extends Model
{
    
    protected $guarded = ['id'];

    protected $table = 'company_teams';

    /**
     * Get all teams
     *
     * @author tolawho
     * @return mixed
     */
    public function getAll()
    {
        return self::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray();
    }

    /**
     * Get all teams
     *
     * @author tolawho
     * @return mixed
     */
    public function getAllActive()
    {
        return self::select('id', 'name')->where('active', 1)->orderBy('name')->get()->pluck('name', 'id')->toArray();
    }    
}
