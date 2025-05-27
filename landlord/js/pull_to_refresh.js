// pull_to_refresh.js

let startY = 0;
let isPulled = false;

document.addEventListener('touchstart', function(e) {
  if (window.scrollY === 0) {
    startY = e.touches[0].clientY;
  }
}, { passive: true });

document.addEventListener('touchmove', function(e) {
  const currentY = e.touches[0].clientY;
  if (window.scrollY === 0 && currentY - startY > 100 && !isPulled) {
    isPulled = true;
    location.reload(); // Refresh the page
  }
}, { passive: true });

document.addEventListener('touchend', function() {
  isPulled = false;
});
