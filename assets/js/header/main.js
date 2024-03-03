// Redirect to the selected language
document.getElementById('local-selector').addEventListener('change', function() {
    window.location = '/'+this.value.toLowerCase();
});

// Set the selected language
document.getElementById('local-selector').value = window.location.pathname.split('/')[1].toUpperCase();
// if local is not set, set it to the default language
if (document.getElementById('local-selector').value === '') {
    document.getElementById('local-selector').value = 'EN';
}
