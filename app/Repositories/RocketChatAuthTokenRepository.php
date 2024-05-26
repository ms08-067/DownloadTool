<?php

namespace App\Repositories;

use App\Models\RocketChatAuthToken;

/**
 * Class RocketChatAuthTokenRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class RocketChatAuthTokenRepository extends Repository
{
    /**
     * @var RocketChatAuthToken
     */
    protected $rocketchatauthtoken;

    /**
     * Create a new repository instance.
     * @param RocketChatAuthToken $rocketchatauthtoken
     */
    public function __construct(RocketChatAuthToken $rocketchatauthtoken)
    {
        $this->rocketchatauthtoken = $rocketchatauthtoken;
    }

    /**
     * Get by rc_username
     *
     * @author sigmoswitch
     * @param $id
     * @return mixed
     */
    public function findByUsername($rc_username)
    {
        return $this->rocketchatauthtoken->where('rc_username', $rc_username);
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
        return $this->findByUsername($input['rc_username'])->update($input);
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
        $record = $this->rocketchatauthtoken->where($where)->first();
        /**dd($record);*/
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
    	return $this->rocketchatauthtoken->create($input);
    	//
    }  
}
