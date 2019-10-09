<?php

namespace ride\application\mail\transport;

use ride\application\queue\job\MailQueueJob;

use ride\library\mail\transport\Transport;
use ride\library\mail\MailMessage;
use ride\library\queue\dispatcher\QueueDispatcher;

use \Exception;

/**
 * Interface for the mail transport
 */
class QueueMailTransport implements Transport {

    /**
     * Instance of the queue dispatcher
     * @var \ride\library\queue\dispatcher\QueueDispatcher
     */
    private $queueDispatcher;

    /**
     * Instance of the mail transport
     * @var \ride\library\mail\transport\Transport
     */
    private $mailTransport;

    /**
     * Time in seconds after which a retry should be attempted
     * @var integer
     */
    private $retryTime = 300;

    /**
     * Maximum number of retries which will be attempted
     * @var integer
     */
    private $maxRetries = 5;

    /**
     * Constructs a new queue mail transport
     * @param \ride\library\queue\dispatcher\QueueDispatcher $queueDispatcher
     * @param \ride\library\mail\transport\Transport $mailTransport
     * @return null
     */
    public function __construct(QueueDispatcher $queueDispatcher, Transport $mailTransport) {
        $this->queueDispatcher = $queueDispatcher;
        $this->mailTransport = $mailTransport;
    }

    /**
     * Creates a mail message
     * @return \ride\library\mail\MailMessage
     */
    public function createMessage() {
        return $this->mailTransport->createMessage();
    }

    /**
     * Sets the retry time for sending messages
     * @param integer $retryTime Time in seconds
     */
    public function setRetryTime($retryTime) {
        if (!is_numeric($retryTime) || $retryTime < 0) {
            throw new Exception('Could not set mail retry time: positive numeric value expected');
        }

        $this->retryTime = $retryTime;
    }

    /**
     * Gets the retry time for sending messages
     * @return integer Time in seconds
     */
    public function getRetryTime() {
        return $this->retryTime;
    }

    /**
     * Sets the maximum number of schedules
     * @param integer $maxSchedules Number of schedules
     */
    public function setMaxRetries($maxRetries) {
        if (!is_numeric($maxRetries) || $maxRetries < 0) {
            throw new QueueException('Could not set the maxRetries: provided value should be a number greater then 0');
        }

        $this->maxRetries = $maxRetries;
    }

    /**
     * Gets the maximum number of retries which will be attempted
     * @return integer
     */
    public function getMaxRetries() {
        return $this->maxRetries;
    }

    /**
     * Delivers a mail message
     * @param \ride\library\mail\MailMessage $message
     * @return null
     */
    public function send(MailMessage $message) {
        $queueJob = new MailQueueJob();
        $queueJob->setMailMessage($message);
        $queueJob->setRetryTime($this->retryTime);
        $queueJob->setMaxSchedules($this->maxRetries);

        $this->queueDispatcher->queue($queueJob);
    }

}
