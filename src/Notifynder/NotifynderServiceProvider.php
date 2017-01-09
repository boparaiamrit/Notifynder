<?php

namespace Boparaiamrit\Notifynder;


use Boparaiamrit\Notifynder\Artisan\CreateCategory;
use Boparaiamrit\Notifynder\Artisan\CreateGroup;
use Boparaiamrit\Notifynder\Artisan\DeleteCategory;
use Boparaiamrit\Notifynder\Artisan\PushCategoryToGroup;
use Boparaiamrit\Notifynder\Builder\NotifynderBuilder;
use Boparaiamrit\Notifynder\Categories\CategoryManager;
use Boparaiamrit\Notifynder\Categories\CategoryRepository;
use Boparaiamrit\Notifynder\Contracts\CategoryDB;
use Boparaiamrit\Notifynder\Contracts\NotificationDB;
use Boparaiamrit\Notifynder\Contracts\NotifynderCategory;
use Boparaiamrit\Notifynder\Contracts\NotifynderDispatcher;
use Boparaiamrit\Notifynder\Contracts\NotifynderGroup;
use Boparaiamrit\Notifynder\Contracts\NotifynderGroupCategoryDB;
use Boparaiamrit\Notifynder\Contracts\NotifynderGroupDB;
use Boparaiamrit\Notifynder\Contracts\NotifynderNotification;
use Boparaiamrit\Notifynder\Contracts\NotifynderSender;
use Boparaiamrit\Notifynder\Contracts\NotifynderTranslator;
use Boparaiamrit\Notifynder\Contracts\StoreNotification;
use Boparaiamrit\Notifynder\Groups\GroupCategoryRepository;
use Boparaiamrit\Notifynder\Groups\GroupManager;
use Boparaiamrit\Notifynder\Groups\GroupRepository;
use Boparaiamrit\Notifynder\Handler\Dispatcher;
use Boparaiamrit\Notifynder\Models\NotificationCategory;
use Boparaiamrit\Notifynder\Models\NotificationGroup;
use Boparaiamrit\Notifynder\Notifications\NotificationManager;
use Boparaiamrit\Notifynder\Notifications\NotificationRepository;
use Boparaiamrit\Notifynder\Parsers\ArtisanOptionsParser;
use Boparaiamrit\Notifynder\Parsers\NotifynderParser;
use Boparaiamrit\Notifynder\Senders\SenderFactory;
use Boparaiamrit\Notifynder\Senders\SenderManager;
use Boparaiamrit\Notifynder\Translator\Compiler;
use Boparaiamrit\Notifynder\Translator\TranslatorManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class NotifynderServiceProvider extends ServiceProvider
{
    /**
     * Register Bindings.
     */
    public function register()
    {
        $this->notifynder();
        $this->senders();
        $this->notifications();
        $this->categories();
        $this->builder();
        $this->groups();
        $this->translator();
        $this->events();
        $this->contracts();
        $this->artisan();
    }

    /*
     * Boot the publishing config
     */
    public function boot()
    {
        $this->config();
    }

    /**
     * Bind Notifynder.
     */
    protected function notifynder()
    {
        $this->app->singleton('notifynder', function ($app) {
            return new NotifynderManager(
                $app['notifynder.category'],
                $app['notifynder.sender'],
                $app['notifynder.notification'],
                $app['notifynder.dispatcher'],
                $app['notifynder.group']
            );
        });

        // Register Facade
        $this->app->alias('notifynder', 'Notifynder');
    }

    /**
     * Bind Notifynder Categories to IoC.
     */
    protected function categories()
    {
        $this->app->singleton('notifynder.category', function ($app) {
            /** @var Application $app */
            return new CategoryManager(
                $app->make('notifynder.category.repository')
            );
        });

        $this->app->singleton('notifynder.category.repository', function () {
            return new CategoryRepository(
                new NotificationCategory()
            );
        });
    }

    /**
     * Bind the notifications.
     */
    protected function notifications()
    {
        $this->app->singleton('notifynder.notification', function ($app) {
            return new NotificationManager(
                $app['notifynder.notification.repository']
            );
        });

        $this->app->singleton('notifynder.notification.repository', function ($app) {
            $notificationModel = $app['config']->get('notifynder.notification_model');
            /** @var Application $app */
            $notificationInstance = $app->make($notificationModel);

            return new NotificationRepository(
                $notificationInstance,
                $app['db']
            );
        });

        // Default store notification
        $this->app->bind('notifynder.store', 'notifynder.notification.repository');
    }

    /**
     * Bind Translator.
     */
    protected function translator()
    {
        $this->app->singleton('notifynder.translator', function ($app) {
            return new TranslatorManager(
                $app['notifynder.translator.compiler'],
                $app['config']
            );
        });

        $this->app->singleton('notifynder.translator.compiler', function ($app) {
            return new Compiler(
                $app['filesystem.disk']
            );
        });
    }

    /**
     * Bind Senders.
     */
    protected function senders()
    {
        $this->app->singleton('notifynder.sender', function ($app) {
            return new SenderManager(
                $app['notifynder.sender.factory'],
                $app['notifynder.store'],
                $app[Container::class]
            );
        });

        $this->app->singleton('notifynder.sender.factory', function ($app) {
            return new SenderFactory(
                $app['notifynder.group'],
                $app['notifynder.category']
            );
        });
    }

    /**
     * Bind Dispatcher.
     */
    protected function events()
    {
        $this->app->singleton('notifynder.dispatcher', function ($app) {
            return new Dispatcher(
                $app['events']
            );
        });
    }

    /**
     * Bind Groups.
     */
    protected function groups()
    {
        $this->app->singleton('notifynder.group', function ($app) {
            return new GroupManager(
                $app['notifynder.group.repository'],
                $app['notifynder.group.category']
            );
        });

        $this->app->singleton('notifynder.group.repository', function () {
            return new GroupRepository(
                new NotificationGroup()
            );
        });

        $this->app->singleton('notifynder.group.category', function ($app) {
            return new GroupCategoryRepository(
                $app['notifynder.category'],
                new NotificationGroup()
            );
        });
    }

    /**
     * Bind Builder.
     */
    protected function builder()
    {
        $this->app->singleton('notifynder.builder', function ($app) {
            return new NotifynderBuilder(
                $app['notifynder.category']
            );
        });

        $this->app->resolving(NotifynderBuilder::class, function (NotifynderBuilder $object, $app) {
            $object->setConfig($app['config']);
        });
    }

    /**
     * Contracts of notifynder.
     */
    protected function contracts()
    {
        // Notifynder
        $this->app->bind(Notifynder::class, 'notifynder');

        // Repositories
        $this->app->bind(CategoryDB::class, 'notifynder.category.repository');
        $this->app->bind(NotificationDB::class, 'notifynder.notification.repository');
        $this->app->bind(NotifynderGroupDB::class, 'notifynder.group.repository');
        $this->app->bind(NotifynderGroupCategoryDB::class, 'notifynder.group.category');

        // Main Classes
        $this->app->bind(NotifynderCategory::class, 'notifynder.category');
        $this->app->bind(NotifynderNotification::class, 'notifynder.notification');
        $this->app->bind(NotifynderTranslator::class, 'notifynder.translator');
        $this->app->bind(NotifynderGroup::class, 'notifynder.group');

        // Store notifications
        $this->app->bind(StoreNotification::class, 'notifynder.store');
        $this->app->bind(NotifynderSender::class, 'notifynder.sender');
        $this->app->bind(NotifynderDispatcher::class, 'notifynder.dispatcher');
    }

    /**
     * Publish config files.
     */
    protected function config()
    {
        $this->publishes([
            __DIR__ . '/../config/notifynder.php' => config_path('notifynder.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/notifynder.php', 'notifynder');

        // Set use strict_extra config option,
        // you can toggle it in the configuration file
        $strictParam = $this->app['config']->get('notifynder.strict_extra', false);
        NotifynderParser::setStrictExtra($strictParam);
    }

    /**
     * Register Artisan commands.
     */
    protected function artisan()
    {
        // Categories
        $this->app->singleton('notifynder.artisan.category-add', function ($app) {
            return new CreateCategory(
                $app['notifynder.category']
            );
        });

        $this->app->singleton('notifynder.artisan.category-delete', function ($app) {
            return new DeleteCategory(
                $app['notifynder.category']
            );
        });

        // Groups
        $this->app->singleton('notifynder.artisan.group-add', function ($app) {
            return new CreateGroup(
                $app['notifynder.group']
            );
        });

        $this->app->singleton('notifynder.artisan.group-add-categories', function ($app) {
            return new PushCategoryToGroup(
                $app['notifynder.group'],
                new ArtisanOptionsParser()
            );
        });

        // Register commands
        $this->commands([
            'notifynder.artisan.category-add',
            'notifynder.artisan.category-delete',
            'notifynder.artisan.group-add',
            'notifynder.artisan.group-add-categories',
        ]);
    }
}
