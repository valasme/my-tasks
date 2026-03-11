# MyTasks

<p align="center">
	<img src="public/og.png" alt="MyTasks banner" width="100%" />
</p>

<p align="center">
	Effortless task management for small teams, built with Laravel & Livewire.
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

MyTasks is a task management application designed for small teams. It provides employee management, attendance tracking, task assignment, critical task highlighting, notes, and due payment tracking — without enterprise complexity.

## Features

- **Employees** — Directory with contact info, department, job title, and work schedules.
- **Attendance** — Daily attendance tracking with per-employee records.
- **Tasks** — Task management with status, priority, due dates, and employee assignment.
- **Critical Tasks** — Highlight urgent open tasks with configurable filter and sort modes.
- **Notes** — Quick notes system with title, description, and search.
- **Due Payments** — Track upcoming pay dates with urgency cues and payment status.
- Authentication powered by Laravel Fortify.
- Responsive UI using Livewire, Flux UI, and Tailwind CSS.

## Tech Stack

- Laravel 12
- Livewire 4 + Flux UI
- Tailwind CSS 4
- Laravel Fortify
- PHP 8.5

## Requirements

- PHP 8.2+ (PHP 8.5 recommended)
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
