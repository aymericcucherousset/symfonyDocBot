// Display loading spinner on form submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const answer = document.querySelector('span[aria-busy]');
    form.addEventListener('submit', function() {
        answer.style.display = 'block';
    });
});
