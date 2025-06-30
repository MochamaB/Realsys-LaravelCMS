@foreach($folders as $subfolder)
    <option value="{{ $subfolder->id }}">{{ str_repeat('— ', $level) }} {{ $subfolder->name }}</option>
    @if($subfolder->children->count() > 0)
        @include('admin.media.partials._folder_options', ['folders' => $subfolder->children, 'level' => $level + 1])
    @endif
@endforeach
