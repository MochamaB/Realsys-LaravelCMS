<div class="section section-footer {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <footer class="border-top">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    @if($widgets && $widgets->count() > 0)
                        <!-- Render footer widgets -->
                        @foreach($widgets as $widget)
                            <div class="widget widget-{{ $widget->widgetType->slug }}">
                                @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                            </div>
                        @endforeach
                    @else
                        <!-- Default footer content if no widgets -->
                        <ul class="list-inline text-center">
                            <li class="list-inline-item">
                                <a href="#" target="_blank">
                                    <span class="fa-stack fa-lg">
                                        <i class="fas fa-circle fa-stack-2x"></i>
                                        <i class="fab fa-twitter fa-stack-1x fa-inverse"></i>
                                    </span>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" target="_blank">
                                    <span class="fa-stack fa-lg">
                                        <i class="fas fa-circle fa-stack-2x"></i>
                                        <i class="fab fa-facebook-f fa-stack-1x fa-inverse"></i>
                                    </span>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" target="_blank">
                                    <span class="fa-stack fa-lg">
                                        <i class="fas fa-circle fa-stack-2x"></i>
                                        <i class="fab fa-github fa-stack-1x fa-inverse"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>
                        <div class="small text-center text-muted fst-italic">Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </footer>
</div>
