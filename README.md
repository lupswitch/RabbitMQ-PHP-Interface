# RabbitMQ PHP Interface

PHP classes to use RabbitMQ, a queuing manager!

[RabbitMQ](https://www.rabbitmq.com/features.html) is an open source message broker software that implements the Advanced Message Queuing Protocol (AMQP). You can install it freely by following the instructions on the [official website](https://www.rabbitmq.com/download.html). <br />

[Tutorials](https://www.rabbitmq.com/getstarted.html) in several languages are available. But if I decided to share this code is because the PHP tutorials sources did not work for me, and apparently for many other people... I took the opportunity to add a few more safety tests and some good uses as the management of the load and the limitation of the workers.

Maybe one day I will translate all the comments but the names of variables and functions are pretty explicit!

## Usage

Launch the producer first to send some messages in the queue.

```php
// Initialize object.
$producer = new RabbitMQProducer();
$producer->setHost('your_host');
$producer->setPort('your_port');
$producer->setUser('your_user_login');
$producer->setPassword('your_password');
// Open connection.
$producer->connect();
// Initialize queue.
$producer->setQueueName('test');
$producer->declareQueue();
// Open transaction.
$producer->startTransaction();
// Send some messages.
for ($i = 0; $i < 20; $i++) {
  $message = array('parameter_one', 'parameter_two', $i, 'parameter_n');
  $producer->send($message);
}
// Commit transaction: send all the messages at the same time.
$producer->commitTransaction();
// Close transaction.
$producer->disconnect();
```

Launch the consumer then to process the messages.

```php
// Initialize object.
$consumer = new RabbitMQConsumer();
$consumer->setHost('your_host');
$consumer->setPort('your_port');
$consumer->setUser('your_user_login');
$consumer->setPassword('your_password');
// Open connection.
$consumer->connect();
// Initialize queue.
$consumer->setQueueName('test');
$consumer->declareQueue();
// Pops and processes the messages one by one: see directly in the class for more information.
$consumer->listen();
// Optional.
$consumer->disconnect();
```
To easier understand the mechanics of RabbitMQ, refer please to the [RabbitMQ Tutorials page](https://www.rabbitmq.com/getstarted.html).

#### Credits

Jérémy Ferrero<br />
[Compilatio](https://www.compilatio.net/)<br />
[GETALP](http://getalp.imag.fr/xwiki/bin/view/Main/) - Laboratory of Informatics of Grenoble <br/> <br/>
<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.
