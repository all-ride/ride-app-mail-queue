<?php

namespace ride\application\mail\transport;

use ride\application\queue\job\MailQueueJob;

use ride\library\mail\transport\Transport;
use ride\library\mail\MailMessage;
use ride\library\queue\dispatcher\QueueDispatcher;

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
     * Delivers a mail message
     * @param \ride\library\mail\MailMessage $message
     * @return null
     */
    public function send(MailMessage $message) {
        $queueJob = new MailQueueJob();
        $queueJob->setMailMessage($message);

        $this->queueDispatcher->queue($queueJob);
    }

}
