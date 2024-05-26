<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Class UserRepository
 *
 * @author tolawho
 * @package App\Repositories
 */
class UserRepository extends Repository
{
    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new repository instance.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get an user by id
     *
     * @author tolawho
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        return $this->user->find($id);
    }

    /**
     * Get an app user by user_id column
     *
     * @author tolawho
     * @param $id
     * @return mixed
     */
    public function findByUserId($id)
    {
    	return $this->user->where('user_id', $id)->first()->toArray();
    }


    /**
     * Update user
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
     * Update or create new record by where condition
     *
     * @author tolawho
     * @param array $item
     * @param array $where
     * @return static
     */
    public function updateOrCreate($item, $where)
    {
        $record = $this->user->where($where)->first();
        if (is_null($record)) {
            return $this->store($item);
        }
        unset($item['created_at']);
        return $record->update($item);
    }

    /**
     * Create new user record
     *
     * @author sigmoswitch
     * @param $input
     * @return static
     */
    public function store($input)
    {
    	return $this->user->create($input);
    	//
    }  
}
