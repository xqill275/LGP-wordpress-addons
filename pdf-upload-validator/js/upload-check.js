jQuery(document).ready(function($) {
    console.log('JS Loaded');

    const waitForPreview = setInterval(() => {
        const previewEls = document.querySelectorAll('.wc-checkout-add-ons-preview');
        if (previewEls.length > 0) {
            clearInterval(waitForPreview);
            console.log('[Observer] Preview containers found:', previewEls.length);

            previewEls.forEach(function(previewEl) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length || mutation.type === 'childList') {
                            console.log('[Observer] Mutation detected. Checking file...');
                            checkUploadedFile(previewEl);
                        }
                    });
                });

                observer.observe(previewEl, { childList: true, subtree: true });
                console.log('[Observer] Watching preview:', previewEl);
            });
        }
    }, 300);

    // Core logic to validate uploaded file
    function checkUploadedFile(previewEl) {
        const $fileLink = $(previewEl).find('.file');

        if ($fileLink.length && $fileLink.attr('href')) {
            let fileUrl = $fileLink.attr('href');
            console.log('[Upload] File URL Detected:', fileUrl);

            // Check if the file is a PDF
            if (!/\.pdf$/i.test(fileUrl)) {
                alert('Only PDF files are allowed.');

                $(previewEl).addClass('hide');
                $(previewEl).siblings('.wc-checkout-add-ons-dropzone').removeClass('hide');

                $(previewEl).siblings('.wc-checkout-add-ons-feedback')
                    .removeClass('hide')
                    .text('Only PDF files are allowed.');

                $(previewEl).closest('.wc-checkout-add-ons').find('input[type="hidden"]').val('');
            } else {
                // Check PDF size and display a message
                checkPdfSize(fileUrl, previewEl);
            }
        }
    }

    // Function to check PDF size and display a message with the paper size
    function checkPdfSize(fileUrl, previewEl) {
        const loadingTask = pdfjsLib.getDocument(fileUrl);
        loadingTask.promise.then(function(pdf) {
            console.log('[PDF.js] PDF loaded. Pages:', pdf.numPages);
    
            // Get the first page's dimensions
            pdf.getPage(1).then(function(page) {
                const viewport = page.getViewport({ scale: 1 });
    
                // Convert the page size from points to mm (1 point = 0.3528 mm)
                const widthMm = viewport.width * 0.3528;
                const heightMm = viewport.height * 0.3528;
    
                console.log(`[PDF.js] Page size in mm: ${widthMm.toFixed(2)} x ${heightMm.toFixed(2)} mm`);
    
                // Determine the paper size based on the dimensions
                const paperSize = getPaperSize(widthMm, heightMm);
    
                // Create a message with the PDF size and paper size
                const sizeMessage = `This file is ${widthMm.toFixed(2)} x ${heightMm.toFixed(2)} mm (${paperSize}). Does that match your specified job size?`;
    
                // Display the size in a popup message
                alert(sizeMessage);
            });
        }).catch(function(error) {
            if (error.name === 'PasswordException') {
                console.error('[PDF.js] This PDF is password-protected.');
                alert('This PDF is password-protected. Please upload an unlocked version.');
    
                // Remove the uploaded PDF (hide the preview and show the drop zone again)
                $(previewEl).addClass('hide'); // Hide the preview container
                $(previewEl).siblings('.wc-checkout-add-ons-dropzone').removeClass('hide'); // Show the drop zone
                $(previewEl).siblings('.wc-checkout-add-ons-feedback')
                    .removeClass('hide')
                    .text('Password-protected PDFs are not allowed.');
    
                // Optionally clear the file input (hidden input field) for the user
                $(previewEl).closest('.wc-checkout-add-ons').find('input[type="hidden"]').val('');
            } else {
                console.error('[PDF.js] Failed to open PDF:', error);
                alert('Error opening PDF. Please try again.');
            }
        });
    }

    // Function to determine the paper size based on the dimensions
    function getPaperSize(widthMm, heightMm) {
        // Define paper sizes in mm
        const sizes = {
            A3: { width: 297, height: 420 },
            A4: { width: 210, height: 297 },
            A5: { width: 148, height: 210 },
            A6: { width: 105, height: 148 },
            DL: { width: 210, height: 99 }
        };

        // Check dimensions and return the corresponding paper size if smaller than or equal to each
        for (const [size, dimensions] of Object.entries(sizes)) {
            if (
                (widthMm <= dimensions.width && heightMm <= dimensions.height) ||
                (widthMm <= dimensions.height && heightMm <= dimensions.width)
            ) {
                return size;
            }
        }

        // If no match is found, return a generic size
        return 'Unknown size';
    }
});
