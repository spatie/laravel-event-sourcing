<?php

return [

    /*
     * The class name of the model responsible for storing events.
     */
    'stored_event_model' => \Spatie\EventProjector\Models\StoredEvent::class,

    /*
     * The class responsible for serializing events.
     * It should implement Spatie\EventProjector\EventSerializers\Serializer
     */
    'event_serializer' => \Spatie\EventProjector\EventSerializers\JsonEventSerializer::class,
];
