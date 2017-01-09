<?php

namespace Boparaiamrit\Notifynder\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class NotificationCategory.
 *
 * @property int    id
 * @property string name
 * @property string text
 */
class NotificationCategory extends Model
{
    /**
     * @var string
     */
    protected $collection = 'notification_categories';

    /**
     * @var array
     */
    protected $fillable = ['name', 'text'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Relation with the notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany('Boparaiamrit\Notifynder\Models\Notification', 'category_id');
    }

    /**
     * Groups Categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(
            'Boparaiamrit\Notifynder\Models\NotificationGroup',
            'notifications_categories_in_groups',
            'category_id',
            'group_id'
        );
    }
}
