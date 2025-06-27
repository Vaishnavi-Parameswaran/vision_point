// Simple tab switching logic
const tabs = document.querySelectorAll('.login-tab');
const forms = document.querySelectorAll('form');


tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    // Remove active from all tabs & forms
    tabs.forEach(t => t.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));

    // Add active to clicked tab and corresponding form
    tab.classList.add('active');
    const target = tab.getAttribute('data-target');
    document.getElementById(target).classList.add('active');
  });
});