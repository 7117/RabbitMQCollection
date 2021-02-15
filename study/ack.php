<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

include './config.php';

$connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
$channel = $connection->channel();

//保存订单消息到数据库；是否将信息成功推送到消息队列

//推送成功
$channel->set_ack_handler(
    function (AMQPMessage $message) {
        echo "success" . $message->body . PHP_EOL;
    }
);

// 推送失败
$channel->set_ack_handler(
    function (AMQPMessage $message) {
        echo "fail" . $message->body . PHP_EOL;
    }
);
//开启确认模式
$channel->confirm_select();

$channel->exchange_declare($exchange, AMQPExchangeType::FANOUT, false, false, true);

$i = 1;

$msg = new AMQPMessage($i, ['content_type' => 'text/plain']);
$channel->basic_publish($msg, $exchange);
$channel->wait_for_pending_acks();

while ($i <= 11) {
    $msg = new AMQPMessage($i, ['content_type' => 'text/plain']);
    $channel->basic_publish($msg, $exchange);
}

$channel->wait_for_pending_acks();
$channel->close();