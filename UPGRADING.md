# Upgrading

Because there are many breaking changes we cannot give you a waterproof list of steps to provide. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v1 to v2

- Add a nullable `aggregate_uuid` field in the `stored_events` table
- Delete the `projector_statuses` table
- Remove all options in the config file not present in the config file that ships with v2
- In v1 streams were used to track if events came in the right order.  All support for event streams has been removed. If for your projectors the order of events is imports, use a queued projector.
- v1 tracked which events were already processed by a given event handler. In v2 all functionality around projector statusses is removed. It's now your own resposibility that you give all projectors the right events. 
