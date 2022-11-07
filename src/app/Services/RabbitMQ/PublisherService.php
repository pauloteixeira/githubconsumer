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
            $this->channel->queue_declare($this->queue, true, false, false, false);
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

    public function receiveMessages() {
        $this->channel->basic_consume($this->queue, '', false, false, false, false, function( $message ) {
            echo $message->body . PHP_EOL;

            $body = json_decode($message->body);

            if($body->id == 2) {
                $this->channel->basic_nack($message->getDeliveryTag(), false, true);
                return;
            }
            
            $this->channel->basic_ack($message->getDeliveryTag());

            if ($message->body == 'stop'){
                $this->channel->basic_cancel("GHUBBER_RETURNER_MESSAGES");
            }
        });
       
        
        while (count($this->channel->callbacks)) {
            //try{
                $this->channel->wait();
            // }
            // catch( \Exception $e)
            // {
            //     // DO NOTHING
            // }
        }

        dd($filas);

        $this->close();

    } 

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}