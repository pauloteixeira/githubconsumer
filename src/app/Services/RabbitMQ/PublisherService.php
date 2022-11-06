<?php 

namespace App\Services\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection as Connection;
use PhpAmqpLib\Message\AMQPMessage;

class PublisherService 
{
    private $connection;
    private $channel;
    private $queue;

    public function connect() 
    {
        try 
        {
            $this->queue = env("QUEUE_GHUBBER_RETURNER_MESSAGES");
            $this->connection = new Connection(
                "rabbitmq",
                env("RABBITMQ_PORT"),
                env("RABBITMQ_USER"),
                env("RABBITMQ_PASSWORD")
            );

            $this->channel = $this->connection->channel();
            $this->channel->queue_declare($this->queue, false, false, false, false);
        }
        catch( \Throwable $t ){
            throw new \Exception($t->getMessage());
        }
    }

    public function publishMessage($message)
    {
        $msg = new AMQPMessage( $message );
        $this->channel->basic_publish($msg, "", $this->queue);
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}