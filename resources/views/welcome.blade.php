<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Welcome')])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">

        {{-- Header --}}
        <header class="fixed inset-x-0 top-0 z-50 h-[120px] border-b border-zinc-200 bg-zinc-50">
            <div class="mx-auto flex h-full max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3 underline-offset-4 hover:underline">
                    <img src="{{ asset('favicon.png') }}" alt="MyTasks" class="h-8 w-8 object-contain">
                    <span class="text-base font-semibold tracking-wide text-zinc-900">MyTasks</span>
                </a>

                {{-- Desktop nav --}}
                <nav class="hidden items-center gap-1 sm:flex sm:gap-2">
                    <a
                        href="https://github.com/valasme/my-chat"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                    </a>

                    @if (Route::has('login'))
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                            >
                                Sign in
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                                >
                                    Register
                                </a>
                            @endif
                        @endauth
                    @endif
                </nav>

                {{-- Mobile hamburger button --}}
                <button
                    id="open-mobile-menu"
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-zinc-500 transition hover:text-zinc-900 sm:hidden"
                    aria-label="Open menu"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                >
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
            </div>

            {{-- Full-screen mobile menu --}}
            <div
                id="mobile-menu"
                class="fixed inset-0 z-[100] hidden bg-zinc-50 sm:hidden"
                inert
            >
                <div class="relative h-full w-full">
                    <button
                        id="close-mobile-menu"
                        type="button"
                        class="absolute right-5 top-10 z-20 inline-flex items-center justify-center rounded-md p-2 text-zinc-500 transition hover:text-zinc-900"
                        aria-label="Close menu"
                    >
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>

                    {{-- Centered links --}}
                    <nav class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-8">
                        @if (Route::has('login'))
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    data-menu-link
                                    class="text-2xl font-medium text-zinc-900 underline-offset-4 transition hover:underline"
                                >
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    data-menu-link
                                    class="text-2xl font-medium text-zinc-900 underline-offset-4 transition hover:underline"
                                >
                                    Sign in
                                </a>
                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        data-menu-link
                                        class="text-2xl font-medium text-zinc-900 underline-offset-4 transition hover:underline"
                                    >
                                        Register
                                    </a>
                                @endif
                            @endauth
                        @endif
                        <a
                            href="https://github.com/valasme/my-chat"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-menu-link
                            class="inline-flex items-center gap-2 text-2xl font-medium text-zinc-900 underline-offset-4 transition hover:underline"
                        >
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                            GitHub
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        {{-- Spacer for fixed header --}}
        <div class="h-[120px]"></div>

        <main class="mx-auto max-w-6xl px-5 sm:px-8">

            {{-- Hero --}}
                <section class="flex flex-col items-center py-20 text-center sm:py-28">
                <h1 class="text-5xl font-bold tracking-tight text-zinc-900 sm:text-6xl md:text-7xl">MyTasks</h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-zinc-500">
                    Effortless task management for small teams. Create, update, and track tasks with priorities and due dates.
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-6 py-2.5 text-sm font-medium text-zinc-700 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                            >
                                Open Dashboard
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-6 py-2.5 text-sm font-medium text-zinc-700 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                                >
                                    Get Started
                                </a>
                            @endif
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-md px-6 py-2.5 text-sm font-medium text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                            >
                                Sign in
                            </a>
                        @endauth
                    @endif
                </div>
            </section>

            {{-- Features grid: 3 rows x 2 columns --}}
            <section class="py-12">
                <p class="mb-8 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Core Feature</p>
                <div class="grid gap-4 sm:grid-cols-1">
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Tasks</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Create, assign, prioritize, and track tasks with due dates and statuses.</p>
                    </div>
                </div>
            </section>

            {{-- OG Image --}}
            <section class="py-12">
                <div class="overflow-hidden rounded-md border border-zinc-200">
                    <img
                        src="{{ asset('og.png') }}"
                        alt="MyTasks preview"
                        class="block w-full"
                        loading="lazy"
                    >
                </div>
            </section>

            {{-- Tech stack grid: 3 rows x 2 columns --}}
            <section class="py-12">
                <p class="mb-8 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Tech Stack</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Laravel 12</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Full-stack PHP framework powering the backend and routing.</p>
                    </div>
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Livewire 4</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Reactive server-driven UI components without writing JavaScript.</p>
                    </div>
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Flux UI</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Official Livewire component library for polished interfaces.</p>
                    </div>
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Tailwind CSS 4</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Utility-first CSS framework for rapid, responsive styling.</p>
                    </div>
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">Laravel Fortify</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Headless authentication backend with 2FA and password reset.</p>
                    </div>
                    <div class="flex h-40 flex-col justify-center rounded-md border border-zinc-200 bg-white p-8">
                        <p class="text-sm font-semibold text-zinc-900">PHP 8.5</p>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500">Latest PHP runtime with modern language features and performance.</p>
                    </div>
                </div>
            </section>

        </main>

        {{-- Footer --}}
        <footer class="mt-12 border-t border-zinc-200">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-6 px-5 py-10 sm:flex-row sm:justify-between sm:px-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('favicon.png') }}" alt="MyTasks" class="h-6 w-6 object-contain">
                    <span class="text-sm font-medium text-zinc-500">MyTasks</span>
                </div>

                <p class="text-xs text-zinc-400" x-data x-text="new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })"></p>

                <a
                    href="https://github.com/valasme/my-chat"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-xs text-zinc-500 underline-offset-4 transition hover:text-zinc-900 hover:underline"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                    GitHub
                </a>
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    const openButton = document.getElementById('open-mobile-menu');
                    const closeButton = document.getElementById('close-mobile-menu');
                    const mobileMenu = document.getElementById('mobile-menu');

                    if (!openButton || !closeButton || !mobileMenu) {
                        console.warn('Mobile menu elements are missing.');
                        return;
                    }

                    if (mobileMenu.dataset.initialized === 'true') {
                        return;
                    }

                    const links = mobileMenu.querySelectorAll('[data-menu-link]');
                    let isOpen = false;

                    function syncState() {
                        if (!isOpen && mobileMenu.contains(document.activeElement)) {
                            openButton.focus();
                        }

                        mobileMenu.classList.toggle('hidden', !isOpen);
                        mobileMenu.inert = !isOpen;
                        openButton.setAttribute('aria-expanded', String(isOpen));
                        document.body.classList.toggle('overflow-hidden', isOpen);
                    }

                    function openMenu() {
                        isOpen = true;
                        syncState();
                        closeButton.focus();
                    }

                    function closeMenu() {
                        isOpen = false;
                        syncState();
                    }

                    openButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        openMenu();
                    });

                    closeButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        closeMenu();
                    });

                    links.forEach(function (link) {
                        link.addEventListener('click', closeMenu);
                    });

                    mobileMenu.addEventListener('click', function (event) {
                        if (event.target === mobileMenu) {
                            closeMenu();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && isOpen) {
                            closeMenu();
                        }
                    });

                    window.addEventListener('resize', function () {
                        if (window.innerWidth >= 640 && isOpen) {
                            closeMenu();
                        }
                    });

                    mobileMenu.dataset.initialized = 'true';
                    syncState();
                } catch (error) {
                    console.error('Failed to initialize mobile menu:', error);
                }
            });
        </script>

    </body>
</html>
