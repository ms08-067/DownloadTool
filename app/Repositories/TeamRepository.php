<?php

namespace App\Repositories;

use App\Models\Team;

/**
 * Class TeamRepository
 * @author tolawho
 * @package App\Repositories
 */
class TeamRepository extends Repository
{
    /**
     * @var Team
     */
    protected $team;

    /**
     * Create a new repository instance.
     * @param Team $team
     */
    public function __construct(Team $team) 
    {
    
        $this->team = $team;
    }

    /**
     * Get list team
     * @author tolawho
     * @return array
     */
    public function getAll()
    {
        return $this->team->getAll();
    }
   
    /**
     * Get list team that are active
     * @author tolawho
     * @return array
     */
    public function getAllActive()
    {
        return $this->team->getAllActive();
    }

    /**
     * Get list company_position types
     *
     * @author sigmoswitch
     * @return array
     */
    public function getList()
    {
    	return $this->team->getList()->toArray();
    }

    /**
     * Get profile detail by id
     *
     * @author sigmoswitch
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
    	return $this->team->where('id', $id)->first();
    }

    /**
     * Create new expense record
     *
     * @author sigmoswitch
     * @param $input
     * @return static
     */
    public function store($input)
    {
    	return $this->team->create($input);
    	/***/
    }

    /**
     * Update contract detail
     *
     * @author sigmoswitch
     * @param $input
     * @return mixed
     */
    public function update($input)
    {
    	return $this->team->where('id', $input['id'])->update($input);
    	/***/
    }

    /**
     * Update or create new record by where condition
     *
     * @author sigmoswitch
     * @param array $item
     * @param array $where
     * @return static
     */
    public function updateOrCreate($item, $where)
    {
    	$record = $this->team->where($where)->first();
        /**echo '<pre>';print_r($record);echo '</pre>';die();*/
    	if (is_null($record)) {
    		return $this->store($item);
    	}
    	unset($item['created_at']);
    	return $record->update($item);
    }

    /**
     * Delete a contract type by id
     *
     * @author sigmoswitch
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
    	return $this->findById($id)->delete();
    }     
}
