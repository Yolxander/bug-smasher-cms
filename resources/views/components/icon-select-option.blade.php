@props(['icon', 'label'])

<div class="flex items-center gap-2">
    <x-dynamic-component :component="$icon" class="w-5 h-5" />
    <span>{{ $label }}</span>
</div>
