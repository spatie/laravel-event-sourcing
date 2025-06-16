# Changelog

All notable changes to `laravel-event-sourcing` will be documented in this file:

## 7.12.0 - 2025-06-16

### What's Changed

* Fix restorePartialState() being incorrectly assigned to dynamic property by @nick-potts in https://github.com/spatie/laravel-event-sourcing/pull/507
* feat: add dynamic weight by @Bloemendaal in https://github.com/spatie/laravel-event-sourcing/pull/506

### New Contributors

* @Bloemendaal made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/506

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.11.3...7.12.0

## 7.11.3 - 2025-05-30

### What's Changed

* [Docs] Add Missing Version Property in Event Serializers by @devhammed in https://github.com/spatie/laravel-event-sourcing/pull/503
* Filter out non-existent classes from cached event handlers by @youyingxiang in https://github.com/spatie/laravel-event-sourcing/pull/504

### New Contributors

* @devhammed made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/503

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.11.2...7.11.3

## 7.11.2 - 2025-04-16

### What's Changed

* docs: fix method name inconsistencies and usage corrections by @vildanbina in https://github.com/spatie/laravel-event-sourcing/pull/498
* Case insensitive CouldNotPersistAggregate::invalidVersion detection by @nick-potts in https://github.com/spatie/laravel-event-sourcing/pull/501

### New Contributors

* @vildanbina made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/498
* @nick-potts made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/501

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.11.1...7.11.2

## 7.11.1 - 2025-03-21

### What's Changed

* Fix the incorrect time conversion in json_encode by @youyingxiang in https://github.com/spatie/laravel-event-sourcing/pull/499

### New Contributors

* @youyingxiang made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/499

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.11.0...7.11.1

## 7.11.0 - 2025-02-20

### What's Changed

* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-event-sourcing/pull/497

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.10.3...7.11.0

## 7.10.3 - 2025-01-22

### What's Changed

* feat: add eloquent stored event generic types for collection and query builder by @maartenpaauw in https://github.com/spatie/laravel-event-sourcing/pull/496

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.10.2...7.10.3

## 7.10.2 - 2024-12-20

### What's Changed

* Fix Remaining Nullable Parameter Definitions by @okaufmann in https://github.com/spatie/laravel-event-sourcing/pull/495

### New Contributors

* @okaufmann made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/495

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.10.1...7.10.2

## 7.10.1 - 2024-12-09

### What's Changed

* Explicitly mark variable nullable by @BertvanHoekelen in https://github.com/spatie/laravel-event-sourcing/pull/494

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.10.0...7.10.1

## 7.10.0 - 2024-12-02

### What's Changed

* PHP 8.4 explicit null types by @BertvanHoekelen in https://github.com/spatie/laravel-event-sourcing/pull/493

### New Contributors

* @BertvanHoekelen made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/493

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.9.1...7.10.0

## 7.9.1 - 2024-10-14

### What's Changed

* update command name to match class name by @PH7-Jack in https://github.com/spatie/laravel-event-sourcing/pull/490

### New Contributors

* @PH7-Jack made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/490

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.9.0...7.9.1

## 7.9.0 - 2024-10-10

### What's Changed

* Use class map name when querying event class by @maartenpaauw in https://github.com/spatie/laravel-event-sourcing/pull/476
* Register optimize commands by @erikgaal in https://github.com/spatie/laravel-event-sourcing/pull/489

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.8.0...7.9.0

## 7.8.0 - 2024-09-20

### What's Changed

* Replace 'projector' with 'reactor' where necessary. by @PHPGuus in https://github.com/spatie/laravel-event-sourcing/pull/485
* Only Dispatch If Event Has Async Handler by @damiantw in https://github.com/spatie/laravel-event-sourcing/pull/484

### New Contributors

* @PHPGuus made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/485

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.7.0...7.8.0

## 7.7.0 - 2024-07-10

### What's Changed

