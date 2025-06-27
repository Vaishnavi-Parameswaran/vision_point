// Show current time
function updateTime() {
  const now = new Date();
  document.getElementById('current-time').textContent =
    now.toLocaleString('en-US', {
      dateStyle: 'long',
      timeStyle: 'short',
    });
}

setInterval(updateTime, 1000);
updateTime();

// Toggle sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.querySelector('.sidebar');
menuToggle.addEventListener('click', () => {
  sidebar.classList.toggle('open');
});
