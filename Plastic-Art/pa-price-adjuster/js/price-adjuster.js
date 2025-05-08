jQuery(document).ready(function($) {
    console.log('PA Price Adjuster script loaded.');

    $(document).on('uni_cpo_set_price_event', function(e, priceInput) {
        let priceStr = '';

        // Handle jQuery object (DOM element)
        if (typeof priceInput === 'object' && priceInput instanceof jQuery) {
            priceStr = priceInput.text();
        } else if (typeof priceInput === 'string') {
            priceStr = priceInput;
        } else {
            console.warn('Unknown price input type:', priceInput);
            return;
        }

        console.log('Parsed priceStr:', priceStr);

        // Remove currency symbols like £ or &pound;
        priceStr = priceStr.replace(/[^0-9.,-]/g, ''); // Keep . , and - for now
        priceStr = priceStr.replace(/,/g, ''); // Remove comma before parsing

        let price = parseFloat(priceStr);
        if (isNaN(price)) {
            console.warn('Invalid numeric price:', priceStr);
            return;
        }

        if (!paAdjuster || !paAdjuster.percent) {
            console.warn('paAdjuster not defined or missing percent value');
            return;
        }

        const increasePercent = parseFloat(paAdjuster.percent);
        const adjustedPrice = price + (price * (increasePercent / 100));

        console.log(`Original price: ${price}, Increase: ${increasePercent}%, Adjusted: ${adjustedPrice}`);

        // Replace the visible price
        $('.wc-block-components-product-price').text('£' + adjustedPrice.toFixed(2));
    });
});
