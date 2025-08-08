// Counter animation script
document.addEventListener('DOMContentLoaded', function() {
    // Function to animate a single counter
    function animateCounter(counterElement) {
        const target = parseInt(counterElement.getAttribute('data-count'));
        const speed = parseInt(counterElement.getAttribute('data-speed')) || 2000;
        const increment = target / speed * 10;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                if (current > target) current = target;
                counterElement.innerText = Math.ceil(current);
                setTimeout(updateCounter, 10);
            } else {
                counterElement.innerText = target;
            }
        };
        
        updateCounter();
    }
    
    // Function to animate all counters
    function animateCounters() {
        // Find all counter elements in any counter widget
        const counters = document.querySelectorAll('.widget-counter .counter[data-count]');
        
        counters.forEach(counter => {
            // Only animate if it hasn't been animated yet
            if (!counter.hasAttribute('data-animated')) {
                animateCounter(counter);
                counter.setAttribute('data-animated', 'true');
            }
        });
    }
    
    // Check if element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Initialize counters when they come into view
    function checkCounters() {
        const counterSections = document.querySelectorAll('.counter_area, .widget-counter');
        
        counterSections.forEach(section => {
            if (isInViewport(section)) {
                animateCounters();
            }
        });
    }
    
    // Check on scroll and initial load
    window.addEventListener('scroll', checkCounters);
    checkCounters();
});
