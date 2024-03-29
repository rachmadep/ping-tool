<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JJG\Ping;
use App\Libraries\PingMapper;
use App\Events\PingReplied;

class PingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping:test
                            {host=ultramon.telkom.com : hostname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping Command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $host = $this->argument('host');
        // $host = 'ultramon.telkom.com';
        print $host;
        $ping = new Ping($host);
        for ($i=0; $i < 10; $i++) { 
            $latency = $ping->ping();
            $this->print($latency, $host);
            sleep(5);
        }
        return 0;
    }

    private function print($latency, $host)
    {
        echo "\n";
        $date = date("Y-m-d H:i:s");
        $ping = new PingMapper([
            'hostname' => $host,
            'datetime' => $date,
            'latency' => $latency,
        ]);
        print $date;


        if ($latency !== false) {
            print ' | Latency is ' . $latency . ' ms';
            PingReplied::dispatch($ping);
        }
        else {
            print ' | Host could not be reached.';
        }
    }
}
