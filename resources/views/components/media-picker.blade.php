{{-- Media Picker Component --}}
<div class="media-picker-field" data-name="{{ $name }}" data-multiple="{{ $multiple ? 'true' : 'false' }}">
    <div class="form-group mb-3">
        <label for="{{ $name }}">{{ $label }}</label>
        
        {{-- Selected Media Preview Area --}}
        <div class="media-picker-preview mt-2">
            @if(!$multiple)
                @php
                    $mediaUrl = '';
                    $mediaName = '';
                    if (count($selected)) {
                        $mediaItem = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($selected[0]);
                        if ($mediaItem) {
                            $mediaUrl = $mediaItem->getUrl();
                            $mediaName = $mediaItem->name;
                        }
                    }
                @endphp
                <div class="selected-media-container" style="{{ count($selected) ? '' : 'display: none;' }}">
                    <div class="selected-media-item position-relative">
                        <button type="button" class="btn-close remove-selected-media position-absolute top-0 end-0 bg-light rounded-circle m-1"></button>
                        <img src="{{ $mediaUrl }}" class="img-fluid selected-media-image">
                        <div class="selected-media-name text-center small mt-1">{{ $mediaName }}</div>
                    </div>
                </div>
            @else
                <div class="selected-media-multiple row g-2">
                    @foreach($selected as $mediaId)
                        @php
                            $mediaItem = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                            $mediaUrl = $mediaItem ? $mediaItem->getUrl() : '';
                        @endphp
                        <div class="col-auto selected-media-item" data-media-id="{{ $mediaId }}">
                            <div class="position-relative border rounded p-1">
                                <button type="button" class="btn-close remove-selected-media position-absolute top-0 end-0 bg-light rounded-circle m-1"></button>
                                <img src="{{ $mediaUrl }}" class="img-fluid" style="height: 60px; width: auto;">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        {{-- Hidden Input to Store Selected Media IDs --}}
        @if($multiple)
            <input type="hidden" name="{{ $name }}" value="{{ implode(',', $selected) }}" class="media-picker-input">
        @else
            <input type="hidden" name="{{ $name }}" value="{{ count($selected) ? $selected[0] : '' }}" class="media-picker-input">
        @endif
        
        {{-- Select Media Button --}}
        <button type="button" class="btn btn-primary open-media-picker mt-2">
            <i class="ri-image-add-line me-1"></i> {{ count($selected) ? 'Change Media' : 'Select Media' }}
        </button>
    </div>
</div>

{{-- Media Picker Modal is now included once in the admin layout --}}
