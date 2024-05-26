<?php

namespace App\Repositories;

use App\Models\RoleHasPermission;

/**
 * Class RoleHasPermissionRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class RoleHasPermissionRepository extends Repository
{
    /**
     * @var RoleHasPermission
     */
    protected $rolehaspermission;

    /**
     * RoleHasPermissionRepository constructor.
     * @param RoleHasPermission $rolehaspermission
     */
    public function __construct(
    	RoleHasPermission $rolehaspermission
    ){
        $this->rolehaspermission = $rolehaspermission;
    }

    /**
     * Get all role has permission
     *
     * @author sigmoswitch
     * @return array
     */
    public function getAll()
    {
        return $this->rolehaspermission->getAll();
    }


   /**
     * Get timesheet by id
     *
     * @author tolawho
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
    	return $this->rolehaspermission->find($id);
    }

    /**
     * Create new timesheet record
     *
     * @author tolawho
     * @param $input
     * @return static
     */
    public function store($input)
    {
    	return $this->rolehaspermission->create($input);
    }

    /**
     * Save multiple record timesheets
     *
     * @author tolawho
     * @param $data
     * @return mixed
     */
    public function insert($data)
    {
    	return $this->rolehaspermission->insert($data);
    }

    /**
     * Update timesheet
     *
     * @author tolawho
     * @param $input
     * @return mixed
     */
    public function update($input)
    {
    	return $this->findById($input['id'])->update($input);
    }

    /**
     * Delete an expense record by id
     *
     * @author tolawho
     * @param int|array $id
     * @return mixed
     */
    public function delete($id)
    {
    	return $this->findById($id)->delete();
    }


    /**
     * Delete an expense record by id
     *
     * @author tolawho
     * @param int|array $id
     * @return mixed
     */
    public function deleteWhere($where)
    {
    	$record = $this->rolehaspermission->where($where)->first();
    	// if it finds then it will need to remove this row 
    	if (is_null($record)) {
    		/** there is a problem here because it should not be possible even in the first place */
    		return false;
    	} else {
    		return $record->delete($record->id);
    	}
    }

    /**
     * Update or create new record by where condition
     *
     * @author tolawho
     * @param array $item
     * @param array $where
     * @return static
     */
    public function updateOrCreate($item, $where)
    {
    	$record = $this->rolehaspermission->where($where)->first();
    	if (is_null($record)) {
    		return $this->store($item);
    	} else {
	    	return $record->update($item);
    	}
    }

    /**
     * Update or create many record
     *
     * @author tolawho
     * @param array $data
     * @return bool
     */
    public function updateOrCreateMany($data)
    {
    	foreach ($data as $item) :
    		$this->updateOrCreate($item, ['fk_user_id' => $item['fk_user_id'], 'record_date' => $item['record_date']]);
    	endforeach;

    	return true;
    }    
}
