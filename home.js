/* Home page: scroll-triggered animations and stat counters */
(function () {
    'use strict';

    const ANIMATE_CLASS = 'animate-on-scroll';
    const VISIBLE_CLASS = 'is-visible';
    const OPTIONS = { root: null, rootMargin: '0px 0px -80px 0px', threshold: 0.1 };

    function initScrollAnimations() {
        var elements = document.querySelectorAll('.' + ANIMATE_CLASS);
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var delay = el.getAttribute('data-delay') || 0;
                setTimeout(function () {
                    el.classList.add(VISIBLE_CLASS);
                }, delay * 120);
            });
        }, OPTIONS);

        elements.forEach(function (el) { observer.observe(el); });
    }

    function initStatCounters() {
        var stats = document.querySelectorAll('.stat-value[data-target]');
        if (!stats.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var target = parseInt(el.getAttribute('data-target'), 10);
                var duration = 1500;
                var start = 0;
                var startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var easeOut = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.floor(easeOut * target);
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
                observer.unobserve(el);
            });
        }, { threshold: 0.5 });

        stats.forEach(function (el) { observer.observe(el); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initScrollAnimations();
            initStatCounters();
        });
    } else {
        initScrollAnimations();
        initStatCounters();
    }
})();
