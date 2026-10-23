@props(['type' => 'success'])

@php
    $classes = match ($type) {
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-900 dark:bg-yellow-900/40 dark:text-yellow-200',
        'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-900/40 dark:text-red-200',
        default => 'border-green-200 bg-green-50 text-green-800 dark:border-green-900 dark:bg-green-900/40 dark:text-green-200',
    };
@endphp

<div role="{{ $type === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->merge(['class' => "rounded-md border px-4 py-3 text-sm {$classes}"]) }}>
    {{ $slot }}
</div>
