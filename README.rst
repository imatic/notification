.. image:: https://secure.travis-ci.org/imatic/data-bundle.png?branch=master
   :alt: Build Status
   :target: http://travis-ci.org/imatic/data-bundle
|
.. image:: https://img.shields.io/badge/License-MIT-yellow.svg
   :alt: License: MIT
   :target: LICENSE

Imatic Notification
*******************

Application for simplifying using message queues

Configuration
=============

Library needs 2 configuration options to by specified by you. How to specify them depends on your service container (see `Acessing to the services section`_).

* imatic_notification_params

  * parameters of the connection to the broker

.. sourcecode:: php

   <?php
   $params = [
       'host' => 'localhost',
       'port' => 5672,
       'user' => 'guest',
       'password' => 'guest',
       'namespace' => '',
   ];


* imatic_notification.logger

  * logger implementing interface of `psr log`_
  * in our examples below, we did use NullLogger, which will log nothing - we highly discourage from usage if this logger in production since you'll have no Idea what went wrong if something wrong happend

Interface of the library
========================

You will work with 2 interfaces Publisher for publishing messages into queues and Consumer to consume messages published by publisher.

.. sourcecode:: php

   <?php
   namespace Imatic\Notification;

   interface Connection
   {
      public function createPublisher(ChannelParams $params);
   
      public function createConsumer(ChannelParams $params);
   }

   interface Publisher
   {
       public function publish(Message $message, $key = '');
   }

   interface Consumer
   {
       public function consume($queueName, $key, callable $callback);
       
       public function wait();

       public function waitN($n);
   }

These 2 interfaces are implemented by service ``imatic_notification.connection``

.. _`Acessing to the services section`:

Accessing to the services
=========================

It is advised to use one container to create connection object for you from definition file "config/services.yml". Below you can see 2 of many possible ways to go.

Using Pimple
------------

To load services for pimple, you need to add yml2pimple_ dependency into your composer.json

.. sourcecode:: php

   <?php
   // create instance of container with required parameters
   $pimple = new Container([
       'imatic_notification_params' => [],
       'imatic_notification.logger' => new NullLogger(),
   ]);

   // load services using yaml2pimple
   $builder = new ContainerBuilder($pimple);
   $locator = new FileLocator([
       __DIR__ . '/../vendor/imatic/notification/config',
   ]);
   $loader = new YamlFileLoader($builder, $locator);
   $loader->load('services.yml');

   // then you can access to the services
   $connection = $pimple['imatic_notification.connection'];

Using Symfony
-------------

To load services for Symfony, you need to edit your your app/config/config.yml

.. sourcecode:: yaml

   imports:
       - { resource: ../../vendor/imatic/notification/config/services.yml }

   parameters:
       imatic_notification_params: []

   services:
       imatic_notification.logger:
           class: Psr\Log\NullLogger

And then you can access to the services from your Symfony container

.. sourcecode:: php

   <?php
   $connection = $this->container->get('imatic_notification.connection');

Usage example
=============

.. sourcecode:: php

   <?php
   // create connection to the broker
   $connection = $this->container->get('imatic_notification.connection');

   // create channel parameters
   $channelParams = new ChannelParams($exchange = 'imatic_queue_test');

   // create consumer
   $consumer = $connection->createConsumer($channelParams);

   // listen to the messages on queue "queue_name"
   // to all messages having routing key "routing_key"
   $consumer->consume('queue_name', 'routing_key', function (Message $msg) {
       $this->logger->logData('data');

       // you need to return true to tell the broker that it can discard the messaga
       // because you successfully processed it
       return true;
   });

   // create publisher
   $publisher = $connection->createPublisher($channelParams);

   // publish message to the channel with routing key "routing_key"
   $publisher->publish(new Message(['data' => 'bdy']), 'routing_key');

   // consume only 1 message, then continue
   $consumer->waitN(1);

   // won't return till you have listening consumers
   $consumer->wait();

.. _yml2pimple: https://github.com/gonzalo123/yml2pimple
.. _`psr log`: https://github.com/php-fig/log/tree/master

