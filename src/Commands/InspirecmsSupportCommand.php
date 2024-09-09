<?php

namespace Solutionforest\Inspirecms\Support\Commands;

use Illuminate\Console\Command;

class InspirecmsSupportCommand extends Command
{
    public $signature = 'inspirecms-support';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
