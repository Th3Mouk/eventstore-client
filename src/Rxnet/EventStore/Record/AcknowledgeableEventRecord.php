<?php

declare(strict_types=1);

/*
 * This file is part of the RxNET project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rxnet\EventStore\Record;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Rx\Observable;
use Rxnet\EventStore\Data\PersistentSubscriptionAckEvents;
use Rxnet\EventStore\Data\PersistentSubscriptionNakEvents;
use Rxnet\EventStore\Message\MessageType;
use Rxnet\EventStore\Writer;

final class AcknowledgeableEventRecord extends EventRecord
{
    /**
     * Client unknown on action. Let server decide
     */
    const NACK_ACTION_UNKNOWN = 0;
    /**
     * Park message do not resend. Put on poison queue
     * Don't retry the message, park it until a request is sent to reply the parked messages
     */
    const NACK_ACTION_PARK = 1;
    /**
     * Explicitly retry the message
     */
    const NACK_ACTION_RETRY = 2;
    /**
     * Skip this message do not resend do not put in poison queue
     */
    const NACK_ACTION_SKIP = 3;
    /**
     * Stop the subscription.
     */
    const NACK_ACTION_STOP = 4;

    protected $binaryId;
    protected $correlationID;
    protected $writer;
    protected $group;
    protected $linkedEvent;

    public function __construct(
        \Rxnet\EventStore\Data\EventRecord $event,
        string $correlationID,
        string $group,
        Writer $writer,
        \Rxnet\EventStore\Data\EventRecord $linkedEvent = null
    ) {
        $this->binaryId = ($linkedEvent) ? $linkedEvent->getEventId() : $event->getEventId();

        parent::__construct($event);

        if ($linkedEvent) {
            $this->stream_id = $linkedEvent->getEventStreamId();
            $this->number = $linkedEvent->getEventNumber();
        }

        $this->correlationID = $correlationID;
        $this->writer = $writer;
        $this->group = $group;
        $this->linkedEvent = $linkedEvent;
    }

    public function setLinkedEvent(EventRecord $record): self
    {
        $this->linkedEvent = $record;
        return $this;
    }

    public function getLinkedEvent()
    {
        if ($this->linkedEvent) {
            return $this->linkedEvent;
        }
        return $this->data;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @throws \Exception
     */
    public function ack(): Observable
    {
        $ack = new PersistentSubscriptionAckEvents();
        $ack->setSubscriptionId($this->stream_id . "::" . $this->group);

        $events = new RepeatedField(GPBType::BYTES);
        $events[] = $this->binaryId;

        $ack->setProcessedEventIds($events);

        return $this->writer->composeAndWrite(
            MessageType::PERSISTENT_SUBSCRIPTION_ACK_EVENTS,
            $ack,
            $this->correlationID
        );
    }

    /**
     * @throws \Exception
     */
    public function nack($action = self::NACK_ACTION_UNKNOWN, $msg = ''): Observable
    {
        $nack = new PersistentSubscriptionNakEvents();
        $nack->setSubscriptionId($this->stream_id . "::" . $this->group);
        $nack->setAction($action);
        $nack->setMessage($msg);

        $events = new RepeatedField(GPBType::BYTES);
        $nack->setProcessedEventIds($events);
        $events[] = $this->binaryId;

        return $this->writer->composeAndWrite(
            MessageType::PERSISTENT_SUBSCRIPTION_NACK_EVENTS,
            $nack,
            $this->correlationID
        );
    }
}
