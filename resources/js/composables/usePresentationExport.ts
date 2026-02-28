export function usePresentationExport() {
    
    /**
     * Detect if user is on mobile device
     */
    const isMobileDevice = () => {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    };
    
    /**
     * Capture a slide element with proper dimensions for mobile or desktop
     */
    const captureSlide = async (slideElement: HTMLElement, html2canvas: any) => {
        const isMobile = isMobileDevice();
        
        // For mobile: Use 9:16 portrait ratio (1080x1920) - standard mobile screen
        // For desktop: Use 16:9 landscape ratio (1920x1080) - standard presentation
        const targetWidth = isMobile ? 1080 : 1920;
        const targetHeight = isMobile ? 1920 : 1080;
        
        // Get the slide's scroll container - check if it exists and has scroll
        const scrollContainer = slideElement.closest('.overflow-y-auto') as HTMLElement | null;
        const hasScroll = scrollContainer ? scrollContainer.scrollHeight > scrollContainer.clientHeight : false;
        
        // If slide has scrollable content, we need to capture the full height
        const fullHeight = (hasScroll && scrollContainer) ? scrollContainer.scrollHeight : slideElement.offsetHeight;
        const fullWidth = slideElement.offsetWidth;
        
        // Calculate scale to achieve target resolution
        const scaleX = targetWidth / fullWidth;
        const scaleY = targetHeight / fullHeight;
        const scale = Math.min(scaleX, scaleY, 3); // Cap at 3x for performance
        
        // Temporarily remove scroll and expand to full height for capture
        let originalOverflow = '';
        let originalHeight = '';
        if (hasScroll && scrollContainer) {
            originalOverflow = scrollContainer.style.overflow || '';
            originalHeight = scrollContainer.style.height || '';
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
                backgroundColor: null,
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
            console.log('Starting PowerPoint generation...');
            
            // Dynamic imports - using html2canvas-pro for OKLCH support
            const PptxGenJS = (await import('pptxgenjs')).default;
            const html2canvas = (await import('html2canvas-pro')).default;
            console.log('Libraries loaded');
            
            const pptx = new PptxGenJS();
            console.log('PptxGenJS instance created');
            
            const isMobile = isMobileDevice();
            
            // Set presentation properties
            pptx.author = 'MyGrowNet';
            pptx.company = 'MyGrowNet';
            pptx.subject = 'GrowNet Platform Presentation';
            pptx.title = 'Join MyGrowNet - Access, Earn, Grow';
            
            // Use portrait layout for mobile, landscape for desktop
            if (isMobile) {
                pptx.layout = 'LAYOUT_CUSTOM';
                pptx.defineLayout({ name: 'MOBILE_PORTRAIT', width: 5.625, height: 10 }); // 9:16 ratio in inches
                pptx.layout = 'MOBILE_PORTRAIT';
            } else {
                pptx.layout = 'LAYOUT_16x9';
            }
            
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
                
                // Capture the slide with proper dimensions
                const canvas = await captureSlide(slideElement, html2canvas);
                
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
                        type: 'contain', // Use contain to preserve aspect ratio
                        w: '100%',
                        h: '100%'
                    }
                });
            }
            
            // Save the presentation
            const deviceType = isMobile ? 'Mobile' : 'Desktop';
            console.log(`Saving PowerPoint file for ${deviceType}...`);
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
            
            // Use portrait for mobile, landscape for desktop
            const orientation = isMobile ? 'portrait' : 'landscape';
            const width = isMobile ? 1080 : 1920;
            const height = isMobile ? 1920 : 1080;
            
            // Create PDF
            const pdf = new jsPDF({
                orientation: orientation,
                unit: 'px',
                format: [width, height]
            });
            console.log(`PDF instance created (${orientation})`);
            
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
                
                // Capture the slide with proper dimensions
                const canvas = await captureSlide(slideElement, html2canvas);
                
                // Convert canvas to image with maximum quality (PNG for better quality)
                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // Add page (except for first slide)
                if (i > 0) {
                    pdf.addPage([width, height], orientation);
                }
                
                // Add image to PDF - fit to page while maintaining aspect ratio
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const ratio = Math.min(width / imgWidth, height / imgHeight);
                const scaledWidth = imgWidth * ratio;
                const scaledHeight = imgHeight * ratio;
                const x = (width - scaledWidth) / 2;
                const y = (height - scaledHeight) / 2;
                
                pdf.addImage(imgData, 'PNG', x, y, scaledWidth, scaledHeight, undefined, 'FAST');
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
    };
}