* Update installation-setup.md to match actual contents of config/event-sourcing.php by @inmanturbo in https://github.com/spatie/laravel-event-sourcing/pull/473
* add force option to replay command by @inmanturbo in https://github.com/spatie/laravel-event-sourcing/pull/474

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.6.2...7.7.0

## 7.6.2 - 2024-06-13

### What's Changed

* fix: internal links use /docs prefix by @rburgt in https://github.com/spatie/laravel-event-sourcing/pull/468
* Add reflection exception error message to InvalidStoredEvent exception by @deonvdv in https://github.com/spatie/laravel-event-sourcing/pull/469

### New Contributors

* @rburgt made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/468
* @deonvdv made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/469

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.6.1...7.6.2

## 7.6.1 - 2024-05-28

### What's Changed

* Use custom serializer while persisting events by @sebastiandittrich in https://github.com/spatie/laravel-event-sourcing/pull/464

### New Contributors

* @sebastiandittrich made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/464

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.6.0...7.6.1

## 7.6.0 - 2024-05-13

### What's Changed

* Pass specified aggregate uuid to resetState while replaying by @meijdenmedia in https://github.com/spatie/laravel-event-sourcing/pull/463

### New Contributors

* @meijdenmedia made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/463

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.5.0...7.6.0

## 7.5.0 - 2024-04-01

### What's Changed

* Add config to enforce every event has an alias. by @thettler in https://github.com/spatie/laravel-event-sourcing/pull/461

### New Contributors

* @thettler made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/461

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.4.2...7.5.0

## 7.4.2 - 2024-03-23

- fix AllowDynamicProperties to suppress PHP warnings on 8.2

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.4.1...7.4.2

## 7.4.1 - 2024-03-15

### What's Changed

* AllowDynamicProperties to suppress PHP warnings on 8.2 by @sebastiandedeyne in https://github.com/spatie/laravel-event-sourcing/pull/459

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.4.0...7.4.1

## 7.4.0 - 2024-03-09

### What's Changed

* L11 compatibility by @inmanturbo in https://github.com/spatie/laravel-event-sourcing/pull/458

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.10...7.4.0

## 7.3.10 - 2023-11-27

### What's Changed

* Address Typos and Make Grammatical Improvements in Documentation by @salehhashemi1992 in https://github.com/spatie/laravel-event-sourcing/pull/442
* Update actions/checkout to v4 by @salehhashemi1992 in https://github.com/spatie/laravel-event-sourcing/pull/441
* Fix Some Typos in Method Names, Namespaces, and PHPDoc Annotations by @salehhashemi1992 in https://github.com/spatie/laravel-event-sourcing/pull/443
* Document prepare events using abstract class instead of interface by @Sparclex in https://github.com/spatie/laravel-event-sourcing/pull/446
* Change the column type of snapshots.aggregate_version by @eschalks in https://github.com/spatie/laravel-event-sourcing/pull/447

### New Contributors

* @salehhashemi1992 made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/442
* @Sparclex made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/446
* @eschalks made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/447

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.9...7.3.10

## 7.3.9 - 2023-10-02

### What's Changed

- Fix snapshots in aggregate partials by @sonja-turo in https://github.com/spatie/laravel-event-sourcing/pull/406
- Ignore abstract event handlers by @27pchrisl in https://github.com/spatie/laravel-event-sourcing/pull/430

### New Contributors

- @sonja-turo made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/406

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.8...7.4.0

## 7.3.8 - 2023-09-05

### What's Changed

- Added an extra rejection to prevent events being passed to invalid handlers by @27pchrisl in https://github.com/spatie/laravel-event-sourcing/pull/438

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.7...7.3.8

## 7.3.7 - 2023-08-24

### What's Changed

- Support interfaces in aggregate root apply methods by @sebastiandedeyne in https://github.com/spatie/laravel-event-sourcing/pull/434

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.6...7.3.7

## 7.3.6 - 2023-08-17

### What's Changed

