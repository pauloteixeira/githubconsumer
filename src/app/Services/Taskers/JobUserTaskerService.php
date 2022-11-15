<?php

namespace App\Services\Taskers;

use App\Services\RabbitMQ\PublisherService;

class JobUserTaskerService 
{
    public function execute() {
        $publisher = new PublisherService( env("QUEUE_GHUBBER_USERS_COLLECTION_MESSAGES") );
        $publisher->connect();
        $publisher->receiveUserMessages();
    }
}