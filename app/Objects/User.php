<?php

namespace App\Objects;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'admin', 'active'
    ];

    protected $attributes = [
        'admin' => false,
        'active' => true
    ];

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

    public function prompt_paths()
    {
        return $this->belongsToMany('App\Objects\PromptPath')
                ->using('App\Objects\PathPromptUser')
                ->withPivot([ 'write_access' ]);
    }

    public function getPaths()
    {
        $paths = $this->prompt_paths;
        foreach ($paths as $path) {
            $note = $path->pivot->write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }
}