- Correcting Commands Documentation by @ChangingTerry in https://github.com/spatie/laravel-event-sourcing/pull/416
- Sort projectors by weight when replaying by @rapkis in https://github.com/spatie/laravel-event-sourcing/pull/425
- Replay events with custom stored event model by @avosalmon in https://github.com/spatie/laravel-event-sourcing/pull/410
- Corrects typos in docs by @DriverCat in https://github.com/spatie/laravel-event-sourcing/pull/429
- Make the aggregatePartial recordThat method fluent by @maartenpaauw in https://github.com/spatie/laravel-event-sourcing/pull/433

### New Contributors

- @ChangingTerry made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/416
- @DriverCat made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/429
- @maartenpaauw made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/433

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.5...7.3.6

## 7.3.5 - 2023-06-10

### What's Changed

- Document the use of the Projection class by @27pchrisl in https://github.com/spatie/laravel-event-sourcing/pull/409
- Expand documentation for projection factories by @Ahrengot in https://github.com/spatie/laravel-event-sourcing/pull/413
- Fix Symfony Serializer Deprecation Warnings by @damiantw in https://github.com/spatie/laravel-event-sourcing/pull/415

### New Contributors

- @Ahrengot made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/413
- @damiantw made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/415

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.4...7.3.5

## 7.3.4 - 2023-04-18

### What's Changed

- Fix documentation version cross-linking to other versions for v7. #396 by @Shkeats in https://github.com/spatie/laravel-event-sourcing/pull/402
- Fix README.md version cross-linking to other versions for v7. #396 by @Shkeats in https://github.com/spatie/laravel-event-sourcing/pull/403
- Fix preserving event order when dispatched from aggregate root via event handler by @daniser in https://github.com/spatie/laravel-event-sourcing/pull/405

### New Contributors

- @Shkeats made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/402

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.3...7.3.4

## 7.3.3 - 2023-01-25

### What's Changed

- Laravel 10.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-event-sourcing/pull/391

### New Contributors

- @laravel-shift made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/391

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.2...7.3.3

## 7.3.2 - 2023-01-03

### What's Changed

- Fixes bug with retrieving last event by @aidan-casey in https://github.com/spatie/laravel-event-sourcing/pull/384

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.1...7.3.2

## 7.3.1 - 2022-12-22

### What's Changed

- Refactor tests to pest by @AyoobMH in https://github.com/spatie/laravel-event-sourcing/pull/377
- Add PHP 8.2 Support by @patinthehat in https://github.com/spatie/laravel-event-sourcing/pull/379
- update document by @godkinmo in https://github.com/spatie/laravel-event-sourcing/pull/380
- Use the snapshot with the highest ID by @27pchrisl in https://github.com/spatie/laravel-event-sourcing/pull/381

### New Contributors

- @AyoobMH made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/377
- @patinthehat made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/379
- @godkinmo made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/380
- @27pchrisl made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/381

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.3.0...7.3.1

## 7.3.0 - 2022-09-12

### What's Changed

- Support weight property to event handlers by @sebastiandedeyne in https://github.com/spatie/laravel-event-sourcing/pull/365

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.2.4...7.3.0

## 7.2.4 - 2022-08-22

### What's Changed

- Update Reactor docs on how to get the aggregate uuid by @soarecostin in https://github.com/spatie/laravel-event-sourcing/pull/363
- Update event-sourcing:replay command to work with just one aggregate uuid by @soarecostin in https://github.com/spatie/laravel-event-sourcing/pull/362

### New Contributors

- @soarecostin made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/363

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.2.3...7.2.4

## 7.2.3 - 2022-07-29

### What's Changed

- Fix typo in ReadMe by @michael-rubel in https://github.com/spatie/laravel-event-sourcing/pull/353
- Update AggregateRoot::apply() to utilize acceptsTypes() function by @zackrowe in https://github.com/spatie/laravel-event-sourcing/pull/354

### New Contributors

- @michael-rubel made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/353
- @zackrowe made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/354

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.2.2...7.2.3

## 7.2.2 - 2022-07-14

### What's Changed

