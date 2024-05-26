<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RocketChatAuthToken
 *
 * @author sigmoswitch
 * @package App\Models
 */
class RocketChatAuthToken extends Model
{
    protected $table = 'rocket_chat_auth_tokens';
	protected $fillable = ['rc_username', 'x_auth_token', 'x_user_id'];

	/**
     * Get all 
     *
     * @author sigmoswitch
     * @return mixed
     */
	public function getAll()
	{
		return self::select('*')->get()->toArray();
	}
}
