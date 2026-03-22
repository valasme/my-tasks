<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('tasks.index')" :current="request()->routeIs('tasks.*')" wire:navigate>
                        {{ __('Tasks') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('due-tasks.index')" :current="request()->routeIs('due-tasks.*')" wire:navigate>
                        {{ __('Due Tasks') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="rectangle-group" :href="route('workspaces.index')" :current="request()->routeIs('workspaces.*')" wire:navigate>
                        {{ __('Workspaces') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Focus')" class="grid">
                    <flux:sidebar.item icon="clock" :href="route('pomodoro.index')" :current="request()->routeIs('pomodoro.*')" wire:navigate>
                        {{ __('Pomodoro') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('time-blocks.index')" :current="request()->routeIs('time-blocks.*')" wire:navigate>
                        {{ __('Time Blocks') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Prioritization')" class="grid">
                    <flux:sidebar.item icon="inbox" :href="route('inbox.index')" :current="request()->routeIs('inbox.*')" wire:navigate>
                        {{ __('Inbox') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="light-bulb" :href="route('someday.index')" :current="request()->routeIs('someday.*')" wire:navigate>
                        {{ __('Someday / Maybe') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Insights')" class="grid">
                    <flux:sidebar.item icon="chart-bar" :href="route('analytics.index')" :current="request()->routeIs('analytics.*')" wire:navigate>
                        {{ __('Analytics') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="trophy" :href="route('gamification.index')" :current="request()->routeIs('gamification.*')" wire:navigate>
                        {{ __('Gamification') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="face-smile" :href="route('mood-logs.index')" :current="request()->routeIs('mood-logs.*')" wire:navigate>
                        {{ __('Mood Tracker') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('weekly-reviews.index')" :current="request()->routeIs('weekly-reviews.*')" wire:navigate>
                        {{ __('Weekly Reviews') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
