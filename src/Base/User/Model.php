<?php

namespace MCMIS\Foundation\Base\User;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use MCMIS\Foundation\Traits\User\AttributesTrait;
use MCMIS\Foundation\Traits\User\DepartmentTrait;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class Model extends Authenticatable
{

    use SoftDeletes, EntrustUserTrait {

        SoftDeletes::restore as SoftDeleteRestore;
        EntrustUserTrait::restore as EntrustRestore;
    }

    use AttributesTrait;
    use DepartmentTrait;

    protected $dates = ['updated_at', 'created_at', 'deleted_at'];

    protected $fillable = [
        'name', 'email', 'password',
        'address', 'mobile', 'curp', 'gender', 'age', 'creator_id',
        'disability', 'disability_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        /** TODO: Transform into observer. */
        parent::boot();

        static::creating(function ($post) {
            if (!empty($post->attributes['mobile']) && empty($post->attributes['email']))
                $post->attributes['email'] = $post->attributes['mobile'] . '@acciones-bj.org';

            if (Auth::check())
                $post->attributes['creator_id'] = Auth::user()->id;

        });

        static::created(function ($user) {
            $role = app('model.user.role')->where('name', 'reader')->first();
            if ($role !== null) $user->attachRole($role->id);
        });
    }

    public function restore()
    {
        $this->SoftDeleteRestore();
        Cache::tags(config('entrust.role_user_table'))->flush();
    }

    public function avatar()
    {
        return $this->belongsToMany(app('model.avatar'), 'avatar_user');
    }

    public function employee()
    {
        return $this->belongsToMany(app('model.company.employee'), 'employee_user');
    }

    public function complaints()
    {
        return $this->hasMany(app('model.complain'));
    }

    public function notices()
    {
        return $this->belongsToMany(app('model.notice'), 'user_notice_receivers')->withPivot('seen', 'id');
    }

}