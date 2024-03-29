<?php namespace DreamFactory\Console;

use DreamFactory\Managed\Bootstrap\ManagedInstance;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /** @inheritdoc */
    protected $commands = [
        'DreamFactory\Console\Commands\ClearAllFileCache',
        'DreamFactory\Console\Commands\Request',
        'DreamFactory\Console\Commands\Import',
        'DreamFactory\Console\Commands\ImportPackage',
        'DreamFactory\Console\Commands\PullMigrations',
        'DreamFactory\Console\Commands\Setup',
        'DreamFactory\Console\Commands\ADGroupImport',
        'DreamFactory\Console\Commands\HomesteadConfig',
        'DreamFactory\Console\Commands\ServiceTypeMigrate',
	'DreamFactory\Console\Commands\Hhvm',
	'\DreamFactory\Console\Commands\CheckNequiActivity',
	'\DreamFactory\Console\Commands\CheckServiceActivity',
       	'\DreamFactory\Console\Commands\CheckReservas'

    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Inject our bootstrapper into the mix
     */
    protected function bootstrappers()
    {
        $_stack = parent::bootstrappers();

        //  Insert our guy
        array_unshift($_stack, array_shift($_stack), ManagedInstance::class);

        return $_stack;
    }
}
