<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper\MessageQueue;

use Interop\Queue\ConnectionFactory as QueueConnectionInterface;

/**
 * Interface MessageConfigInterface
 * @api
 * @since 1.1.0
 */
interface MessageConfigInterface
{
    /**
     * @return array
     */
    public function getMessageQueueConfig();

    /**
     * @return QueueConnectionInterface
     */
    public function getConnection();
}
