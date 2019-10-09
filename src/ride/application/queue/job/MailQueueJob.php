<?php

namespace ride\application\queue\job;

use ride\library\mail\exception\MailException;
use ride\library\mail\MailMessage;
use ride\library\queue\job\AbstractQueueJob;
use ride\library\queue\QueueManager;

use \Exception;

/**
 * Queue job to send a mail
 */
class MailQueueJob extends AbstractQueueJob {

    /**
     * Instance of the message to send
     * @var \ride\library\mail\MailMessage
     */
    private $message;

    /**
     * Time in seconds after which a retry should be attempted
     * @var integer
     */
    private $retryTime = 300;

    /**
     * Sets the retry time for this message
     * @param integer $retryTime Time in seconds
     */
    public function setRetryTime($retryTime) {
        if (!is_numeric($retryTime) || $retryTime < 0) {
            throw new Exception('Could not set mail retry time: positive numeric value expected');
        }

        $this->retryTime = $retryTime;
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
        if (!$this->message) {
            return;
        }

        $dependencyInjector = $queueManager->getSystem()->getDependencyInjector();
        $id = null;
        $arguments = null;
        $invokeCalls = false;
        $exclude = array(
            'ride\\application\\mail\\transport\\QueueMailTransport' => array(
                'queue' => true
            ),
        );

        $transport = $dependencyInjector->get('ride\\library\\mail\\transport\\Transport', $id, $arguments, $invokeCalls, $exclude);

        try {
            $transport->send($this->message);
        } catch (Exception $exception) {
            if ($exception instanceof MailException) {
                $previousException = $exception->getPrevious();

                if ($previousException && !$previousException instanceof MailException) {
                    // mail failed, try again in an hour
                    return time() + $this->retryTime;
                }
            }

            throw $exception;
        }
    }

}
