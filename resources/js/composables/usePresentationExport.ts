export function usePresentationExport() {
    
    /**
     * Detect if user is on mobile device
     */
    const isMobileDevice = () => {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    };
    
    /**
     * Capture a slide element with optimized settings for mobile
     */
    const captureSlide = async (slideElement: HTMLElement, html2canvas: any, isMobile: boolean) => {
        // Get the slide's scroll container
        const scrollContainer = slideElement.closest('.overflow-y-auto') as HTMLElement;
        const hasScroll = scrollContainer && scrollContainer.scrollHeight > scrollContainer.clientHeight;
        
        // If slide has scrollable content, we need to capture the full height
        const fullHeight = hasScroll ? scrollContainer.scrollHeight : slideElement.offsetHeight;
        const fullWidth = slideElement.offsetWidth;
        
        // Mobile: Use lower scale to reduce memory usage
        // Desktop: Use higher scale for better quality
        const scale = isMobile ? 2 : 3;
        
        // Temporarily remove scroll and expand to full height for capture
        let originalOverflow = '';
        let originalHeight = '';
        if (hasScroll && scrollContainer) {
            originalOverflow = scrollContainer.style.overflow;
            originalHeight = scrollContainer.style.height;
            scrollContainer.style.overflow = 'visible';
            scrollContainer.style.height = 'auto';
        }
        
        try {
            // Capture the full slide content
            const canvas = await html2canvas(slideElement, {
                scale: scale,
                useCORS: true,
                logging: false,
                allowTaint: true,
                backgroundColor: '#ffffff', // White background for better PDF rendering
                width: fullWidth,
                height: fullHeight,
                windowWidth: fullWidth,
                windowHeight: fullHeight,
                scrollY: 0,
                scrollX: 0,
            });
            
            // Restore scroll properties
            if (hasScroll && scrollContainer) {
                scrollContainer.style.overflow = originalOverflow;
                scrollContainer.style.height = originalHeight;
            }
            
            return canvas;
        } catch (error) {
            // Restore on error too
            if (hasScroll && scrollContainer) {
                scrollContainer.style.overflow = originalOverflow;
                scrollContainer.style.height = originalHeight;
            }
            throw error;
        }
    };
    
    const generatePowerPoint = async (referralCode: string, onProgress?: (current: number, total: number) => void) => {
        try {
            const isMobile = isMobileDevice();
            
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
            pptx.title = 'Join MyGrowNet - Access, Earn, Grow';
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
                
                // Capture the slide
                const canvas = await captureSlide(slideElement, html2canvas, isMobile);
                
                // Convert canvas to base64 image
                const imageFormat = isMobile ? 'JPEG' : 'PNG';
                const imageQuality = isMobile ? 0.85 : 1.0;
                const imgData = canvas.toDataURL(`image/${imageFormat.toLowerCase()}`, imageQuality);
                
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
                        type: 'contain',
                        w: '100%',
                        h: '100%'
                    }
                });
                
                // Free memory on mobile after each slide
                if (isMobile && i < slideElements.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 150));
                }
            }
            
            // Save the presentation
            const deviceType = isMobile ? 'Mobile' : 'Desktop';
            console.log('Saving PowerPoint file...');
            await pptx.writeFile({ fileName: `MyGrowNet_${deviceType}_${referralCode}.pptx` });
            console.log('PowerPoint file saved successfully');
        } catch (error) {
            console.error('Error in generatePowerPoint:', error);
            throw error;
        }
    };
    
    const generatePDF = async (onProgress?: (current: number, total: number) => void) => {
        try {
            console.log('Starting PDF generation...');
            
            // Dynamic imports - using html2canvas-pro for OKLCH support
            const { jsPDF } = await import('jspdf');
            const html2canvas = (await import('html2canvas-pro')).default;
            console.log('PDF libraries loaded');
            
            const isMobile = isMobileDevice();
            
            // For mobile: Use portrait A4 size (210mm x 297mm = 595px x 842px at 72dpi)
            // This ensures full-page viewing in PDF readers
            const pageWidth = isMobile ? 595 : 1920;
            const pageHeight = isMobile ? 842 : 1080;
            const orientation = isMobile ? 'portrait' : 'landscape';
            
            // Create PDF with proper page size
            const pdf = new jsPDF({
                orientation: orientation,
                unit: 'px',
                format: [pageWidth, pageHeight],
                compress: true, // Enable compression for smaller file size
            });
            console.log(`PDF instance created (${orientation}, ${pageWidth}x${pageHeight})`);
            
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
                
                // Capture the slide with mobile optimization
                const canvas = await captureSlide(slideElement, html2canvas, isMobile);
                
                // Convert canvas to image with good quality (JPEG for smaller size on mobile)
                const imageFormat = isMobile ? 'JPEG' : 'PNG';
                const imageQuality = isMobile ? 0.92 : 1.0;
                const imgData = canvas.toDataURL(`image/${imageFormat.toLowerCase()}`, imageQuality);
                
                // Add page (except for first slide)
                if (i > 0) {
                    pdf.addPage([pageWidth, pageHeight], orientation);
                }
                
                // For mobile: Fill entire page (no margins)
                // For desktop: Maintain aspect ratio with centering
                if (isMobile) {
                    // Fill the entire page - stretch to fit
                    pdf.addImage(imgData, imageFormat, 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                } else {
                    // Desktop: Calculate dimensions to fit the page while maintaining aspect ratio
                    const imgWidth = canvas.width;
                    const imgHeight = canvas.height;
                    const ratio = Math.min(pageWidth / imgWidth, pageHeight / imgHeight);
                    const scaledWidth = imgWidth * ratio;
                    const scaledHeight = imgHeight * ratio;
                    
                    // Center the image on the page
                    const x = (pageWidth - scaledWidth) / 2;
                    const y = (pageHeight - scaledHeight) / 2;
                    
                    pdf.addImage(imgData, imageFormat, x, y, scaledWidth, scaledHeight, undefined, 'FAST');
                }
                
                // Free memory on mobile after each slide
                if (isMobile && i < slideElements.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
            }
            
            // Save the PDF
            const deviceType = isMobile ? 'Mobile' : 'Desktop';
            console.log('Saving PDF file...');
            pdf.save(`MyGrowNet_${deviceType}_${Date.now()}.pdf`);
            console.log('PDF file saved successfully');
        } catch (error) {
            console.error('Error in generatePDF:', error);
            throw error;
        }
    };
    
    return {
        generatePowerPoint,
        generatePDF,
        isMobileDevice,
    };
}
