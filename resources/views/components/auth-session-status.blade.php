@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-zinc-900 dark:text-zinc-100']) }}>
        {{ $status }}
    </div>
@endif
