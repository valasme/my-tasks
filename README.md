# MyTasks

<p align="center">
	<img src="public/og.png" alt="MyTasks banner" width="100%" />
</p>

<p align="center">
	A productivity-focused task manager with GTD workflows, time blocking, and analytics — built with Laravel & Livewire.
</p>

---

> ⚠️ **Heavy Development in Progress**
>
> This repository is under active development and is not currently
> suitable for any kind of production or real-world usage. The database
> schema, APIs, and features change frequently; your data may be lost at
> any time. Only run this project if you understand it is _very_ early
> and you are prepared to rebuild from scratch when the next update
> lands.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
    - [Windows](#windows)
    - [Linux](#linux)
    - [macOS](#macos)
- [Running the App](#running-the-app)
- [Demo Data](#demo-data)
- [Authentication and Access](#authentication-and-access)
- [Testing](#testing)

## Overview

MyTasks is a productivity-focused task management application. Beyond basic task CRUD, it incorporates GTD (Getting Things Done) workflows, time blocking, weekly reviews, mood logging, and analytics — giving you a comprehensive system for staying organized and motivated.

## Features

### Core Task Management

- **Tasks** — Full CRUD with statuses (pending, in progress, completed), priorities (low, medium, high, urgent), due dates, and estimated minutes.
- **Workspace Grouping** — Organize tasks into named workspaces by project or domain.
- **Schedule Status Tracking** — Each task automatically derives a schedule status (pending, missed, completed on time, completed late) from its due date and completion timestamp.
- **Due Tasks View** — A dedicated view surfacing overdue and upcoming tasks that need immediate attention, with sorting options.
- **Recurring Daily Tasks** — Mark tasks as recurring with configurable daily repetition counts.

### GTD Workflows

- **Inbox (Quick Capture)** — Rapidly capture ideas into an inbox. Review and convert items into full tasks later, following the GTD "collect then process" methodology.
- **Someday/Maybe List** — Park tasks you might want to do eventually but aren't actionable now. Activate them into regular tasks when ready.

### Time Blocking

- **Time Blocks** — Schedule blocks of time on specific dates with start and end times, optionally linked to a task. Supports a day-view calendar for daily planning.

### Analytics & Insights

- **Productivity Analytics** — View completion ratios and tasks completed per day (last 14 days).
- **Mood & Energy Logging** — Log your energy level (energized, neutral, drained) optionally tied to a task, with notes. Correlate well-being with productivity over time.
- **Weekly Reviews** — Record end-of-week summaries capturing tasks completed, missed, and created, plus freeform notes. Includes computed completion rate.

### Authentication & Security

- Authentication powered by Laravel Fortify (login, registration, email verification, password reset).
- Two-factor authentication support.
- User settings for profile, appearance, security, and account deletion.

### UI

- Responsive UI using Livewire, Flux UI, and Tailwind CSS.

## Tech Stack

- Laravel 12
- Livewire 4 + Flux UI
- Tailwind CSS 4
- Laravel Fortify
- PHP 8.5

## Requirements

- PHP 8.5 (PHP 8.5 recommended)
- Composer (latest version recommended)
- Node.js and npm (Node.js 25 recommended)
- A Laravel-supported database (SQLite, MySQL, etc. SQLite is
  convenient for development)

## Quick Start

### Windows

```cmd
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Linux

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### macOS

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Running the App

If you're on macOS or Windows and using Laravel Herd, the site will be
served automatically at the `.test` domain.

For manual development you can use:

```bash
composer run dev
```

This command starts the Laravel server, queue listener, and Vite dev
server together.

## Demo Data

```bash
php artisan db:seed
```

Seeds create a test user (`test@example.com`) and any other sample
records needed for development.

## Authentication and Access

- You must be logged in to access the dashboard or any app screens.
- Fortify handles login, registration, password resets, and profile
  updates. Two-factor authentication is scaffolded but may not be live.

## Testing

Run the test suite with:

```bash
php artisan test --compact
```

Make sure to run the specific tests you modify during development.
