<?php

namespace Piscarocarlos\Helpify;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Piscarocarlos\Helpify\Commands\HelpifyCommand;

class HelpifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         */
        $package
            ->name('helpify');
            // ->hasConfigFile()
            // ->hasViews()
            // ->hasMigration('create_helpify_table')
            // ->hasCommand(HelpifyCommand::class);
    }
}