- Fix method annotations in Projection class by @daniser in https://github.com/spatie/laravel-event-sourcing/pull/350
- Exclude `tap` from possible handlers by @erikgaal in https://github.com/spatie/laravel-event-sourcing/pull/352

### New Contributors

- @daniser made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/350

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.2.1...7.2.2

## 7.2.1 - 2022-05-27

## What's Changed

- Fix URL to documentation about caching by @rodrigopedra in https://github.com/spatie/laravel-event-sourcing/pull/328
- Update cache_path in config by @lloricode in https://github.com/spatie/laravel-event-sourcing/pull/329
- Update Projector docs to reflect changes to getting the aggregate uuid by @RobHarveyDev in https://github.com/spatie/laravel-event-sourcing/pull/331
- Docs: createdAt() method is camel case by @inmanturbo in https://github.com/spatie/laravel-event-sourcing/pull/332
- Update migration file to closure by @lloricode in https://github.com/spatie/laravel-event-sourcing/pull/330
- Fix link to "getting familiar section" by @felixfrey in https://github.com/spatie/laravel-event-sourcing/pull/343
- Do not assert events given to FakeAggregateRoot as recorded by @itsmarsu in https://github.com/spatie/laravel-event-sourcing/pull/344

## New Contributors

- @lloricode made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/329
- @RobHarveyDev made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/331
- @inmanturbo made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/332
- @felixfrey made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/343
- @itsmarsu made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/344

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.2.0...7.2.1

## 7.2.0 - 2022-03-04

## What's Changed

- Adds query helpers for event properties by @aidan-casey in https://github.com/spatie/laravel-event-sourcing/pull/326
- Adds last event helper by @aidan-casey in https://github.com/spatie/laravel-event-sourcing/pull/327

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.0.1...7.2.0

## 7.2.0 - 2022-03-04

