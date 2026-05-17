console.log('folder UI initialized');

function previewMoodleFile(event, url, name) {
    if(event) event.preventDefault();
    
    const iframe = document.getElementById('filePreviewIframe');
    const label = document.getElementById('filePreviewOffcanvasLabel');
    const downloadBtn = document.getElementById('fileDownloadBtn');
    const loader = document.getElementById('offcanvas-loading');
    const offcanvasEl = document.getElementById('filePreviewOffcanvas');

    if (!offcanvasEl) return;

    // Set Title and Download link
    label.textContent = name;
    downloadBtn.href = url;

    // Reset loader states
    loader.classList.remove('d-none');
    loader.classList.add('d-flex');

    // Display Canvas with Programmatic Fallback Support
    try {
        let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (!bsOffcanvas) {
            bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
        }
        bsOffcanvas.show();
    } catch (error) {
        // Fallback explicit toggle rule
        offcanvasEl.classList.add('show');
        offcanvasEl.style.visibility = 'visible';
        offcanvasEl.style.display = 'block';
    }

    // Set preview frame URL target
    iframe.src = url;

    // Hide loader wrapper on framework load completition
    iframe.onload = function() {
        loader.classList.remove('d-flex');
        loader.classList.add('d-none');
    };
}

function closeMoodleOffcanvas() {
    const offcanvasEl = document.getElementById('filePreviewOffcanvas');
    const iframe = document.getElementById('filePreviewIframe');
    if(offcanvasEl) {
        // Stop playing any audio/video on background when canvas closes
        iframe.src = 'about:blank';
        
        try {
            let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if(bsOffcanvas) bsOffcanvas.hide();
        } catch(e) {
            offcanvasEl.classList.remove('show');
            offcanvasEl.style.visibility = 'hidden';
        }
    }
}