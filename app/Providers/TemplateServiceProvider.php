<?php

namespace App\Providers;

use App\Services\TemplateRenderer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register custom blade directives
        $this->registerBladeDirectives();
    }
    
    /**
     * Register custom blade directives for templates
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        // @templateSection directive for rendering a template section
        Blade::directive('templateSection', function ($expression) {
            return "<?php echo app('" . TemplateRenderer::class . "')->renderSection({$expression}, get_defined_vars()); ?>";
        });
        
        // @hasTemplateSection directive to check if a section exists
        Blade::directive('hasTemplateSection', function ($expression) {
            return "<?php if(app('" . TemplateRenderer::class . "')->sectionExists({$expression}, get_defined_vars())): ?>";
        });
        
        // @endHasTemplateSection directive
        Blade::directive('endHasTemplateSection', function () {
            return "<?php endif; ?>";
        });
        
        // @noTemplateSection directive for when a section doesn't exist
        Blade::directive('noTemplateSection', function ($expression) {
            return "<?php if(!app('" . TemplateRenderer::class . "')->sectionExists({$expression}, get_defined_vars())): ?>";
        });
        
        // @endNoTemplateSection directive
        Blade::directive('endNoTemplateSection', function () {
            return "<?php endif; ?>";
        });
        
        // @endTemplateSection directive
        Blade::directive('endTemplateSection', function () {
            return "<?php ?>"; // Just close the PHP tag
        });
    }
}
