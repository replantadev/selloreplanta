// Test JavaScript file
console.log('Test JS file loaded successfully');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, JS working correctly');
    if (typeof jQuery !== 'undefined') {
        console.log('jQuery is available:', jQuery().jquery);
    } else {
        console.log('jQuery not found');
    }
});