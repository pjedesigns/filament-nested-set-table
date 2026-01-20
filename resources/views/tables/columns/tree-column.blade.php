@php
    $record = $getRecord();
    $state = $getState();
    $indentPadding = $getIndentPadding();
    $hasChildren = $hasChildren();
    $showDragHandle = $shouldShowDragHandle() && $isDraggable();
    $showExpandToggle = $shouldShowExpandToggle() && $hasChildren;
    $nodeId = $record->getKey();
    $parentId = $record->parent_id;
    $depth = $record->depth ?? 0;

    $isExpanded = $this->isNodeExpanded($nodeId);
@endphp

<div
    class="fi-ta-tree-column flex items-center gap-1"
    style="padding-left: {{ $indentPadding }}px"
    data-node-id="{{ $nodeId }}"
    data-parent-id="{{ $parentId }}"
    data-depth="{{ $depth }}"
>
    {{-- Drag Handle --}}
    @if ($showDragHandle)
        <button
            type="button"
            class="tree-drag-handle flex h-6 w-6 shrink-0 cursor-grab items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
            title="{{ __('Drag to reorder') }}"
        >
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4 -mr-2"
            />
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4"
            />
        </button>
    @endif

    {{-- Expand/Collapse Toggle --}}
    @if ($showExpandToggle)
        <button
            type="button"
            class="tree-expand-toggle flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
            wire:click="toggleNode({{ $nodeId }})"
            title="{{ $isExpanded ? __('Collapse') : __('Expand') }}"
        >
            <x-filament::icon
                :icon="$isExpanded ? 'heroicon-m-chevron-down' : 'heroicon-m-chevron-right'"
                class="h-4 w-4 transition-transform duration-150"
            />
        </button>
    @elseif ($shouldShowExpandToggle())
        {{-- Placeholder to maintain alignment when item has no children but siblings might --}}
        <span class="w-6 shrink-0"></span>
    @endif

    {{-- Content (delegate to parent TextColumn rendering) --}}
    <div class="tree-content min-w-0 flex-1">
        @php
            $isClickable = $getUrl() || $getAction();
            $shouldOpenUrlInNewTab = $shouldOpenUrlInNewTab();

            $formattedState = $formatState($state);

            $icon = $getIcon();
            $iconPosition = $getIconPosition();
            $iconSize = $getIconSize();

            $color = $getColor();
            $copyableState = $getCopyableState() ?? $state;
            $copyMessage = $getCopyMessage();
            $copyMessageDuration = $getCopyMessageDuration();
            $isCopyable = $isCopyable();
            $tooltip = $getTooltip();

            $isListWithLineBreaks = $isListWithLineBreaks();
            $isProse = $isProse();

            $weight = $getWeight();
            $fontFamily = $getFontFamily();
        @endphp

        @if ($isBadge())
            <x-filament::badge
                :color="$color"
                :icon="$icon"
                :icon-position="$iconPosition"
                :size="$getSize() ?? 'sm'"
            >
                {{ $formattedState }}
            </x-filament::badge>
        @else
            <div
                @class([
                    'fi-ta-text-item inline-flex items-center gap-1.5',
                    match ($color) {
                        null => 'text-gray-950 dark:text-white',
                        'gray' => 'text-gray-500 dark:text-gray-400',
                        default => 'fi-color-custom text-custom-600 dark:text-custom-400',
                    },
                    is_string($color) ? "fi-color-{$color}" : null,
                    match ($getSize()) {
                        'xs', \Filament\Support\Enums\TextSize::ExtraSmall => 'text-xs',
                        'sm', \Filament\Support\Enums\TextSize::Small, null => 'text-sm',
                        'base', 'md', \Filament\Support\Enums\TextSize::Medium => 'text-base',
                        'lg', \Filament\Support\Enums\TextSize::Large => 'text-lg',
                        default => $getSize(),
                    },
                    match ($weight) {
                        'thin', \Filament\Support\Enums\FontWeight::Thin => 'font-thin',
                        'extralight', \Filament\Support\Enums\FontWeight::ExtraLight => 'font-extralight',
                        'light', \Filament\Support\Enums\FontWeight::Light => 'font-light',
                        'medium', \Filament\Support\Enums\FontWeight::Medium => 'font-medium',
                        'semibold', \Filament\Support\Enums\FontWeight::SemiBold => 'font-semibold',
                        'bold', \Filament\Support\Enums\FontWeight::Bold => 'font-bold',
                        'extrabold', \Filament\Support\Enums\FontWeight::ExtraBold => 'font-extrabold',
                        'black', \Filament\Support\Enums\FontWeight::Black => 'font-black',
                        default => null,
                    },
                    match ($fontFamily) {
                        'sans', \Filament\Support\Enums\FontFamily::Sans => 'font-sans',
                        'serif', \Filament\Support\Enums\FontFamily::Serif => 'font-serif',
                        'mono', \Filament\Support\Enums\FontFamily::Mono => 'font-mono',
                        default => null,
                    },
                ])
                @if (filled($tooltip))
                    x-tooltip="{
                        content: @js($tooltip),
                        theme: $store.theme,
                    }"
                @endif
            >
                @if ($icon && $iconPosition === \Filament\Support\Enums\IconPosition::Before)
                    <x-filament::icon
                        :icon="$icon"
                        @class([
                            'fi-ta-text-item-icon',
                            match ($iconSize) {
                                'xs', \Filament\Support\Enums\IconSize::ExtraSmall => 'h-3 w-3',
                                'sm', \Filament\Support\Enums\IconSize::Small => 'h-4 w-4',
                                'md', \Filament\Support\Enums\IconSize::Medium => 'h-5 w-5',
                                'lg', \Filament\Support\Enums\IconSize::Large => 'h-6 w-6',
                                'xl', \Filament\Support\Enums\IconSize::ExtraLarge => 'h-7 w-7',
                                default => 'h-5 w-5',
                            },
                        ])
                    />
                @endif

                <span @class([
                    'fi-ta-text-item-label',
                    'cursor-pointer' => $isCopyable,
                    'truncate' => ! $canWrap(),
                ])
                    @if ($isCopyable)
                        x-on:click="
                            window.navigator.clipboard.writeText(@js($copyableState))
                            $tooltip(@js($copyMessage), {
                                theme: $store.theme,
                                timeout: @js($copyMessageDuration),
                            })
                        "
                    @endif
                >
                    {{ $formattedState }}
                </span>

                @if ($icon && $iconPosition === \Filament\Support\Enums\IconPosition::After)
                    <x-filament::icon
                        :icon="$icon"
                        @class([
                            'fi-ta-text-item-icon',
                            match ($iconSize) {
                                'xs', \Filament\Support\Enums\IconSize::ExtraSmall => 'h-3 w-3',
                                'sm', \Filament\Support\Enums\IconSize::Small => 'h-4 w-4',
                                'md', \Filament\Support\Enums\IconSize::Medium => 'h-5 w-5',
                                'lg', \Filament\Support\Enums\IconSize::Large => 'h-6 w-6',
                                'xl', \Filament\Support\Enums\IconSize::ExtraLarge => 'h-7 w-7',
                                default => 'h-5 w-5',
                            },
                        ])
                    />
                @endif
            </div>
        @endif
    </div>
</div>
