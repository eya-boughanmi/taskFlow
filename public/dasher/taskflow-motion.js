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

  let revealFrame = null;
  const scheduleReveal = () => {
    if (revealFrame !== null) {
      cancelAnimationFrame(revealFrame);
    }
    revealFrame = requestAnimationFrame(() => {
      revealFrame = null;
      reveal();
    });
  };

  // DOMContentLoaded : premier chargement complet (scripts defer).
  // turbo:load : chaque navigation Turbo (le contenu du body est remplacé sans reload ; DOMContentLoaded ne refire pas).
  document.addEventListener('DOMContentLoaded', scheduleReveal);
  document.addEventListener('turbo:load', scheduleReveal);
})();
