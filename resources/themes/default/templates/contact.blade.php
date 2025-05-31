@extends('theme::layouts.theme')

@templateSection('content')
    @hasTemplateSection('hero')
        @templateSection('hero')
    @endHasTemplateSection

    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5">
            <div class="col-md-8">
                @hasTemplateSection('content')
                    @templateSection('content')
                @endHasTemplateSection
                
                @hasTemplateSection('form')
                    @templateSection('form')
                @endHasTemplateSection
            </div>
            
            @hasTemplateSection('sidebar')
                <div class="col-md-4">
                    @templateSection('sidebar')
                </div>
            @endHasTemplateSection
        </div>
    </div>
    
    @hasTemplateSection('map')
        @templateSection('map')
    @endHasTemplateSection
@endtemplateSection
