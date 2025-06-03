<!-- Post List Widget Template -->
<?php
// DEBUGGING INFORMATION - Remove in production
\Log::debug('Post List Widget Template Variables', [
    'widget_type' => gettype($widget),
    'widget_keys' => is_array($widget) ? array_keys($widget) : 'not an array',
    'has_content' => is_array($widget) && isset($widget['content']) ? 'yes' : 'no',
    'content_keys' => is_array($widget) && isset($widget['content']) ? array_keys($widget['content']) : 'no content',
]);
?>

@php
    // Set up default posts if none are provided
    $defaultPosts = [
        ['id' => 1, 'title' => 'Sample Post 1', 'excerpt' => 'This is a sample post for testing purposes', 'url' => '#', 'created_at' => now(), 'author' => ['name' => 'Admin']],
        ['id' => 2, 'title' => 'Sample Post 2', 'excerpt' => 'Another sample post with some interesting content', 'url' => '#', 'created_at' => now(), 'author' => ['name' => 'Editor']],
        ['id' => 3, 'title' => 'Sample Post 3', 'excerpt' => 'Yet another sample post to demonstrate the widget', 'url' => '#', 'created_at' => now(), 'author' => ['name' => 'Guest']],
    ];
    
    // Check for content from the widget service - from logs we know it's at posts key
    $postsData = isset($widget['content']['posts']) ? $widget['content']['posts'] : collect($defaultPosts);
    
    // Get widget settings
    $title = $widget['fields']['title'] ?? 'Latest Posts';
    $limit = (int)($widget['fields']['limit'] ?? 3);
    
    // Handle both collection and array formats
    $postsData = is_array($postsData) ? collect($postsData) : $postsData;
    
    // Apply limit if needed
    if ($postsData->count() > $limit) {
        $postsData = $postsData->take($limit);
    }
@endphp

<div class="post-list-widget">
    <h3 class="widget-title">{{ $title }}</h3>

@if(count($postsData) > 0)
    @foreach($postsData as $post)
        <div class="post-preview">
            @php
                // Handle both object and array formats
                $postTitle = $post->title ?? $post['title'] ?? 'Untitled Post';
                $postSubtitle = $post->subtitle ?? $post['subtitle'] ?? null;
                $postUrl = $post->url ?? $post['url'] ?? '#';
                $postExcerpt = $post->excerpt ?? $post['excerpt'] ?? '';
                $postDate = $post->created_at ?? ($post['created_at'] ?? now());
                $postDate = is_string($postDate) ? new \DateTime($postDate) : $postDate;
                $authorName = isset($post->author) ? ($post->author->name ?? 'Anonymous') : ($post['author']['name'] ?? 'Anonymous');
            @endphp
            
            <a href="{{ $postUrl }}">
                <h2 class="post-title">{{ $postTitle }}</h2>
                @if($postSubtitle)
                    <h3 class="post-subtitle">{{ $postSubtitle }}</h3>
                @endif
            </a>
            <p>{{ $postExcerpt }}</p>
            <p class="post-meta">
                Posted by
                <a href="#!">{{ $authorName }}</a>
                @if($postDate instanceof \DateTime || $postDate instanceof \Carbon\Carbon)
                    on {{ $postDate->format('F d, Y') }}
                @endif
            </p>
        </div>
        <!-- Divider-->
        @if(!$loop->last)
            <hr class="my-4" />
        @endif
    @endforeach

    <!-- Paging-->
    @if(method_exists($postsData, 'links'))
        <div class="d-flex justify-content-end mb-4">
            {{ $postsData->links() }}
        </div>
    @endif
@else
    <div class="alert alert-info">
        No posts available at this time.
    </div>
@endif

</div><!-- /.post-list-widget -->
