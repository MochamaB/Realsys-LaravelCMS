<ul class="list-unstyled pl-3 mb-0">
    @foreach($children as $childFolder)
        <li>
            <a href="#" class="folder-item d-flex align-items-center" data-folder-id="{{ $childFolder->id }}">
                <i class="ri-folder-line me-2"></i> {{ $childFolder->name }}
            </a>
            @if($childFolder->children->count())
                @include('admin.media.partials._folder_tree', ['children' => $childFolder->children])
            @endif
        </li>
    @endforeach
</ul>
