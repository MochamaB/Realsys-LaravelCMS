@extends('theme::layouts.theme')

@templateSection('content')
    @hasTemplateSection('hero')
        @templateSection('hero')
    @endHasTemplateSection

    <article class="mb-4">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-md-8">
                    @hasTemplateSection('content')
                        @templateSection('content')
                    @endHasTemplateSection
                    
                    @hasTemplateSection('author')
                        @templateSection('author')
                    @endHasTemplateSection
                    
                    @hasTemplateSection('comments')
                        @templateSection('comments')
                    @endHasTemplateSection
                </div>
                
                @hasTemplateSection('sidebar')
                    <div class="col-md-4">
                        @templateSection('sidebar')
                    </div>
                @endHasTemplateSection
            </div>
        </div>
    </article>
@endtemplateSection
