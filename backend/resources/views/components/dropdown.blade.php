@props([
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => 'Select an option',
    'disabled' => false,
    'searchable' => false,
])

@php
    $selectedLabel = collect($options)->first(
        fn ($label, $optionValue) => (string) $optionValue === (string) $value
    );
    $displayValue = $selectedLabel ?? $value;
    $dropdownId = $attributes->get('id', $name.'-dropdown');
@endphp

<div
    id="{{ $dropdownId }}"
    class="dispatcher-dropdown{{ $searchable ? ' is-searchable' : '' }}"
    data-dispatcher-dropdown
    data-searchable="{{ $searchable ? 'true' : 'false' }}"
    data-placeholder="{{ $placeholder }}"
>
    <input
        type="hidden"
        class="dispatcher-dropdown-value"
        name="{{ $name }}"
        value="{{ $value }}"
        data-dropdown-value
    >

    <div class="dispatcher-dropdown-trigger-wrap">
        @if ($searchable)
            <input
                type="search"
                class="dispatcher-dropdown-trigger"
                value="{{ $displayValue }}"
                placeholder="{{ $placeholder }}"
                role="combobox"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $dropdownId }}-options"
                autocomplete="off"
                data-dropdown-trigger
                @disabled($disabled)
            >
        @else
            <button
                type="button"
                class="dispatcher-dropdown-trigger"
                role="combobox"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $dropdownId }}-options"
                data-dropdown-trigger
                @disabled($disabled)
            >
                <span data-dropdown-label>{{ $selectedLabel ?? $placeholder }}</span>
            </button>
        @endif
        <span class="dispatcher-dropdown-arrow" aria-hidden="true"></span>
    </div>

    <div
        id="{{ $dropdownId }}-options"
        class="dispatcher-dropdown-menu"
        role="listbox"
        aria-label="{{ $placeholder }}"
        hidden
    >
        @foreach ($options as $optionValue => $optionLabel)
            <button
                type="button"
                class="dispatcher-dropdown-option"
                role="option"
                aria-selected="{{ (string) $optionValue === (string) $value ? 'true' : 'false' }}"
                data-dropdown-option
                data-value="{{ $optionValue }}"
                data-label="{{ $optionLabel }}"
            >{{ $optionLabel }}</button>
        @endforeach
        <div class="dispatcher-dropdown-no-results" data-dropdown-empty hidden>
            No matching options.
        </div>
    </div>
</div>
