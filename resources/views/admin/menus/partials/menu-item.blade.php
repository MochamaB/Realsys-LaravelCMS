<li class="menu-item" data-id="{{ $item->id }}">
    <div class="menu-item-handle">
        <div class="menu-item-bar">
            <div class="menu-item-info">
                @if($item->children && $item->children->count() > 0)
                    <span class="collapse-arrow">
                        <i class="ri-arrow-down-s-line"></i>
                    </span>
                @else
                    <span class="collapse-placeholder">
                        <i class="ri-checkbox-blank-circle-line"></i>
                    </span>
                @endif
                <span class="drag-handle">
                    <i class="ri-drag-move-line"></i>
                </span>
                <span class="item-title">{{ $item->label }}</span>
                
                @if($item->link_type == 'page' && $item->page_id)
                    <span class="badge bg-info menu-item-badge">
                        Page: {{ $item->page->title ?? 'Unknown' }}
                    </span>
                @elseif($item->link_type == 'url' && $item->url)
                    <span class="badge bg-primary menu-item-badge">
                        URL: {{ \Illuminate\Support\Str::limit($item->url, 30) }}
                    </span>
                @elseif($item->link_type == 'section' && $item->section_id)
                    <span class="badge bg-warning menu-item-badge">
                        Section: #{{ $item->section_id }}
                    </span>
                @endif
                
                @if(!$item->is_active)
                    <span class="badge bg-danger menu-item-badge">Inactive</span>
                @endif
            </div>
            <div class="menu-item-actions">
                <a href="{{ route('admin.menus.items.edit', ['menu' => $menu->id, 'item' => $item->id]) }}" class="menu-item-action" title="Edit">
                    <i class="ri-edit-line"></i>
                </a>
                <button type="button" class="menu-item-action delete-menu-item" data-id="{{ $item->id }}" data-title="{{ $item->label }}" title="Delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
                <form id="delete-menu-item-{{ $item->id }}" action="{{ route('admin.menus.items.destroy', ['menu' => $menu->id, 'item' => $item->id]) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
    @if($item->children && $item->children->count() > 0)
        <div class="menu-item-children nested-sortable">
            @foreach($item->children as $child)
                @include('admin.menus.partials.menu-item', ['item' => $child])
            @endforeach
        </div>
    @endif
</li>
