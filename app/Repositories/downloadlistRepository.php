<?php

namespace App\Repositories;

use App\Models\AdvancePayment;

/**
 * Class downloadlistRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class downloadlistRepository extends Repository
{

    /**
     * @var AdvancePayment
     */
    protected $advancepayment;

    /**
     * Create new repository instance.
     *
     * @param AdvancePayment $advancepayment
     */
    public function __construct(AdvancePayment $advancepayment)
    {
    	$this->advancepayment = $advancepayment;
    }

    /**
     * Get all timesheetrecordtypes
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return $this->advancepayment->getAll();
    }

    /**
     * Get list timesheetrecordtypes types
     *
     * @author sigmoswitch
     * @return array
     */
    public function getList()
    {
    	return $this->advancepayment->getList()->toArray();
    }

    /**
     * Get entirely all timesheet record types every column
     * @author sigmoswitch
     * @return array
     */
    public function getEntirelyAll()
    {
    	return $this->advancepayment->getEntirelyAll();
    }
    
    /**
     * Get entirely all timesheet record types every column
     * @author sigmoswitch
     * @return array
     */
    public function getEntirelyAllThatHavePenalties()
    {
    	return $this->advancepayment->getEntirelyAllThatHavePenalties();
    }

    /**
     * Get all timesheetrecordtypes
     * @author sigmoswitch
     * @return mixed
     */
    public function getAllwithValue()
    {
        return $this->advancepayment->getAllwithValue();
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
    	return $this->advancepayment->where('id', $id)->first();
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
    	return $this->advancepayment->where('id', $input['id'])->update($input);
    	/***/
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
    	return $this->advancepayment->create($input);
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
    	$record = $this->advancepayment->where($where)->first();
        /**$record*/
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
