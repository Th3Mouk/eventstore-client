<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: ClientMessageDtos.proto

namespace Rxnet\EventStore\Data\DeletePersistentSubscriptionCompleted;

/**
 * Protobuf type <code>Rxnet.EventStore.Data.DeletePersistentSubscriptionCompleted.DeletePersistentSubscriptionResult</code>
 */
class DeletePersistentSubscriptionResult
{
    /**
     * Generated from protobuf enum <code>Success = 0;</code>
     */
    const Success = 0;
    /**
     * Generated from protobuf enum <code>DoesNotExist = 1;</code>
     */
    const DoesNotExist = 1;
    /**
     * Generated from protobuf enum <code>Fail = 2;</code>
     */
    const Fail = 2;
    /**
     * Generated from protobuf enum <code>AccessDenied = 3;</code>
     */
    const AccessDenied = 3;
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DeletePersistentSubscriptionResult::class, \Rxnet\EventStore\Data\DeletePersistentSubscriptionCompleted_DeletePersistentSubscriptionResult::class);
