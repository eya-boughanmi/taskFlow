'use strict';

(() => {
  const reveal = () => {
    const nodes = document.querySelectorAll('.tf-reveal');
    if (!nodes.length || !('IntersectionObserver' in window)) {
      nodes.forEach((el) => el.classList.add('tf-reveal-visible'));
      return;
    }
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('tf-reveal-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { root: null, threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );
    nodes.forEach((el) => io.observe(el));
  };

  document.addEventListener('DOMContentLoaded', reveal);
})();
