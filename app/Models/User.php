<?php

namespace App\Models;

//use Adldap\Laravel\Traits\HasLdapUser;
use LdapRecord\Laravel\Auth\HasLdapUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @author tolawho
 * @package App\Models
 */

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens;
    use SoftDeletes;
    use Notifiable;
    use HasLdapUser;
    use HasFactory;
    use AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $protected = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**protected $fillable = ['name', 'email', 'password',];*/

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getLdapDomainColumn(): string
    {
        return 'domain';
    }

    public function getLdapGuidColumn(): string
    {
        return 'objectguid';
    }

    /**
     * Overrides the method to ignore the remember token.
     */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
            parent::setAttribute($key, $value);
        }
    }

    /**
     * Scope a query to only include active users.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeActive($query)
    {
        return $query->where('status', 1);

    }
    
    /**
     * Scope a query to only include inactive users.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeInActive($query)
    {
        return $query->where('status', 0);
    }    
}
