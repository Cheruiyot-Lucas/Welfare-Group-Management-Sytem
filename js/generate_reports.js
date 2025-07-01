function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

async function downloadAsPDF() {
    try {
        if (typeof html2pdf !== 'function') {
            // Dynamically load the library if not already loaded
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js');
        }

        // Get the element to print
        const element = document.getElementById('pdf-content') || document.body;

        // Show loading indicator
        const loading = document.createElement('div');
        loading.textContent = 'Preparing PDF...';
        loading.style.position = 'fixed';
        loading.style.top = '20px';
        loading.style.right = '20px';
        loading.style.padding = '10px';
        loading.style.background = 'white';
        loading.style.border = '1px solid black';
        loading.style.zIndex = '9999';
        document.body.appendChild(loading);

        // PDF options
        const opt = {
            margin: 10,
            filename: 'document.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                logging: true,
                useCORS: true,
                allowTaint: true
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Generate and download PDF
        await html2pdf().set(opt).from(element).save();

        // Remove loading indicator
        document.body.removeChild(loading);

    } catch (error) {
        console.error('PDF generation failed:', error);
        alert('Failed to generate PDF. Please try again or contact support.');
    }
}

// Helper function to load scripts dynamically
function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}