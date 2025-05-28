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
        // @section directive for rendering a template section
        Blade::directive('section', function ($expression) {
            return "<?php echo app('" . TemplateRenderer::class . "')->renderSection({$expression}, get_defined_vars()); ?>";
        });
        
        // @hassection directive to check if a section exists
        Blade::directive('hassection', function ($expression) {
            return "<?php if(app('" . TemplateRenderer::class . "')->sectionExists({$expression}, get_defined_vars())): ?>";
        });
        
        // @endhassection directive
        Blade::directive('endhassection', function () {
            return "<?php endif; ?>";
        });
        
        // @nosection directive for when a section doesn't exist
        Blade::directive('nosection', function ($expression) {
            return "<?php if(!app('" . TemplateRenderer::class . "')->sectionExists({$expression}, get_defined_vars())): ?>";
        });
        
        // @endnosection directive
        Blade::directive('endnosection', function () {
            return "<?php endif; ?>";
        });
    }
}
