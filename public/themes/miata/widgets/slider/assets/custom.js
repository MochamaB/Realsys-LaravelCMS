// Slider initialization script
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all slider widgets on the page
    const sliders = document.querySelectorAll('.widget-slider .nivoSlider');
    
    sliders.forEach(slider => {
        const sliderId = slider.id;
        const config = window.sliderConfigs && window.sliderConfigs[sliderId] ? window.sliderConfigs[sliderId] : {};
        
        // Default configuration
        const defaultConfig = {
            effect: 'random',
            slices: 15,
            boxCols: 8,
            boxRows: 4,
            animSpeed: 500,
            pauseTime: 3000,
            startSlide: 0,
            directionNav: true,
            controlNav: true,
            pauseOnHover: true,
            manualAdvance: false,
            prevText: 'Prev',
            nextText: 'Next'
        };
        
        // Merge configurations
        const finalConfig = { ...defaultConfig, ...config };
        
        // Initialize slider if jQuery and nivoSlider are available
        if (typeof jQuery !== 'undefined' && jQuery.fn.nivoSlider) {
            jQuery('#' + sliderId).nivoSlider(finalConfig);
        }
    });
});
