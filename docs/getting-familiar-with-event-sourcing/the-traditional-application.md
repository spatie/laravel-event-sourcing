---
title: The traditional application
weight: 2
---

In a traditional application, you're probably going to use a database to hold the state of your application. Whenever you want to update a state, you're simply going to overwrite the old value. That old value isn't accessible anymore. Your application only holds the current state.

You might think that you still have the old state inside your backups. But they don't count. Your app probably can't, nor should it, make decisions on data inside those backups.

<figure class="scheme">
    <figcaption class="scheme_caption">
        First, we write value X
    </figcaption>
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/db-01.svg">
</figure>

<figure class="scheme">
    <figcaption class="scheme_caption">
        Next, we overwrite X by Y. X cannot be accessed anymore.
    </figcaption>
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/db-02.svg">
</figure>

Here's a demo application that uses a traditional architecture. Inside the [`AccountsController`](https://github.com/spatie/larabank-traditional/blob/9cc38858c50a4f2ac5a36e64719c891fac85bd3f/app/Http/Controllers/AccountsController.php) we are just going to [create new accounts](https://github.com/spatie/larabank-traditional/blob/9cc38858c50a4f2ac5a36e64719c891fac85bd3f/app/Http/Controllers/AccountsController.php#L19-L27) and [update the balance](https://github.com/spatie/larabank-traditional/blob/9cc38858c50a4f2ac5a36e64719c891fac85bd3f/app/Http/Controllers/AccountsController.php#L29-L36). We're using an eloquent model to update the database. Whenever we change the balance of the account, the old value is lost.
