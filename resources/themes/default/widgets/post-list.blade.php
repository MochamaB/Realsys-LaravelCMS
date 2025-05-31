<!-- Post preview-->
@foreach($posts as $post)
    <div class="post-preview">
        <a href="{{ $post->url }}">
            <h2 class="post-title">{{ $post->title }}</h2>
            @if($post->subtitle)
                <h3 class="post-subtitle">{{ $post->subtitle }}</h3>
            @endif
        </a>
        <p class="post-meta">
            Posted by
            <a href="#!">{{ $post->author->name }}</a>
            on {{ $post->created_at->format('F d, Y') }}
        </p>
    </div>
    <!-- Divider-->
    @if(!$loop->last)
        <hr class="my-4" />
    @endif
@endforeach

<!-- Paging-->
<div class="d-flex justify-content-end mb-4">
    {{ $posts->links() }}
</div>
