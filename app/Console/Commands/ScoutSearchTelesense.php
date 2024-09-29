<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\search;

class ScoutSearchTelesense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = search(
            'Search something cool',
            fn(string $value) => ($value) 
                ? User::search($value)->get()->pluck('name', 'id')->all() 
                : []
        );
    }
}
