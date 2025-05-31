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
                
                @hasTemplateSection('team')
                    @templateSection('team')
                @endHasTemplateSection
                
                @hasTemplateSection('services')
                    @templateSection('services')
                @endHasTemplateSection
            </div>
            
            @hasTemplateSection('sidebar')
                <div class="col-md-4">
                    @templateSection('sidebar')
                </div>
            @endHasTemplateSection
        </div>
    </div>
@endtemplateSection

