<?php

namespace Piscarocarlos\Helpify\Commands;

use Illuminate\Console\Command;

class HelpifyCommand extends Command
{
    public $signature = 'helpify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
