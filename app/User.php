<?php

namespace Avem;

use Avem\Notifiable as AppNotifiable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements AppNotifiable
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'surname', 'email',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at', 'updated_at',
	];

	public function directNotifications()
	{
		return $this->morphMany('Avem\Notification', 'notifiable');
	}

	public function filedClaims()
	{
		return $this->hasMany('Avem\Claim');
	}

	public function getIsActiveAttribute()
	{
		return $this->renewals()->active()->exists();
	}

	public function getNotifiableReceiversAttribute()
	{
		return [$this];
	}

	public function hasPermission($name)
	{
		$this->load('roles.permissions');
		foreach ($this->roles as $role) {
			if ($role->permissions->contains('name', $name))
				return true;
		}
		return false;
	}

	public function inscribedActivities()
	{
		return $this->belongsToMany('Avem\Activity');
	}

	public function inscribedActivityTasks()
	{
		return $this->belongsToMany('Avem\ActivityTask', 'activity_task_user_all');
	}

	public function mbMember()
	{
		return $this->hasOne('Avem\MbMember', 'id');
	}

	public function notificationReceipts()
	{
		return $this->hasMany('Avem\NotificationReceipt');
	}

	public function ownRoles()
	{
		return $this->belongsToMany('Avem\Role');
	}

	public function renewals()
	{
		return $this->hasMany('Avem\Renewal');
	}

	public function roles()
	{
		return $this->belongsToMany('Avem\Role', 'all_user_roles');
	}

	public function selfInscribedActivityTasks()
	{
		return $this->belongsToMany('Avem\ActivityTask');
	}

	public function subscribedActivities()
	{
		return $this->morphedByMany('Avem\Activity', 'subscribable', 'all_subscribables');
	}

	public function subscribedActivityTasks()
	{
		return $this->morphedByMany('Avem\ActivityTask', 'subscribable', 'all_subscribables');
	}

	public function transactions()
	{
		return $this->hasMany('Avem\Transaction');
	}
}
