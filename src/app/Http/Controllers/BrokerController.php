<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\RabbitMQ\PublisherService;
use App\Services\Github\GithubRequestService;
use App\Models\Appointments;
use App\Messages\GithubMessage;
use App\Messages\IssueMessage;
use App\Messages\ContributtorMessage;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\EachPromise;

class BrokerController extends Controller
{
    private $issues;
    private $contributtors;
    private $userData;

    public function requestGitHub( Request $request ) {
        set_time_limit(200);
        //$owner = "Yalantis";
        //$repository = "StarWars.Android";
        $owner = "laravel";
        $repository = "laravel";
        
        $github = new GithubRequestService($owner, $repository);

        $callbackIssues = function( $response ) use ($owner, $repository) {
            $this->mapIssues( $response );
        };

        $callbackContrinuttors = function( $responseContributtors ) use ($owner, $repository) {
            $this->userData = $this->mapContributtors( $responseContributtors );

            $message = new GithubMessage();
            $message->user = $owner;
            $message->repository = $repository;
            $message->issues = $this->issues;
            $message->contributtors = $this->contributtors;
            $message->published_at = date('m/d/Y h:i:s', time());

            $this->publishUsersQueue( $message );
        };

        $promises = (function () use ($github, $callbackIssues, $callbackContrinuttors) {
                yield $github->requestIssues( $callbackIssues );
                yield $github->requestContributtors( $callbackContrinuttors );
            }
        )();

        $eachPromise = new EachPromise($promises, [
            'concurrency' => 2,
            'fulfilled' => function ($response) {
                $response["callback"]($response["result"]);
            },
            'rejected' => function ($reason) {}
        ]);

        $eachPromise->promise()->wait();
    }

    public function consumerUsersQueue( Request $request ) {
        $publisher = new PublisherService( env("QUEUE_GHUBBER_USERS_COLLECTION_MESSAGES") );
        $publisher->connect();
        $publisher->receiveUserMessages();
    }

    public function consumerMessageQueue( Request $request )
    {
        $publisher = new PublisherService( env("QUEUE_GHUBBER_RETURNER_MESSAGES") );
        $publisher->connect();
        $publisher->receiveMessages();
    }

    private function mapIssues( $issues )
    {
        $issuesList = [];
        
        foreach($issues as ["title" => $title, "user" => $user, "labels" => $labels]) {
            $issue = new IssueMessage();
            $issue->title = $title;
            $issue->author = $user["login"];
            $issue->labels = $labels;

            $issuesList[] = $issue;
        }

        $this->issues = $issuesList;
    }

    private function mapContributtors( $contributtors )
    {
        $contributtorsList = [];
        $users = [];

        foreach($contributtors as ["login" => $login, "contributions" => $contributions]) {
            $users[] = $login;
            $contributtor = new ContributtorMessage();
            $contributtor->user = $login;
            $contributtor->qtd_commits = $contributions;

            $contributtorsList[] = $contributtor;
        }

        $this->contributtors = $contributtorsList;

        return $users;
    }

    private function publishUsersQueue( $message ) {
        $publisher = new PublisherService( env("QUEUE_GHUBBER_USERS_COLLECTION_MESSAGES") );
        $publisher->connect();
        $publisher->publishMessage(json_encode($message));
    }
}