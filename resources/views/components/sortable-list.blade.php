{{--
    Sortable List Component
    
    Props:
    - id: Unique identifier for the sortable list
    - items: Collection of items to be displayed in the list
    - itemKey: The property to use as the item's unique identifier
    - saveUrl: URL to send reordering request (optional)
    - emptyMessage: Message to display when the list is empty
    - nestedKey: Key for nested items (for nested sortable lists)
    - group: Sortable group name (for linking multiple lists)
--}}

@props([
    'id',
    'items',
    'itemKey' => 'id',
    'saveUrl' => null,
    'emptyMessage' => 'No items available',
    'nestedKey' => null,
    'group' => 'sortable-group'
])

<div class="sortable-container">
    @if($items->isEmpty())
        <div class="sortable-empty-state">
            {{ $emptyMessage }}
        </div>
    @else
        <ul class="dd-list sortable-list" id="{{ $id }}" data-save-url="{{ $saveUrl }}">
            @foreach($items as $item)
                <li class="dd-item sortable-item" data-id="{{ $item->{$itemKey} }}">
                    <div class="dd-handle">
                        {{ $item($item) }}
                    </div>
                    
                    @if($nestedKey && isset($item->{$nestedKey}) && $item->{$nestedKey}->isNotEmpty())
                        <ul class="dd-list">
                            @foreach($item->{$nestedKey} as $nestedItem)
                                <li class="dd-item sortable-item" data-id="{{ $nestedItem->{$itemKey} }}">
                                    <div class="dd-handle">
                                        {{ $nested($nestedItem) }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
        
        @if($saveUrl)
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary btn-sm save-sortable-order" data-target="{{ $id }}">
                    <i class="ri-save-line me-1"></i> Save Order
                </button>
            </div>
        @endif
    @endif
</div>
