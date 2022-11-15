<?php 

namespace App\Services\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection as Connection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Services\Taskers\GithubTaskerService;
use App\Services\Webhook\WebhookRequestService;

class PublisherService 
{
    private $connection;
    private $channel;
    private $queue;
    private $message;
    const LIMIT_DAYS = 1;

    public function __construct( $queue ) {
        $this->queue = $queue;
    }

    public function connect() 
    {
        try 
        {
            $this->connection = new Connection(
                env("QUEUE_CONNECTION"),
                env("RABBITMQ_PORT"),
                env("RABBITMQ_USER"),
                env("RABBITMQ_PASSWORD")
            );

            $this->channel = $this->connection->channel();
            $this->channel->queue_declare($this->queue, true, false, false, false);
        }
        catch( \Throwable $t ){
            throw new \Exception(json_encode(["message" => $t->getMessage(), "queue" => $this->queue]));
        }
    }

    public function publishMessage($message)
    {
        $this->message = $message;
        $msg = new AMQPMessage( $this->message );
        $this->channel->basic_publish($msg, "", $this->queue);
    }

    public function receiveUserMessages() {
        $this->channel->basic_consume($this->queue, '', false, true, false, false, function( $message ) {
            $body = json_decode($message->body);
            $task = new GithubTaskerService();
            $task->execute($body);
        });

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->close();
    }

    public function receiveMessages() {
        $this->channel->basic_consume($this->queue, '', false, false, false, false, function( $message ) {
            $body = json_decode($message->body);

            if( $this->validateMessageDate($body->published_at) < SELF::LIMIT_DAYS ){
                $this->channel->basic_nack($message->getDeliveryTag(), false, true);
                return;
            }
            
            $webhookService = new WebhookRequestService();
            $webhookService->request(json_encode($body));
            $this->channel->basic_ack($message->getDeliveryTag());
        });

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->close();
    }

    private function validateMessageDate( $date )
    {
        $messaageDateTime = date_create($date);
        $currentDateTime = date_create(date('m/d/Y h:i:s', time()));
        $interval = date_diff($messaageDateTime, $currentDateTime);

        return $interval->days;
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}