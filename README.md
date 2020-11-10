# primedata-connect information
This module used to sync data from Magento Site to PrimeAi 

#Install
```shell script
composer require primedata-ai/prime-mage:1.1.2
```
## Dependency
This module dependency some library :
* "primedata-ai/analytics-php": "0.3.*",
* "enqueue/redis": "^0.9.12",
* "predis/predis": "^1.1",
* "ext-json": "*",
* "enqueue/dsn": "0.9.2",
* "enqueue/amqp-tools": "0.9.12",
* "enqueue/amqp-lib": "0.9.14",
* "enqueue/enqueue": "0.9.2"
## UseCase:
Go to Store -> PrimeData 
* Config Transport: 'Redis' or 'RabbitMQ AMQP'
* Config Prime Client
* Setup information for 'Redis'.
* Setup information for RabbitMQ follow document: https://www.rabbitmq.com/documentation.html
* To run consumer get data and sync to PrimeData Ai. Please run the command.
```
php bin/magento prime:sync
```

##Change Log
1.0.0 innit module add some event to tracking product, customer
1.1.0 Add more event for tracking add_to_cart, wishlist, review, place_order
1.1.4 Add Prime Host, add Command to consume data and send it to primeAI
2.0.0 Add RabbitMQ Message Queue using AMQP
