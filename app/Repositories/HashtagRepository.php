<?php

namespace App\Repositories;

use App\Models\Hashtag;

/**
 * Class HashtagRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class HashtagRepository extends Repository
{
    /**
     * @var Hashtag
     */
    protected $hashtag;

    /**
     * Create a new repository instance.
     * @param Hashtag $hashtag
     */
    public function __construct(Hashtag $hashtag)
    {
        $this->hashtag = $hashtag;
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
        return $this->hashtag->where('id', $id)->first();
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
        return $this->hashtag->where('id', $input['id'])->update($input);
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
        return $this->hashtag->create($input);
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
        $record = $this->hashtag->where($where)->first();
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
