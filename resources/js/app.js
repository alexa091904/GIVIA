/**
 * GIVIA Gift Shop - Main JavaScript
 */

// Load the bootstrap configuration
require('./bootstrap');

// Your custom GIVIA JavaScript
console.log('GIVIA Gift Shop is ready!');

// Cart functions (will be implemented later)
window.GIVIA = {
    cart: {
        add: function(productId, quantity = 1) {
            console.log('Adding product ' + productId + ' to cart');
            // You'll implement AJAX call here later
        },
        remove: function(cartItemId) {
            console.log('Removing item ' + cartItemId + ' from cart');
        },
        update: function(cartItemId, quantity) {
            console.log('Updating item ' + cartItemId + ' to quantity ' + quantity);
        }
    }
};