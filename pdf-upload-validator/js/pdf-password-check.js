import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js').then((pdfjsLib) => {
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.worker.min.js';

    // Get the URL of the uploaded PDF
    const fileUrl = $fileLink.attr('href') // Use the file URL from your upload check

    // Check if it's a PDF
    if (/\.pdf$/i.test(fileUrl)) {
        // Extract the filename
        const urlParts = fileUrl.split('/');
        const fileName = urlParts[urlParts.length - 1];

        // Check if the first letter of the filename is capitalized
        const capitalizedFileName = fileName.charAt(0).toUpperCase() + fileName.slice(1);
        const capitalizedFileUrl = fileUrl.replace(fileName, capitalizedFileName);

        console.log('Checking file:', capitalizedFileUrl);

        // Try to fetch the file with the capitalized filename
        fetch(capitalizedFileUrl, { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    console.log('File found with correct capitalization:', capitalizedFileUrl);
                    // You can proceed with the PDF check here
                    const loadingTask = pdfjsLib.getDocument(capitalizedFileUrl);
                    loadingTask.promise.then(function(pdf) {
                        console.log('[PDF.js] PDF loaded. Pages:', pdf.numPages);
                        // No error = file is not password protected
                    }).catch(function(error) {
                        if (error?.name === 'PasswordException') {
                            alert('This PDF is password-protected. Please upload an unlocked version.');
                        } else {
                            console.error('[PDF.js] Failed to open PDF:', error);
                        }
                    });
                } else {
                    console.error('File not found with capitalized filename:', capitalizedFileUrl);
                    alert('The file could not be found with the correct capitalization.');
                }
            })
            .catch(error => {
                console.error('Error fetching the file:', error);
                alert('Error checking the file.');
            });
    } else {
        alert('Only PDF files are allowed.');
    }
});
