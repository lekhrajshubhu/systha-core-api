<?php

namespace Systha\Core\Traits;

use Systha\Core\Models\Audit;

// use Systha\FoodTruck\Model\audit;


trait StoreAudit
{
	public static function bootStoreAudit()
	{
		foreach (static::getModelEvents() as $event) {
			static::$event(function ($model) use ($event) {
				$model->storeAudit($event);
			});
		}
	}

	protected function getActivityName($model, $action)
	{
		//created
		//created_post
		$name = strtolower((new \ReflectionClass($model))->getShortName());
		if ($model->isDirty('is_deleted')) {
			if ($model->is_deleted) {
				$action = 'deleted';
			} else {
				$action = 'Recovered';
			}
		}
		return "{$action}_{$name}";
	}

	protected static function getModelEvents()
	{
		if (isset(static::$recordEvents)) {
			return static::$recordEvents;
			//insert static::$recordEvents=[events] into the model to override.

		}
		return ['created', 'updated'];
	}

	protected function storeAudit($event)
	{
		// dd($this->fresh(), $this->relation);
		// dd($this->getRelations());
		$this->audits()->create([
			'table_name' => $this->getTable() ?: '',
			'table_id' => $this->fresh()->id,
			'new_data' => json_encode($this->fresh()),
			'old_data' => json_encode($this->getOriginal()),
			'created_at' => now(),
			'updated_at' => now(),
			'activity' => $this->getActivityName($this, $event),
			'userc_id' => auth()->check() ? auth()->id() : 1, //on front end there can be no auth
			'userc_date' => now(),
			'userc_time' => now()->format('H:i:s'),
			'related' => $this->related ?? NULL,
			'related_id' => $this->related_id ?? $this->fresh()->id,
		]);
	}

	public function audits()
	{
		return $this->morphMany(Audit::class, 'table', 'table_name', 'table_id', 'id');
	}
}
