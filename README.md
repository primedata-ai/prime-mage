# primedata-connect information
This module used to sync data from Magento Site to PrimeAi 

## Dependency
This module dependency some library :
    `
    1. primedata-ai/analytics-php:v0.3.*
    2. enqueue/redis:0.9.2
    3. predis/predis:^1.1
    4. ext-json
    5. enqueue/enqueue:0.9.2
    `
## UseCase:
Go to Store -> PrimeData 
    1. Config Transport: 'Redis' 
    2. Config Prime Client
    3. Setup information for 'Redis'.
## To test function
###  example for procedure
```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$redisConfig = $objectManager->create('PrimeData\PrimeDataConnect\Helper\RedisConfig');
$data = $redisConfig->getRedisConnection();
$buffer = $objectManager->create('PrimeData\PrimeDataConnect\Model\MessageQueue\QueueBuffer');
$buffer->createQueueManage($redisConfig);

$event = $objectManager->create('PrimeData\PrimeDataConnect\Model\Tracking\PrimeEvent');
$primeConfig = $objectManager->create('PrimeData\PrimeDataConnect\Model\PrimeConfig');
$client = $objectManager->create(
    'PrimeData\PrimeDataConnect\Model\PrimeClient',
    [$primeConfig, $buffer]
);

$test = $client->getPrimeClient();

$test->track(
    "purchase_product",
    ['total_value' => 2000, 'currency' => "USD"],
    $event->setSessionId("1e85YTciGhH6vLfLpmqhJfhFhpq")
);
```
### example to consumer
```php
<?php
require 'app/bootstrap.php';

use Enqueue\Redis\RedisConnectionFactory;

$config = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'lazy' => 1,
    'vendor' => 'predis',
    'scheme_extensions' => ['predis']
];

$factory = new RedisConnectionFactory($config);

$context = $factory->createContext();


$queueName = 'primedata-events';
$fooQueue = $context->createQueue($queueName);
$context->createTopic('primedata-events');
$consumer = $context->createConsumer($fooQueue);
$run = true;

while ($run == true) {
    $message = $consumer->receive();
    if (!$message) {
        print_r('chua co du lieu');
    } else {
        print_r($message);
        $consumer->acknowledge($message);
        $run = false;
    }
}
```
