---
title: Temporal Query
weight: 12
---

One feature of event sourcing is able to perform a temporal query. A temporal query allows us to read
an aggregate's state at any given point of time. 

You can retrieve an aggregate root's state at any point of time by calling:

```php
AggregateRoot::retrieveUntil($uuid, $tillDateTime)
```
