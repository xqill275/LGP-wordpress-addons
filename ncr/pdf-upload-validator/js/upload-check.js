jQuery(document).ready(function($) {
    const pdfFileWarning = "Preflight Warning: Our preferred format for printing is PDF. Please convert or save your file as a PDF and reupload. If you cannot create a PDF, please email your current file to orders@ncr4less.co.uk after checkout."
    const pdfSizeWarning = "Preflight Check: Your file measures XXXmm x XXXmm (xx), are you happy these are the correct dimensions for the job you require? "
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
                alert(pdfFileWarning);

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
                const paperSize = fileSizeCalc(widthMm, heightMm);
                fileSizeCalc(widthMm, heightMm);
    
                // Create a message with the PDF size and paper size
                const sizeMessage = `Preflight Check: Your file measures ${widthMm.toFixed(2)}mm x ${heightMm.toFixed(2)}mm (${paperSize}). Are you happy these are the correct dimensions for the job you require?`;
    
                const event = new CustomEvent('pdfValidator:showConfirmModal', {
                    detail: {
                        message: sizeMessage,
                        previewEl: previewEl
                    }
                });
                window.dispatchEvent(event);
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

    function fileSizeCalc(widthMm, heightMm) {
        const sizes = {
            A3: { width: 297, height: 420 },
            A4: { width: 210, height: 297 },
            A5: { width: 148, height: 210 },
            A6: { width: 105, height: 148 },
            DL: { width: 210, height: 99 }
        };
    
        let currentLowestSize = null;
    
        for (const [size, dimensions] of Object.entries(sizes)) {
            if (
                (widthMm <= dimensions.width && heightMm <= dimensions.height) ||
                (widthMm <= dimensions.height && heightMm <= dimensions.width)
            ) {
                if (!currentLowestSize || (
                    dimensions.width * dimensions.height <
                    sizes[currentLowestSize].width * sizes[currentLowestSize].height
                )) {
                    currentLowestSize = size;
                }
            }
        }
    
        console.log('[FileSizeCalc] Smallest fitting size:', currentLowestSize);
        if (!currentLowestSize) {
            return 'unknown size'
        } else {
            return currentLowestSize;
        }
    }



    window.addEventListener('pdfValidator:showConfirmModal', function(e) {
        const { message, previewEl } = e.detail;
    
        // Check if modal already exists
        if (!document.getElementById('pdf-confirm-modal')) {
            $('body').append(`
                <div id="pdf-confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:20px; max-width:500px; width:90%; border-radius:8px; text-align:center; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                        <p id="pdf-confirm-text" style="margin-bottom: 20px;"></p>
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <button id="pdf-confirm-yes">Yes</button>
                            <button id="pdf-confirm-cancel">No - I will reupload the correct size</button>
                        </div>
                    </div>
                </div>
            `);
        }
    
        // Set text and show modal
        $('#pdf-confirm-text').text(message);
        $('#pdf-confirm-modal').fadeIn();
    
        // Remove any old listeners
        $('#pdf-confirm-yes').off('click');
        $('#pdf-confirm-cancel').off('click');
    
        // YES
        $('#pdf-confirm-yes').on('click', function () {
            $('#pdf-confirm-modal').fadeOut();
            // User confirmed, do nothing (they accepted dimensions)
        });
    
        // CANCEL
        $('#pdf-confirm-cancel').on('click', function () {
            $('#pdf-confirm-modal').fadeOut();
    
            // User rejected — remove uploaded file and reset
            $(previewEl).addClass('hide');
            $(previewEl).siblings('.wc-checkout-add-ons-dropzone').removeClass('hide');
            $(previewEl).siblings('.wc-checkout-add-ons-feedback')
                .removeClass('hide')
                .text('Please reupload your file with the correct dimensions.');
            $(previewEl).closest('.wc-checkout-add-ons').find('input[type="hidden"]').val('');
        });
    });
});


