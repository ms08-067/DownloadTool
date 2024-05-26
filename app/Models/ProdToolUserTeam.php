<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProdToolUserTeam
 *
 * @author sigmoswitch
 * @package App\Models
 */
class ProdToolUserTeam extends Model
{
    protected $connection = 'prodtool';
    protected $table = 'prodtool_1910.user_teams';

    /**
     * Get all teams
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return self::select('user_id', 'team_id')->get()->toArray();
    }

    /**
     * Get entirely all user from prodtool
     *
     * @author sigmoswitch
     * @return array
     */
    public function getEntirelyAll()
    {
    	return self::select('*')->get()->keyBy('user_id')->toArray();
    }
}
