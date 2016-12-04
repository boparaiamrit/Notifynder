<?php

$factory('Boparaiamrit\Notifynder\Models\NotificationCategory', [

    'name' => $faker->name,
    'text' => 'test notification',
]);

$factory('Boparaiamrit\Tests\Models\User', [

    'name' => $faker->name,
    'surname' => $faker->lastName,
]);

$factory('Boparaiamrit\Notifynder\Models\Notification', [

    'from_id' => 'factory:Boparaiamrit\Tests\Models\User',
    'from_type' => 'Boparaiamrit\Tests\Models\User',
    'to_id' => 'factory:Boparaiamrit\Tests\Models\User',
    'to_type' => 'Boparaiamrit\Tests\Models\User',
    'category_id' => 'factory:Boparaiamrit\Notifynder\Models\NotificationCategory',
    'url' => $faker->url,
    'extra' => json_encode(['exta.name' => $faker->name]),
    'read' => 0,
    'expire_time' => null,
    'created_at' => $faker->dateTime,
    'updated_at' => $faker->dateTime,
]);

$factory('Boparaiamrit\Notifynder\Models\NotificationGroup', [
    'name' => $faker->name,
]);
