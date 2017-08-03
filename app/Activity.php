<?php

namespace Avem;

use Carbon\Carbon;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class Activity extends Model implements HasMediaConversions
{
	use Searchable;
	use HasMediaTrait;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'description', 'location', 'start', 'end', 'audience',
		'inscription_policy', 'inscription_start', 'inscription_end',
		'member_limit', 'points', 'image', 'published',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at', 'updated_at', 'start', 'end',
		'inscription_start', 'inscription_end',
	];

	public function activityTickets()
	{
		return $this->hasMany('Avem\ActivityTicket');
	}

	public function getImageAttribute()
	{
		return $this->getMedia('images')->first();
	}

	public function getIsReadyToPublishAttribute()
	{
		return $this->name !== null
		    && $this->description !== null
		    && $this->image !== null;
	}

	public function inscribedUsers()
	{
		switch ($this->inscriptionPolicy) {
			case 'inscribed':
				return $this->selfInscribedUsers();

			case 'board':
				return $this->query()->crossJoin('charge_periods')
					->select('users.*', 'activities.id as pivot_activity_id', 'users.id as pivot_user_id')
					->join('users', 'users.id', '=', 'charge_periods.user_id')
					->where('activities.id', $this->id)
					->where(function($query) {
						$query->whereBetween('charge_periods.start', ['activities.start', 'activities.end'])
						      ->orWhereBetween('charge_periods.end', ['activities.start', 'activities.end'])
						      ->orWhereBetween('activities.start', ['charge_periods.start', 'charge_periods.end'])
						      ->orWhereBetween('activities.end', ['charge_periods.start', 'charge_periods.end']);
					});

			case 'all':
				$this->query()->crossJoin('users')
					->select('users.*', 'activities.id as pivot_activity_id', 'users.id as pivot_user_id')
					->whereDate('users.created_at', '<', 'activities.end')
					->where('activities.id', $this->id);
		}
	}

	public function notifications()
	{
		return $this->morphMany('Avem\Notification', 'notifiable');
	}

	public function organizerPeriods()
	{
		return $this->belongsToMany('Avem\ChargePeriod');
	}

	public function performedActivityRecords()
	{
		return $this->hasMany('Avem\PerformedActivity');
	}

	public function registerMediaConversions()
	{
		$this->addMediaConversion('thumb')
		     ->width(368)->height(232)
		     ->sharpen(10);
	}

	public function setStartAttribute($date)
	{
		$this->attributes['start'] = $date
			? Carbon::createFromFormat('Y-m-d\TH:i', $date)
			: null;
	}

	public function setEndAttribute($date)
	{
		$this->attributes['end'] = $date
			? Carbon::createFromFormat('Y-m-d\TH:i', $date)
			: null;
	}

	public function setInscriptionStartAttribute($date)
	{
		$this->attributes['inscription_start'] = $date
			? Carbon::createFromFormat('Y-m-d', $date)
			: null;
	}

	public function setInscriptionEndAttribute($date)
	{
		$this->attributes['inscription_end'] = $date
			? Carbon::createFromFormat('Y-m-d', $date)
			: null;
	}

	public function selfInscribedUsers()
	{
		return $this->belongsToMany('Avem\User');
	}

	public function tags()
	{
		return $this->morphToMany('Avem\Tag', 'taggable');
	}

	public function toSearchableArray()
	{
		$data = $this->toArray();
		$data['tags'] = $this->tags->pluck('name');
		return $data;
	}

	public function transactions()
	{
		return $this->morphMany('Avem\Transaction', 'transactionable');
	}
}
