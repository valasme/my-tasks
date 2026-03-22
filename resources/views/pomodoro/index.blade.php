<x-layouts::app :title="__('Pomodoro Timer')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Pomodoro Timer') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Focus sessions to boost productivity. Today: :count completed.', ['count' => $todayCount]) }}</flux:subheading>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Active Session or Start New --}}
        @if ($activeSession)
            <div
                class="rounded-xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-900"
                x-data="{
                    totalSeconds: {{ $activeSession->duration_minutes }} * 60,
                    remaining: 0,
                    display: '--:--',
                    progress: 100,
                    finished: false,
                    interval: null,
                    init() {
                        const started = new Date('{{ $activeSession->started_at->toISOString() }}');
                        const endTime = new Date(started.getTime() + this.totalSeconds * 1000);
                        this.tick(endTime);
                        this.interval = setInterval(() => this.tick(endTime), 250);
                    },
                    tick(endTime) {
                        const now = new Date();
                        this.remaining = Math.max(0, Math.floor((endTime - now) / 1000));
                        const hrs = Math.floor(this.remaining / 3600);
                        const mins = Math.floor((this.remaining % 3600) / 60);
                        const secs = this.remaining % 60;
                        this.display = hrs > 0
                            ? hrs + ':' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0')
                            : String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                        this.progress = Math.round((this.remaining / this.totalSeconds) * 100);
                        if (this.remaining <= 0 && !this.finished) {
                            this.finished = true;
                            clearInterval(this.interval);
                            this.display = '00:00';
                            this.progress = 0;
                            try { new Audio('data:audio/wav;base64,UklGRl9vT19teleWF2ZWZtdCAQAAAAABAAEAESsAABErAAABAAgAZGF0YQ==').play().catch(() => {}); } catch(e) {}
                        }
                    }
                }"
            >
                <div class="flex flex-col items-center gap-8 sm:flex-row sm:items-center sm:justify-center">
                    {{-- Left: Circular Progress Ring --}}
                    <div class="flex flex-col items-center">
                        <div class="relative h-32 w-32" aria-hidden="true">
                            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="8" class="text-zinc-200 dark:text-zinc-700" />
                                <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="8" class="text-zinc-800 dark:text-zinc-200 transition-all duration-500" stroke-linecap="round"
                                    :stroke-dasharray="339.292"
                                    :stroke-dashoffset="339.292 - (339.292 * progress / 100)"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-mono text-2xl font-bold text-zinc-900 dark:text-zinc-50" x-text="display"></span>
                                <span class="mt-1 text-xs text-zinc-500" x-show="!finished">{{ __('remaining') }}</span>
                                <span class="mt-1 text-xs font-semibold text-zinc-700 dark:text-zinc-300" x-show="finished" x-cloak>{{ __('Done!') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Vertical Divider --}}
                    <div class="hidden h-36 w-px bg-zinc-200 dark:bg-zinc-700 sm:block"></div>
                    <div class="w-full border-t border-zinc-200 dark:border-zinc-700 sm:hidden"></div>

                    {{-- Right: Info & Controls --}}
                    <div class="flex flex-col items-center gap-4">
                        <flux:heading size="lg">{{ $activeSession->type === 'work' ? __('Focus Session') : __('Break') }}</flux:heading>
                        <flux:subheading>{{ __(':minutes minute session', ['minutes' => $activeSession->duration_minutes]) }}</flux:subheading>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('pomodoro.stop', $activeSession) }}">
                                @csrf
                                <flux:button class="cursor-pointer" type="submit" variant="primary" icon="check">{{ __('Complete') }}</flux:button>
                            </form>
                            <form method="POST" action="{{ route('pomodoro.cancel', $activeSession) }}">
                                @csrf
                                <flux:button class="cursor-pointer" type="submit" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-900"
                x-data="{
                    selectedMinutes: 25,
                    display: '25:00',
                    updateDisplay() {
                        const m = parseInt(this.selectedMinutes);
                        if (m >= 60) {
                            const h = Math.floor(m / 60);
                            const r = m % 60;
                            this.display = h + ':' + String(r).padStart(2, '0') + ':00';
                        } else {
                            this.display = String(m).padStart(2, '0') + ':00';
                        }
                    }
                }"
            >
                <div class="flex flex-col items-center gap-8 sm:flex-row sm:items-center sm:justify-center">
                    {{-- Left: Preview Timer --}}
                    <div class="flex flex-col items-center">
                        <div class="relative h-32 w-32" aria-hidden="true">
                            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="8" class="text-zinc-200 dark:text-zinc-700" />
                                <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="8" class="text-zinc-800 dark:text-zinc-200" stroke-linecap="round" stroke-dasharray="339.292" stroke-dashoffset="0" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-mono text-2xl font-bold text-zinc-700 dark:text-zinc-300" x-text="display"></span>
                                <span class="mt-1 text-xs text-zinc-400">{{ __('ready') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Vertical Divider --}}
                    <div class="hidden h-36 w-px bg-zinc-200 dark:bg-zinc-700 sm:block"></div>
                    <div class="w-full border-t border-zinc-200 dark:border-zinc-700 sm:hidden"></div>

                    {{-- Right: Controls --}}
                    <div class="flex flex-col items-center gap-4">
                        <flux:heading size="lg">{{ __('Start a Session') }}</flux:heading>
                        <form method="POST" action="{{ route('pomodoro.start') }}" class="flex flex-col items-center gap-4">
                            @csrf
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <div class="w-40">
                                    <flux:select name="type" :label="__('Type')">
                                        <flux:select.option value="work" selected>{{ __('Work') }}</flux:select.option>
                                        <flux:select.option value="break">{{ __('Break') }}</flux:select.option>
                                    </flux:select>
                                </div>
                                <div class="w-40">
                                    <flux:select name="duration_minutes" :label="__('Duration')" x-model="selectedMinutes" x-on:change="updateDisplay()">
                                        <flux:select.option value="5">5 min</flux:select.option>
                                        <flux:select.option value="10">10 min</flux:select.option>
                                        <flux:select.option value="15">15 min</flux:select.option>
                                        <flux:select.option value="20">20 min</flux:select.option>
                                        <flux:select.option value="25" selected>25 min</flux:select.option>
                                        <flux:select.option value="30">30 min</flux:select.option>
                                        <flux:select.option value="45">45 min</flux:select.option>
                                        <flux:select.option value="60">1 hour</flux:select.option>
                                        <flux:select.option value="90">1.5 hours</flux:select.option>
                                        <flux:select.option value="120">2 hours</flux:select.option>
                                        <flux:select.option value="180">3 hours</flux:select.option>
                                        <flux:select.option value="240">4 hours</flux:select.option>
                                        <flux:select.option value="360">6 hours</flux:select.option>
                                        <flux:select.option value="480">8 hours</flux:select.option>
                                        <flux:select.option value="720">12 hours</flux:select.option>
                                        <flux:select.option value="1440">24 hours</flux:select.option>
                                        <flux:select.option value="2880">48 hours</flux:select.option>
                                    </flux:select>
                                </div>
                            </div>
                            <flux:button class="cursor-pointer" type="submit" variant="primary" icon="play">{{ __('Start') }}</flux:button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <flux:separator />

        {{-- Session History --}}
        <div>
            <flux:heading size="lg">{{ __('Session History') }}</flux:heading>

            @if ($sessions->isEmpty())
                <div class="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600">
                    <flux:icon name="clock" class="mb-3 size-10 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:subheading>{{ __('No completed sessions yet.') }}</flux:subheading>
                </div>
            @else
                <flux:table class="mt-4">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Duration') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell">{{ __('Started') }}</flux:table.column>
                        <flux:table.column class="hidden md:table-cell">{{ __('Ended') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($sessions as $session)
                            <flux:table.row>
                                <flux:table.cell>
                                    <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ ucfirst($session->type) }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell>{{ $session->duration_minutes }} min</flux:table.cell>
                                <flux:table.cell class="hidden sm:table-cell">{{ $session->started_at->format('M d, g:i A') }}</flux:table.cell>
                                <flux:table.cell class="hidden md:table-cell">{{ $session->ended_at?->format('M d, g:i A') ?? '—' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
                <div class="mt-6">{{ $sessions->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
