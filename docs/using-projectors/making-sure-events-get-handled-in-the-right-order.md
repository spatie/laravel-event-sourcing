---
title: Making sure events get handled in the right order
weight: 4
---

By default all events are handled in a synchronous manner. This means that if you fire off an event in a request, all projectors will get called in the same request.

## Handling events in a queue

A queue can be used to guarantee that all events get passed to projectors in the right order. If you want a projector to handle events in a queue then simply add the `Illuminate\Contracts\Queue\ShouldQueue` interface to your projector just like you would a Job. 

A useful rule of thumb is that if your projectors aren't producing data that is consumed in the same request as the events are fired, you should let your projector implement `Illuminate\Contracts\Queue\ShouldQueue`.

You can set the name of the queue connection in the `queue` key of the `event-sourcing` config file.  You should make sure that the queue will process only one job at a time.

In a local environment, where events have a very low chance of getting fired concurrently, it's probably ok to just use the `sync` driver.
