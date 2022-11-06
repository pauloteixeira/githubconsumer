<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\RabbitMQ\PublisherService;

class BroakerController extends Controller
{
    public function index( Request $request ) {
        $publisher = new PublisherService();
        $publisher->connect();

        foreach (range(0, 100) as $i)
            $publisher->publishMessage(json_encode(["id" => 1, "body" => "alo gente."]));
        
        dd("publicou!");
    }

}