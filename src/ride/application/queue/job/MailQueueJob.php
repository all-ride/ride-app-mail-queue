<?php

namespace ride\application\queue\job;

use ride\library\mail\transport\Transport;
use ride\library\mail\MailMessage;
use ride\library\queue\job\AbstractQueueJob;
use ride\library\queue\QueueManager;

/**
 * Queue job to send a mail
 */
class MailQueueJob extends AbstractQueueJob {

    /**
     * Instance of the mail transport
     * @var \ride\library\mail\transport\Transport
     */
    private $transport;

    /**
     * Instance of the message to send
     * @var \ride\library\mail\MailMessage
     */
    private $message;

    /**
     * Sets the mail transport
     * @param \ride\library\mail\transport\Transport $transport
     * @return null
     */
    public function setMailTransport(Transport $transport) {
        $this->transport = $transport;
    }

    /**
     * Sets the mail message
     * @param \ride\library\mail\MailMessage $message
     * @return null
     */
    public function setMailMessage(MailMessage $message) {
        $this->message = $message;
    }

    /**
     * Invokes the implementation of the job
     * @param QueueManager $queueManager Instance of the queue manager
     * @return integer|null A timestamp from which time this job should be
     * invoked again or null when the job is done
     */
    public function run(QueueManager $queueManager) {
        if ($this->transport && $this->message) {
            $this->transport->send($this->message);
        }
    }

}
