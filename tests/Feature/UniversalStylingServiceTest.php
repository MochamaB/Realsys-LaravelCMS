<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\UniversalStylingService;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\TemplateSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UniversalStylingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $stylingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stylingService = new UniversalStylingService();
    }

    /** @test */
    public function it_fixes_css_units_in_padding()
    {
        // Create a mock page section with problematic padding
        $templateSection = TemplateSection::factory()->create([
            'section_type' => 'full-width'
        ]);
        
        $pageSection = PageSection::factory()->create([
            'template_section_id' => $templateSection->id,
            'padding' => '40 0 0 40', // Missing px units
            'margin' => '0 0 0 0'
        ]);

        $styles = $this->stylingService->buildSectionStyles($pageSection);

        // Should convert to proper CSS with px units
        $this->assertStringContains('padding: 40px 0 0 40px', $styles);
        $this->assertStringContains('margin: 0 0 0 0', $styles);
        
        // Should not contain malformed CSS
        $this->assertStringNotContains('padding: 40 0 0 40', $styles);
    }

    /** @test */
    public function it_prevents_double_container_fluid_for_theme_sections()
    {
        // Create section with full-width type (theme handles container)
        $templateSection = TemplateSection::factory()->create([
            'section_type' => 'full-width'
        ]);
        
        $pageSection = PageSection::factory()->create([
            'template_section_id' => $templateSection->id
        ]);

        $classes = $this->stylingService->buildSectionClasses($pageSection);

        // Should NOT contain container-fluid since theme handles it
        $this->assertStringNotContains('container-fluid', $classes);
        $this->assertStringContains('cms-section', $classes);
        $this->assertStringContains('section-full-width', $classes);
    }

    /** @test */
    public function it_adds_container_fluid_for_default_sections()
    {
        // Create section with default type (theme doesn't handle container)
        $templateSection = TemplateSection::factory()->create([
            'section_type' => 'default'
        ]);
        
        $pageSection = PageSection::factory()->create([
            'template_section_id' => $templateSection->id
        ]);

        $classes = $this->stylingService->buildSectionClasses($pageSection);

        // Should contain container-fluid since theme doesn't handle it
        $this->assertStringContains('container-fluid', $classes);
        $this->assertStringContains('cms-section', $classes);
        $this->assertStringContains('section-default', $classes);
    }

    /** @test */
    public function it_handles_widget_styles_with_proper_units()
    {
        $widget = PageSectionWidget::factory()->create([
            'padding' => '20 15 10 5', // No units
            'margin' => '10 auto',     // Mixed units
            'min_height' => '200'      // No units
        ]);

        $styles = $this->stylingService->buildWidgetStyles($widget);

        $this->assertStringContains('padding: 20px 15px 10px 5px', $styles);
        $this->assertStringContains('margin: 10px auto', $styles);
        $this->assertStringContains('min-height: 200px', $styles);
    }

    /** @test */
    public function it_validates_color_values()
    {
        $templateSection = TemplateSection::factory()->create();
        
        // Valid color
        $validSection = PageSection::factory()->create([
            'template_section_id' => $templateSection->id,
            'background_color' => '#ff0000'
        ]);

        $validStyles = $this->stylingService->buildSectionStyles($validSection);
        $this->assertStringContains('background-color: #ff0000', $validStyles);

        // Invalid color should be ignored
        $invalidSection = PageSection::factory()->create([
            'template_section_id' => $templateSection->id,
            'background_color' => 'invalid-color-value'
        ]);

        $invalidStyles = $this->stylingService->buildSectionStyles($invalidSection);
        $this->assertStringNotContains('background-color:', $invalidStyles);
    }

    /** @test */
    public function it_handles_array_spacing_correctly()
    {
        $widget = PageSectionWidget::factory()->create([
            'padding' => ['top' => '10', 'right' => '20', 'bottom' => '15', 'left' => '25']
        ]);

        $styles = $this->stylingService->buildWidgetStyles($widget);
        
        // Should convert array to space-separated values with units
        $this->assertStringContains('padding: 10px 20px 15px 25px', $styles);
    }
}