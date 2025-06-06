<li class="dd-item" data-id="{{ $item->id }}">
    <div class="dd-handle">
        <div class="menu-item-title">
            @if($item->children && $item->children->count() > 0)
                <span class="menu-item-has-children">
                    <i class="ri-folder-line"></i>
                </span>
            @else
                <span class="menu-item-no-children">
                    <i class="ri-menu-line"></i>
                </span>
            @endif
            
            <span class="menu-item-label">{{ $item->label }}</span>
            
            <div class="menu-item-badges">
                @if($item->link_type == 'page' && $item->page_id)
                    <span class="badge bg-soft-info text-info menu-item-badge">
                        Page: {{ $item->page->title ?? 'Unknown' }}
                    </span>
                @elseif($item->link_type == 'url' && $item->url)
                    <span class="badge bg-soft-primary text-primary menu-item-badge">
                        URL: {{ \Illuminate\Support\Str::limit($item->url, 30) }}
                    </span>
                @endif
                @if($item->link_type == 'section' && $item->section_id)
                    <span class="badge bg-soft-warning text-warning menu-item-badge">
                        Section: #{{ $item->section_id }}
                    </span>
                @endif
                @if(!$item->is_active)
                    <span class="badge bg-soft-danger text-danger menu-item-badge">Inactive</span>
                @endif
            </div>
        </div>
        
        <div class="menu-item-actions">
            <a href="{{ route('admin.menus.items.edit', ['menu' => $menu->id, 'item' => $item->id]) }}" 
               class="btn btn-sm btn-soft-primary" 
               title="Edit Menu Item">
                <i class="ri-pencil-line"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-soft-danger delete-menu-item" 
                    data-id="{{ $item->id }}" 
                    data-title="{{ $item->label }}"
                    title="Delete Menu Item">
                <i class="ri-delete-bin-line"></i>
            </button>
            <form id="delete-menu-item-{{ $item->id }}" 
                  action="{{ route('admin.menus.items.destroy', ['menu' => $menu->id, 'item' => $item->id]) }}" 
                  method="POST" 
                  style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
    
    @if($item->children && $item->children->count() > 0)
        <ul class="dd-list">
            @foreach($item->children as $child)
                @include('admin.menus.partials.menu-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>