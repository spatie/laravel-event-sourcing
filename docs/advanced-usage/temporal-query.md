---
title: Temporal Query
weight: 12
---

One feature of event sourcing is able to perform a temporal query quoted by Martin Fowler:

```
Temporal Query: We can determine the application state at any point in time. 
Notionally we do this by starting with a blank state and rerunning the events up to a particular time or event. 
We can take this further by considering multiple time-lines (analogous to branching in a version control system).
```

You can retrieve an aggregate root's state at any point of time by calling:

```php
AggregateRoot::retrieveUntil($uuid, $tillDateTime)
```
