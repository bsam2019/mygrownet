/**
 * Image Optimization Composable
 * Automatically resizes and compresses images before upload
 */

export interface OptimizationOptions {
    maxWidth?: number;
    maxHeight?: number;
    quality?: number;
    format?: 'jpeg' | 'png' | 'webp';
    maintainAspectRatio?: boolean;
}

export interface OptimizationResult {
    file: File;
    originalSize: number;
    optimizedSize: number;
    compressionRatio: number;
    width: number;
    height: number;
}

export function useImageOptimization() {
    /**
     * Optimize an image file
     */
    const optimizeImage = async (
        file: File,
        options: OptimizationOptions = {}
    ): Promise<OptimizationResult> => {
        const {
            maxWidth = 1920,
            maxHeight = 1080,
            quality = 0.85,
            format = 'jpeg',
            maintainAspectRatio = true,
        } = options;

        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const img = new Image();
                
                img.onload = () => {
                    // Calculate new dimensions
                    let { width, height } = img;
                    
                    if (maintainAspectRatio) {
                        if (width > maxWidth || height > maxHeight) {
                            const aspectRatio = width / height;
                            
                            if (width > height) {
                                width = Math.min(width, maxWidth);
                                height = width / aspectRatio;
                            } else {
                                height = Math.min(height, maxHeight);
                                width = height * aspectRatio;
                            }
                        }
                    } else {
                        width = Math.min(width, maxWidth);
                        height = Math.min(height, maxHeight);
                    }
                    
                    // Create canvas and draw resized image
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        reject(new Error('Failed to get canvas context'));
                        return;
                    }
                    
                    // Use better image smoothing
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    
                    // Draw image
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Convert to blob
                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                reject(new Error('Failed to create blob'));
                                return;
                            }
                            
                            // Create new file
                            const optimizedFile = new File(
                                [blob],
                                file.name.replace(/\.[^.]+$/, `.${format === 'jpeg' ? 'jpg' : format}`),
                                { type: `image/${format}` }
                            );
                            
                            resolve({
                                file: optimizedFile,
                                originalSize: file.size,
                                optimizedSize: blob.size,
                                compressionRatio: Math.round((1 - blob.size / file.size) * 100),
                                width: Math.round(width),
                                height: Math.round(height),
                            });
                        },
                        `image/${format}`,
                        quality
                    );
                };
                
                img.onerror = () => {
                    reject(new Error('Failed to load image'));
                };
                
                img.src = e.target?.result as string;
            };
            
            reader.onerror = () => {
                reject(new Error('Failed to read file'));
            };
            
            reader.readAsDataURL(file);
        });
    };

    /**
     * Optimize logo specifically (smaller dimensions, higher quality)
     */
    const optimizeLogo = async (file: File): Promise<OptimizationResult> => {
        return optimizeImage(file, {
            maxWidth: 800,
            maxHeight: 400,
            quality: 0.9,
            format: 'png', // Logos often need transparency
            maintainAspectRatio: true,
        });
    };

    /**
     * Optimize background image (larger dimensions, good quality)
     */
    const optimizeBackground = async (file: File): Promise<OptimizationResult> => {
        return optimizeImage(file, {
            maxWidth: 1920,
            maxHeight: 1080,
            quality: 0.85,
            format: 'jpeg',
            maintainAspectRatio: true,
        });
    };

    /**
     * Optimize thumbnail (small dimensions, lower quality)
     */
    const optimizeThumbnail = async (file: File): Promise<OptimizationResult> => {
        return optimizeImage(file, {
            maxWidth: 400,
            maxHeight: 400,
            quality: 0.8,
            format: 'jpeg',
            maintainAspectRatio: true,
        });
    };

    /**
     * Format file size for display
     */
    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    /**
     * Check if file is an image
     */
    const isImage = (file: File): boolean => {
        return file.type.startsWith('image/');
    };

    /**
     * Check if image needs optimization
     */
    const needsOptimization = (file: File, maxSize: number = 500 * 1024): boolean => {
        return file.size > maxSize;
    };

    return {
        optimizeImage,
        optimizeLogo,
        optimizeBackground,
        optimizeThumbnail,
        formatFileSize,
        isImage,
        needsOptimization,
    };
}
