# MCF Queue

## Overview

MCF Queue is the framework's automatic integration with Laravel's native database queue.

The design intentionally stays **on top of Laravel Queue** rather than replacing it.

Laravel remains responsible for creating, storing, reserving, executing, and completing queued jobs. MCF adds an automatic listener that starts a short-lived background process after Laravel successfully queues a job.

---

## Queue Table

MCF Queue uses Laravel's standard database queue table:

```text
jobs
```

The table comes from Laravel's normal queue migration. The MCF framework also includes the required queue migration as part of its framework/database setup.

MCF does **not** create a separate queue table and does not replace Laravel's database queue.

The database queue remains the source of truth:

```text
Application
    ↓
Laravel Queue
    ↓
jobs table
```

---

## Email Queue Is the Default

MCF mail delivery uses Laravel Queue by default.

```php
McfMail::send(
    $email,
    new ExampleMail(),
);
```

This uses Laravel's queued mail mechanism:

```php
Mail::to($email)->queue($mail);
```

The intended lifecycle is:

```text
HTTP Request
    ↓
McfMail::send()
    ↓
Laravel Mail Queue
    ↓
INSERT into jobs
    ↓
HTTP Request continues/finishes
```

After Laravel queues the job, MCF's listener starts independent background processing.

---

## Direct Email

MCF also provides an explicit synchronous API:

```php
McfMail::direct(
    $email,
    new ExampleMail(),
);
```

This uses:

```php
Mail::to($email)->send($mail);
```

Use `direct()` only when synchronous delivery is intentionally required.

The default MCF email path remains queued.

---

## Delayed Email

MCF supports delayed queued delivery:

```php
McfMail::later(
    60,
    $email,
    new ExampleMail(),
);
```

This uses Laravel's queued `later()` mechanism. Laravel remains responsible for the job's availability and normal queue lifecycle.

---

# Zero-Configuration Queue Listener

MCF registers its queue listener automatically through the framework service provider.

The application developer does not need to create a custom listener.

The normal flow is:

```text
Laravel queues a job
        ↓
JobQueued event
        ↓
MCF Queue Listener
        ↓
Independent background process
        ↓
queue:work --once
        ↓
Laravel executes the job
        ↓
Process exits
```

The normal MCF usage does not require the developer to manually run:

```bash
php artisan queue:work
```

---

# Background Processing Model

MCF does not maintain a permanent worker.

The current implementation starts an independent short-lived process for a queued job notification.

The process runs:

```bash
php artisan queue:work --once
```

The `--once` option means the process:

1. Starts independently from the HTTP request.
2. Takes one available queue job.
3. Executes it using Laravel's normal queue worker.
4. Lets Laravel complete the job lifecycle.
5. Exits.

Conceptually:

```text
Job #1
   ↓
Background Process #1
   ↓
queue:work --once
   ↓
Execute Job #1
   ↓
Process exits

Job #2
   ↓
Background Process #2
   ↓
queue:work --once
   ↓
Execute Job #2
   ↓
Process exits
```

There is no permanent MCF worker consuming resources while the queue is empty.

---

# Laravel Queue Is Not Replaced

MCF intentionally does not replace Laravel's queue engine.

MCF does not:

- Replace `DatabaseQueue`.
- Replace Laravel's `QueueManager`.
- Create a custom `jobs` table.
- Change Laravel job payloads.
- Change Laravel job serialization.
- Implement a separate mail queue.
- Execute queued mail inside the original HTTP request.

The MCF layer is intentionally thin:

```text
Laravel Queue
      ↑
      │
MCF Listener
      │
      ↓
Background Process
```

Laravel remains the queue engine and the source of truth.

---

# Request Lifecycle

Queueing and processing are separate operations.

## Queueing

The application calls:

```php
Mail::to($email)->queue($mail);
```

Laravel stores the job:

```text
jobs
└── queued mail job
```

The application request should not wait for the mail job to be executed.

## Processing

Separately, the MCF listener starts the background process:

```text
jobs
   ↓
queue:work --once
   ↓
Laravel executes the job
   ↓
Laravel completes/deletes the job
```

The background process must not block the original HTTP request.

---

# Multiple Jobs

If an application queues several emails:

```php
foreach ($teachers as $teacher) {
    McfMail::send(
        $teacher->email,
        new ExampleMail(),
    );
}
```

Laravel creates separate jobs:

```text
jobs
├── Job 1
├── Job 2
├── Job 3
└── Job 4
```

Each `JobQueued` notification can start an independent background process:

```text
Job 1 → Process 1 → queue:work --once → exit
Job 2 → Process 2 → queue:work --once → exit
Job 3 → Process 3 → queue:work --once → exit
Job 4 → Process 4 → queue:work --once → exit
```

The current implementation intentionally favors simple, short-lived processing.

---

# Resource Considerations

Because the current implementation can start one short-lived process for each queued job notification, a very large burst can create many PHP processes simultaneously.

For normal workloads this provides simple parallel processing.

For high-volume workloads, a future MCF version may introduce a configurable concurrency limit, for example:

```text
max_processes = 4
```

which would limit the number of simultaneous background processes.

The important distinction is that short-lived processes release their resources when they exit; however, the peak resource usage can still increase if many processes are started simultaneously.

---

# Hosting Requirements

The automatic MCF background process depends on the hosting environment allowing PHP to create background processes.

Windows uses Windows process execution.

Unix/Linux uses background process execution.

Generally suitable environments include:

- VPS.
- Dedicated servers.
- Cloud servers.
- Hosting environments that permit PHP process execution.

Some shared hosting environments disable functions such as `proc_open` or `exec`. In such environments the automatic MCF background listener may not be able to start its process.

Laravel's underlying queue behavior remains unchanged.

---

# Developer Experience

The intended developer experience is zero queue-listener configuration.

The developer normally writes:

```php
McfMail::send(
    $email,
    new ExampleMail(),
);
```

MCF automatically provides:

```text
Laravel Queue
    ↓
jobs table
    ↓
JobQueued
    ↓
MCF Listener
    ↓
Independent background process
    ↓
queue:work --once
    ↓
Laravel executes Job
    ↓
Job completes
    ↓
Process exits
```

No custom listener is required in the application.

No permanent worker needs to be configured for the normal MCF automatic behavior.

If synchronous delivery is explicitly required:

```php
McfMail::direct(
    $email,
    new ExampleMail(),
);
```

---

# Design Principles

1. **Laravel Queue remains the source of truth.**
2. **The `jobs` table remains Laravel's queue storage.**
3. **Queued mail is the default MCF mail behavior.**
4. **Direct mail is explicit through `direct()`.**
5. **The listener requires zero application-side configuration.**
6. **Background processing must not block the HTTP request.**
7. **MCF does not replace Laravel's queue engine.**
8. **Background processes are short-lived.**
9. **Future concurrency controls can be added without changing the application's queue API.**

---

# Summary

MCF Queue provides an automatic bridge between Laravel's existing database queue and short-lived background processing.

```text
Application
    ↓
McfMail::send()
    ↓
Laravel Mail Queue
    ↓
jobs table
    ↓
JobQueued
    ↓
MCF Listener
    ↓
Independent Background Process
    ↓
queue:work --once
    ↓
Laravel executes Job
    ↓
Job completed
    ↓
Process exits
```

The developer gets queued email delivery by default with zero listener/worker setup, while Laravel continues to own queue storage and the complete job lifecycle.

For synchronous behavior, the developer explicitly chooses:

```php
McfMail::direct(...)
```

The queued API remains the standard and recommended delivery path.