- Add `EloquentStoredEventQueryBuilder::lastEvent` (#327)
- Add `EloquentStoredEventQueryBuilder::wherePropertyIs` (#326)
- Add `EloquentStoredEventQueryBuilder::wherePropertyIsNot` (#326)

## 7.0.1 - 2022-03-03

## What's Changed

- Update testing-aggregates.md by @rmcdaniel in https://github.com/spatie/laravel-event-sourcing/pull/314
- Updated the requirement to Laravel 9 by @davidlapham in https://github.com/spatie/laravel-event-sourcing/pull/318
- Resolves issue with meta data updating on original event. by @aidan-casey in https://github.com/spatie/laravel-event-sourcing/pull/324

## New Contributors

- @rmcdaniel made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/314
- @davidlapham made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/318
- @aidan-casey made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/324

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/7.0.0...7.0.1

## 7.0.1 - 2022-03-03

- Fix for meta data updating on original event (#324)

## 6.0.5 - 2022-01-17

- Change `event_version` to `tinyint` in migration stub (#306)

## 6.0.4 - 2021-12-06

- Don't mutate original events in `FakeAggregateRoot::getRecordedEventsWithoutUuid` (#296)

## 6.0.3 - 2021-11-28

## What's Changed

- downgrade symfony finder by @morrislaptop in https://github.com/spatie/laravel-event-sourcing/pull/295

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/6.0.2...6.0.3

## 6.0.2 - 2021-11-26

- Skip retrieve on aggregate::fake (#294)

## 6.0.2 - 2021-11-26

- Skip retrieve on aggregate::fake (#294)

## 6.0.1 - 2021-11-26

- Fix for aggregate root testing without a database (#292)

## 6.0.0 - 2021-11-24

- Support PHP 8.1
- The `EventHandler` interface was changed in order to use the `spatie/better-types` package:

```diff
-    public function handles(): array;
+    public function handles(StoredEvent $storedEvent): bool;

-    public function handle(StoredEvent $event);
+    public function handle(StoredEvent $storedEvent): void;









































```
## 6.0.0 - 2021-??-??

- Support PHP 8.1
- The `EventHandler` interface was changed in order to use the `spatie/better-types` package:

```diff
-    public function handles(): array;
+    public function handles(StoredEvent $storedEvent): bool;

-    public function handle(StoredEvent $event);
+    public function handle(StoredEvent $storedEvent): void;









































```
## 5.0.8 - 2021-11-17

## What's Changed

- Fixed tests/VersionedEventTest.php::a_versioned_event_can_be_restored  by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/286
- Set minimum version of illuminate/database to ^8.34 by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/290

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/5.0.7...5.0.8

## 5.0.7 - 2021-11-17

## What's Changed

- Update introduction.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/266
- Update installation-setup.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/265
- Update introduction.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/264
- Update using-projectors-to-transform-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/263
- Update using-aggregates-to-make-decisions-based-on-the-past.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/262
- Update creating-and-configuring-projectors.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/261
- Update thinking-in-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/260
- Update writing-your-first-reactor.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/259
- Update writing-your-first-aggregate.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/258
- Update replaying-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/257
- Update storing-metadata.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/256
- fix broken link to the course by @macbookandrew in https://github.com/spatie/laravel-event-sourcing/pull/253
- Fix urls pointing to previous version by @quintenbuis in https://github.com/spatie/laravel-event-sourcing/pull/269
- [Docs] Add `EloquentStoredEvent` import to example by @stevebauman in https://github.com/spatie/laravel-event-sourcing/pull/273
- [Docs] Add missing opening bracket for `Account` model by @stevebauman in https://github.com/spatie/laravel-event-sourcing/pull/272
- [Docs] Fix wrong operator for onMoneySubtracted by @avosalmon in https://github.com/spatie/laravel-event-sourcing/pull/279
- Changed cursor() into lazyById() to preserve memory when working with large amount of events by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/284

## New Contributors

- @WouterBrouwers made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/266
- @macbookandrew made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/253
- @quintenbuis made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/269
- @stevebauman made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/273
- @avosalmon made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/279
- @etahamer made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/284

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/5.0.6...5.0.7

## 5.0.6 - 2021-09-12

- fix AggregateRoot return types for static analysis (#251)

## 5.0.5 - 2021-07-26

- Use `jsonb` in migration stubs instead of `json` (#237)

## 5.0.4 - 2021-06-15

- Fix visual glitch in `event-sourcing:list` command where event handlers wouldn't be shown

## 5.0.3 - 2021-06-14

- fix `$whenResult` (#227)

## 5.0.2 - 2021-06-14

- Support legacy `spatie/laravel-schemaless-attributes:^1.0` as well

## 5.0.1 - 2021-06-10

- move migrations to default location

## 5.0.0 - 2021-06-09

- Add `EloquentStoredEvent::query()-&amp;amp;gt;whereEvent(EventA::class, …)`
  
- 
- Add `EventQuery`
  
- 
- Add `AggregatePartial`
  
- 
- - If you're overriding an aggregate root's constructor, make sure to call `parent::__construct` from it
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- Add command bus and aggregate root handlers
  
- 
- Add `Projectionist::fake(OriginalReactor::class, FakeReactor::class)` ([#181](https://github.com/spatie/laravel-event-sourcing/discussions/181))
  
- 
- All event listeners are now registered in the same way: by looking at an event's type hint. This applies to all:
  
- 
- - Aggregate root `apply` methods
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- - Projection listeners
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- - Reactor listeners
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- - Event queries
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- Moved `Spatie\EventSourcing\Exception\CouldNotPersistAggregate` to `Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate`
  
- 
- Moved `Spatie\EventSourcing\Exception\InvalidEloquentSnapshotModel` to `Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentSnapshotModel`
  
- 
- Moved `Spatie\EventSourcing\Exception\InvalidEloquentStoredEventModel` to `Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentStoredEventModel`
  
- 
- Moved `Spatie\EventSourcing\Exception\MissingAggregateUuid` to `Spatie\EventSourcing\AggregateRoots\Exceptions\MissingAggregateUuid`
  
- 
- Moved `Spatie\EventSourcing\Exception\InvalidStoredEvent` to `Spatie\EventSourcing\StoredEvents\Exceptions\InvalidStoredEvent`
  
- 
- Dependency injection in handlers isn't supported anymore,  use constructor injection instead
  
- 
- `$storedEvent` and `$aggregateRootUuid` are no longer passed to event handler methods. Use `$event-&amp;amp;gt;storedEventId()` and `$event-&amp;amp;gt;aggregateRootUuid()` instead. ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
  
- 
- Rename `EloquentStoredEvent::query()-&amp;amp;gt;uuid()` to `EloquentStoredEvent::query()-&amp;amp;gt;whereAggregateRoot()`
  
- 
- Removed `AggregateRoot::$allowConcurrency`
  
- 
- Removed `$aggregateVersion` from `StoredEventRepository::persist`
  
- 
- Removed `$aggregateVersion` from `StoredEventRepository::persistMany`
  
- 
- Event handlers are no longer called with `app()-&amp;amp;gt;call()` ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
  
- 
- `$handlesEvents` on Projectors and Reactors isn't supported anymore
  
- 
- PHP version requirement is now `^8.0`
  
- 
- Laravel version requirement is now `^8.0`
  
- 

### A note on changed listeners

Since most code is probably already type hinting events, the listener change is likely to not have an impact on your code. It's good to know though that you don't have to worry about certain naming conventions any more:

- In **aggregate roots**, you don't have to prefix apply methods with `apply` anymore if you don't want to
- In **projectors**, you don't need a manual mapping anymore, neither does the event variable need to be called `$event`
- In **reactors**, you don't need a manual mapping anymore, neither does the event variable need to be called `$event`
- **Event queries** are a new concept and work in the same way

Here's an example:

```php
class MyProjector extends Projector
{
    public function anEventHandlerWithAnotherName(MyEvent $eventVariableWithAnotherName): void
    {
        // This handler will automatically handle `MyEvent`
    }
}











































```
Note that `__invoke` in projectors and reactors works the same way, it's automatically registered based on the type hinted event.

## 4.10.2 - 2021-05-04

- Add missing config key in config stub (#203)

## 4.10.1 - 2021-04-21

- Also store aggregate root version when one event is persisted

## 4.10.0 - 2021-04-21

- Deprecate `AggregateRoot::$allowConcurrency`
- Fix for race condition in aggregate roots (#170), you will need to run a migration to be able to use it:

```php
public function up()
{
    Schema::table('stored_events', function (Blueprint $table) {
        $table->unique(['aggregate_uuid', 'aggregate_version']);
    });
}











































```
**Note**: if you run this migration, all aggregate roots using `$allowConcurrency` will not work any more.

## 4.9.0 - 2021-03-10

- Make base path configurable (#202)

## 4.8.0 - 2021-01-28

- Add support for asserting events with a closure

## 4.7.2 - 2021-01-28

- Fix for broken dependency in 4.7.1

## 4.7.1 - 2021-01-21

- Fix for array serialization (#189)

## 4.7.0 - 2020-12-02

- add support for PHP 8

## 4.6.1 - 2020-10-23

- remove unused `replay_chunk_size` config value

## 4.6.0 - 2020-10-21

- allow protected apply methods (#136)

## 4.5.3 - 2020-10-02

- re-use existing instance of `ShouldBeStored` when possible (#158)

## 4.5.2 - 2020-10-02

- fix Paths and Reference URL in event-sourcing.php config file (#159)

## 4.5.1 - 2020-09-27

- added `loadUuid` (#156)

## 4.5.0 - 2020-09-25

- make normalizers configurable (#153)

## 4.4.0 - 2020-09-18

- Support `then` for aggregate root fakes (#154)

## 4.3.1 - 2020-09-09

- Support Laravel 8

## 4.3.0 - 2020-08-24

- support Carbon dates in events (#137)

## 4.2.0 - 2020-08-06

- allow events to be dispatched from an aggregate root (#135)

## 4.1.0 - 2020-08-03

- add assertion that specific event is recorded (#134)

## 4.0.2 - 2020-07-29

- config style fix

## 4.0.1 - 2020-07-29

- add `snapshot_model` config key

## 4.0.0 - 2020-07-22

- projectors now are abstract classes instead of interfaces
- reactors can now be easily defined by extending the reactor base class
- projectors and reactors can be marked as async by implementing the `ShouldQueue` marker interface
- events that extend `ShouldBeStored` now can retrieve the aggregate root uuid using `aggregateRootUuid()`
- the package has been restructured. Namespaces of most classes have been updated.
- events that extend `ShouldBeStored` can now handle metadata using `metaData` and `setMetaData`
- aggregate roots can now be persisted without calling event handlers using `persistWithoutApplyingToEventHandlers`
- the projectionist can now handle manually specified events using `handleStoredEvents`
- added `persistAggregateRootsInTransaction` to `AggregateRoot`
- you can now get the `uuid` of an aggregate root using the `uuid()` method
- the `reset` method has been removed on projectors
- the `fake` method on an aggregate root now accepts a uuid instead of an array of events
- the `meta_data` property on `StoredEvent` is now an array or a string instead of `SchemalessAttributes`
- apply methods on aggregates can now make use of method injection
- pass metadata to serializer to allow events to be upgraded (#112)

## 3.2.3 - 2020-07-14

- default to `BigIncrements` on package table stubs (#124)

## 3.2.2  - 2020-07-14

- replace model where clause with uuid model scope (#123)

## 3.2.1 - 2020-07-09

- config file comment corrections (#121)

## 3.2.0 - 2020-06-30

- expose `aggregate_version` of `StoredEvent` (#115)

## 3.1.8 - 2020-06-28

- use `app` helper (#117)

> > > > > > > master

## 3.1.7 - 2020-06-18

- allow aggregate roots to have dependencies in constructor (#111)

## 3.1.6 - 2020-06-17

- wrong tag, nothing changed

## 3.1.5 - 2020-05-23

- only include public properties of the aggregate when snapshotting (#105)

## 3.1.4 - 2020-05-07

- simplify snapshot dates

## 3.1.3 - 2020-04-29

- add `static` return type docblock for `AggregateRoot::retrieve`

## 3.1.2 - 2020-04-07

- make sure `created_at` is filled when creating a snapshot

## 3.1.1 - 2020-03-21

- expose `AggregateRoot` for testing state (#75)

## 3.1.0 - 2020-03-03

- add support for Laravel 7

## 3.0.4 - 2020-02-23

- fix for serializing events that use immutable datetime objects (#67)

## 3.0.3 - 2020-02-18

- fixes for Lumen

## 3.0.2 - 2020-02-14

- only replace the first instance of the `basePath` in `DiscoversEventHandlers` (#62)

## 3.0.1 - 2020-02-14

- publish snapshots migration

## 3.0.0 - 2020-02-07

- add the ability to snapshot aggregates
- make all classes non-final
- do not allow concurrent persist on an aggregate by default

## 2.1.0 - 2020-02-07

- add `countAllStartingFrom`

## 2.0.1 - 2020-01-20

- do not dispatch job when there is nothing to be performed on queue

## 2.0.0 - 2019-12-02

- drop PHP 7.3

## 1.0.4 - 2019-11-20

- fix replay from specified event id (#33)

## 1.0.3 - 2019-11-01

- provide docblocks to AggregateRoot class (#31)

## 1.0.2 - 2019-10-27

- implemented missing HandleDomainEventJob interface
- use a UUID field when possible for storing UUIDs

## 1.0.1 - 2019-10-11

- fix an issue with encoding the `event_properties` when they're already a string

## 1.0.0 - 2019-09-20

- initial release

This package supercedes [spatie/laravel-event-projector](https://github.com/spatie/laravel-event-projector)

To learn how to upgrade from laravel-event-projector v3 to laravel-event-sourcing v1 , read [our upgrade guide](https://github.com/spatie/laravel-event-sourcing/blob/master/UPGRADING.md)
