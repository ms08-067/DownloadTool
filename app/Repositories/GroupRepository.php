<?php

namespace App\Repositories;

use App\Models\Group;

/**
 * Class GroupRepository
 * @author tolawho
 * @package App\Repositories
 */
class GroupRepository extends Repository
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * Create a new repository instance.
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Get list group
     * @author tolawho
     * @return array
     */
    public function getAll()
    {
        return $this->group->getAll();
    }
    
    /**
     * Get list group that are active
     * @author tolawho
     * @return array
     */
    public function getAllActive()
    {
        return $this->group->getAllActive();
    }

    /**
     * Get list company_position types
     *
     * @author sigmoswitch
     * @return array
     */
    public function getList()
    {
    	return $this->group->getList()->toArray();
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
    	return $this->group->where('id', $id)->first();
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
    	return $this->group->create($input);
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
    	return $this->group->where('id', $input['id'])->update($input);
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
    	$record = $this->group->where($where)->first();
        //echo '<pre>';print_r($record);echo '</pre>';die();
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
