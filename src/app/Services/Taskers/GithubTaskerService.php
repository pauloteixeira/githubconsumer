<?php

namespace App\Services\Taskers;

use App\Services\RabbitMQ\PublisherService;
use App\Messages\GithubMessage;
use App\Models\Appointments;
use App\Services\Github\GithubRequestService;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\EachPromise;

class GithubTaskerService 
{
    private $users;
    private $message;
    const THREADS = 100;

    public function execute( $task )
    {
        $this->getUsersAndSaveMessage( $task );
    }

    private function getUsersAndSaveMessage( $task ) 
    {
        $github = new GithubRequestService("", "");
        $collectedUsers = [];
        $this->message = $task;
        $this->users = [];
        
        $callback = function( $response ) {
            $this->users[] = [$response["login"] => $response["name"]];
        };

        $promises = (function () use ($github, $callback) {
            foreach( $this->message->contributtors as $contributtor ) {
                yield $github->requestUser( $contributtor->user, $callback );            
            }
        })();

        $eachPromise = new EachPromise($promises, [
            'concurrency' => SELF::THREADS,
            'fulfilled' => function ($response){
                $response["callback"]($response["result"]);
            },
            'rejected' => function ($reason) {}
        ]);

        $eachPromise->promise()->wait();

        $this->mapContributtors();
        $this->publishQueue($this->message);

        return true;
    }

    private function saveAppointments( $message ) 
    {
        $model = new Appointments();
        $model->payload = json_encode( $message );
        $model->save();
    }

    private function mapContributtors()
    {
        foreach( $this->message->contributtors as $contributtor ) {
            foreach( $this->users as $user ){
                if( array_key_exists($contributtor->user, $user)){
                    $contributtor->name = $user[$contributtor->user];
                }
            }
        }
    }

    private function publishQueue( $message ) {
        $publisher = new PublisherService( env("QUEUE_GHUBBER_RETURNER_MESSAGES") );
        $publisher->connect();
        
        return $publisher->publishMessage(json_encode($message));
    }
}