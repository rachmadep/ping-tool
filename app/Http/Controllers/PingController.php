<?php

namespace App\Http\Controllers;

use App\Events\PingFailed;
use App\Events\PingReplied;
use App\Libraries\PingMapper;
use Illuminate\Http\Request;
use JJG\Ping;

class PingController extends Controller
{
    
    public function post(Request $request)
    {
        $host = $request->hostname;

        $ping = new Ping($host);
        for ($i=0; $i < 10; $i++) { 
            $latency = $ping->ping();
            $this->getLatency($latency, $host);
            sleep(5);
        }
        return true;
    }

    private function getLatency($latency, $host)
    {
        $date = date("Y-m-d H:i:s");
        $ping = new PingMapper([
            'hostname' => $host,
            'datetime' => $date,
            'latency' => $latency,
        ]);

        if ($latency !== false) {
            PingReplied::dispatch($ping);
        }
        else {
            PingFailed::dispatch($ping);
        }
    }

    public function view()
    {
        return view('ping');
    }
    
    public function chart(Request $request)
    {
        $host = $request->hostname ?? 'espn.com';

        $ping = new Ping($host);
        $latency = $ping->ping();
        $ping = new PingMapper([
            'hostname' => $host,
            'time' => date("H:i:s"),
            'latency' => $latency,
        ]);

        return response()->json([
            'hostname' => $host,
            'time' => date("Y-m-d H:i:s"),
            'latency' => $latency,
        ]);
    }
    
}
