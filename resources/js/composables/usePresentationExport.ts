export function usePresentationExport() {
    
    const generatePowerPoint = async (referralCode: string, onProgress?: (current: number, total: number) => void) => {
        try {
            console.log('Starting PowerPoint generation...');
            
            // Dynamic imports - using html2canvas-pro for OKLCH support
            const PptxGenJS = (await import('pptxgenjs')).default;
            const html2canvas = (await import('html2canvas-pro')).default;
            console.log('Libraries loaded');
            
            const pptx = new PptxGenJS();
            console.log('PptxGenJS instance created');
            
            // Set presentation properties
            pptx.author = 'MyGrowNet';
            pptx.company = 'MyGrowNet';
            pptx.subject = 'GrowNet Platform Presentation';
            pptx.title = 'Join MyGrowNet - Learn, Earn, Grow';
            pptx.layout = 'LAYOUT_16x9';
            
            // Get all slide elements
            const slideElements = document.querySelectorAll('.h-full.w-full.flex-shrink-0');
            console.log('Found slides:', slideElements.length);
            
            if (slideElements.length === 0) {
                throw new Error('No slides found');
            }
            
            // Capture each slide as image and add to PowerPoint
            for (let i = 0; i < slideElements.length; i++) {
                console.log(`Capturing slide ${i + 1}/${slideElements.length}...`);
                
                // Report progress
                if (onProgress) {
                    onProgress(i + 1, slideElements.length);
                }
                
                const slideElement = slideElements[i] as HTMLElement;
                
                // Get the actual dimensions of the slide
                const rect = slideElement.getBoundingClientRect();
                
                // Calculate scale to get high resolution (target 1920x1080)
                const targetWidth = 1920;
                const targetHeight = 1080;
                const scaleX = targetWidth / rect.width;
                const scaleY = targetHeight / rect.height;
                const scale = Math.max(scaleX, scaleY, 2); // Use at least 2x, or higher to reach target resolution
                
                // Capture the slide with html2canvas-pro (supports OKLCH)
                const canvas = await html2canvas(slideElement, {
                    scale: scale,
                    useCORS: true,
                    logging: false,
                    allowTaint: true,
                    backgroundColor: null,
                    width: rect.width,
                    height: rect.height,
                    windowWidth: rect.width,
                    windowHeight: rect.height,
                });
                
                // Convert canvas to base64 image with maximum quality
                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // Add slide to PowerPoint
                const slide = pptx.addSlide();
                
                // Add the captured image to fill the entire slide
                slide.addImage({
                    data: imgData,
                    x: 0,
                    y: 0,
                    w: '100%',
                    h: '100%',
                    sizing: {
                        type: 'cover',
                        w: '100%',
                        h: '100%'
                    }
                });
            }
            
            // Save the presentation
            console.log('Saving PowerPoint file...');
            await pptx.writeFile({ fileName: `MyGrowNet_Presentation_${referralCode}.pptx` });
            console.log('PowerPoint file saved successfully');
        } catch (error) {
            console.error('Error in generatePowerPoint:', error);
            throw error;
        }
    };
    
    const generatePDF = async (slidesCount: number, onProgress?: (current: number, total: number) => void) => {
        try {
            console.log('Starting PDF generation...');
            
            // Dynamic imports - using html2canvas-pro for OKLCH support
            const { jsPDF } = await import('jspdf');
            const html2canvas = (await import('html2canvas-pro')).default;
            console.log('PDF libraries loaded');
            
            // Create PDF in landscape mode
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: [1920, 1080]
            });
            console.log('PDF instance created');
            
            // Get all slide elements
            const slideElements = document.querySelectorAll('.h-full.w-full.flex-shrink-0');
            console.log('Found slides:', slideElements.length);
            
            if (slideElements.length === 0) {
                throw new Error('No slides found');
            }
            
            // Capture each slide
            for (let i = 0; i < slideElements.length; i++) {
                console.log(`Capturing slide ${i + 1}/${slideElements.length}...`);
                
                // Report progress
                if (onProgress) {
                    onProgress(i + 1, slideElements.length);
                }
                
                const slideElement = slideElements[i] as HTMLElement;
                
                // Get the actual dimensions of the slide
                const rect = slideElement.getBoundingClientRect();
                
                // Calculate scale to get high resolution (target 1920x1080)
                const targetWidth = 1920;
                const targetHeight = 1080;
                const scaleX = targetWidth / rect.width;
                const scaleY = targetHeight / rect.height;
                const scale = Math.max(scaleX, scaleY, 2); // Use at least 2x, or higher to reach target resolution
                
                // Capture the slide with html2canvas-pro (supports OKLCH)
                const canvas = await html2canvas(slideElement, {
                    scale: scale,
                    useCORS: true,
                    logging: false,
                    allowTaint: true,
                    backgroundColor: null,
                    width: rect.width,
                    height: rect.height,
                    windowWidth: rect.width,
                    windowHeight: rect.height,
                });
                
                // Convert canvas to image with maximum quality (PNG for better quality)
                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // Add page (except for first slide)
                if (i > 0) {
                    pdf.addPage([1920, 1080], 'landscape');
                }
                
                // Add image to PDF (using PNG for better quality)
                pdf.addImage(imgData, 'PNG', 0, 0, 1920, 1080, undefined, 'FAST');
            }
            
            // Save the PDF
            console.log('Saving PDF file...');
            pdf.save(`MyGrowNet_Presentation_${Date.now()}.pdf`);
            console.log('PDF file saved successfully');
        } catch (error) {
            console.error('Error in generatePDF:', error);
            throw error;
        }
    };
    
    return {
        generatePowerPoint,
        generatePDF,
    };
}
