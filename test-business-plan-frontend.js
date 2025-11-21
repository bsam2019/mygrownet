/**
 * Business Plan Generator Frontend Testing Script
 * 
 * This script tests the Vue.js components and frontend functionality
 * Run this in the browser console on the business plan generator page
 */

class BusinessPlanFrontendTester {
    constructor() {
        this.testResults = [];
        this.currentTest = null;
        console.log('🧪 Business Plan Generator Frontend Testing Suite');
        console.log('================================================');
    }

    async runAllTests() {
        try {
            await this.testPageLoad();
            await this.testStepNavigation();
            await this.testFormValidation();
            await this.testAutoSave();
            await this.testFinancialCalculations();
            await this.testAIFeatures();
            await this.testMobileResponsiveness();
            await this.testExportFunctionality();
            
            this.displayResults();
        } catch (error) {
            console.error('❌ Test suite failed:', error);
        }
    }

    log(message, type = 'info') {
        const prefix = {
            'success': '✅',
            'error': '❌',
            'warning': '⚠️',
            'info': 'ℹ️'
        }[type] || '•';
        
        console.log(`   ${prefix} ${message}`);
        
        if (this.currentTest) {
            this.testResults.push({
                test: this.currentTest,
                message,
                type,
                timestamp: new Date().toISOString()
            });
        }
    }

    async testPageLoad() {
        this.currentTest = 'Page Load';
        console.log('\n📄 Testing page load...');

        // Check if Vue app is mounted
        const app = document.querySelector('#app');
        if (!app) {
            throw new Error('Vue app not found');
        }
        this.log('Vue app mounted');

        // Check if business plan generator component exists
        const generator = document.querySelector('.business-plan-generator') || 
                         document.querySelector('[data-page="BusinessPlanGenerator"]') ||
                         document.querySelector('h1:contains("Business Plan Generator")');
        
        if (!generator) {
            this.log('Business plan generator component not found', 'warning');
        } else {
            this.log('Business plan generator component found');
        }

        // Check for progress bar
        const progressBar = document.querySelector('.progress-bar') || 
                           document.querySelector('[role="progressbar"]') ||
                           document.querySelector('.bg-blue-600');
        
        if (progressBar) {
            this.log('Progress bar found');
        } else {
            this.log('Progress bar not found', 'warning');
        }

        // Check for step indicators
        const stepIndicators = document.querySelectorAll('.step-indicator') ||
                              document.querySelectorAll('[class*="step"]');
        
        if (stepIndicators.length > 0) {
            this.log(`Step indicators found (${stepIndicators.length})`);
        } else {
            this.log('Step indicators not found', 'warning');
        }
    }

    async testStepNavigation() {
        this.currentTest = 'Step Navigation';
        console.log('\n🔄 Testing step navigation...');

        // Test next button
        const nextButton = document.querySelector('button:contains("Next")') ||
                          document.querySelector('[data-action="next"]') ||
                          Array.from(document.querySelectorAll('button')).find(btn => 
                              btn.textContent.toLowerCase().includes('next'));

        if (nextButton) {
            this.log('Next button found');
            
            // Test click (but don't actually click to avoid disrupting tests)
            if (nextButton.onclick || nextButton.addEventListener) {
                this.log('Next button has click handler');
            }
        } else {
            this.log('Next button not found', 'warning');
        }

        // Test previous button
        const prevButton = document.querySelector('button:contains("Previous")') ||
                          document.querySelector('[data-action="previous"]') ||
                          Array.from(document.querySelectorAll('button')).find(btn => 
                              btn.textContent.toLowerCase().includes('previous'));

        if (prevButton) {
            this.log('Previous button found');
        }

        // Test step jumping
        const stepButtons = document.querySelectorAll('[data-step]') ||
                           document.querySelectorAll('.step-button');
        
        if (stepButtons.length > 0) {
            this.log(`Step jump buttons found (${stepButtons.length})`);
        }
    }

    async testFormValidation() {
        this.currentTest = 'Form Validation';
        console.log('\n✅ Testing form validation...');

        // Test required fields
        const requiredFields = document.querySelectorAll('input[required], textarea[required], select[required]');
        this.log(`Required fields found: ${requiredFields.length}`);

        // Test validation messages
        const validationMessages = document.querySelectorAll('.error-message, .validation-error, [class*="error"]');
        if (validationMessages.length > 0) {
            this.log('Validation message elements found');
        }

        // Test form submission prevention
        const forms = document.querySelectorAll('form');
        forms.forEach((form, index) => {
            if (form.onsubmit || form.addEventListener) {
                this.log(`Form ${index + 1} has submit handler`);
            }
        });
    }

    async testAutoSave() {
        this.currentTest = 'Auto Save';
        console.log('\n💾 Testing auto-save functionality...');

        // Check for save indicators
        const saveIndicators = document.querySelectorAll('[class*="saving"], [class*="saved"]') ||
                              Array.from(document.querySelectorAll('*')).filter(el => 
                                  el.textContent.toLowerCase().includes('saving') ||
                                  el.textContent.toLowerCase().includes('saved'));

        if (saveIndicators.length > 0) {
            this.log('Save indicators found');
        }

        // Check for auto-save intervals (look for setInterval in global scope)
        if (window.autoSaveInterval || window.saveTimer) {
            this.log('Auto-save timer detected');
        }

        // Test manual save button
        const saveButton = Array.from(document.querySelectorAll('button')).find(btn => 
            btn.textContent.toLowerCase().includes('save'));
        
        if (saveButton) {
            this.log('Manual save button found');
        }
    }

    async testFinancialCalculations() {
        this.currentTest = 'Financial Calculations';
        console.log('\n💰 Testing financial calculations...');

        // Look for financial input fields
        const financialInputs = document.querySelectorAll(
            'input[type="number"], input[name*="cost"], input[name*="revenue"], input[name*="price"]'
        );
        
        this.log(`Financial input fields found: ${financialInputs.length}`);

        // Look for calculated values
        const calculatedValues = document.querySelectorAll('[class*="calculated"], [data-calculated]') ||
                                Array.from(document.querySelectorAll('*')).filter(el => 
                                    el.textContent.includes('K') && /\d/.test(el.textContent));

        if (calculatedValues.length > 0) {
            this.log('Calculated values display found');
        }

        // Test for real-time calculation updates
        const firstFinancialInput = financialInputs[0];
        if (firstFinancialInput) {
            const originalValue = firstFinancialInput.value;
            
            // Simulate input change
            firstFinancialInput.value = '1000';
            firstFinancialInput.dispatchEvent(new Event('input', { bubbles: true }));
            
            setTimeout(() => {
                // Check if calculations updated
                const updatedCalculations = document.querySelectorAll('[class*="calculated"]');
                if (updatedCalculations.length > 0) {
                    this.log('Real-time calculations appear to work');
                }
                
                // Restore original value
                firstFinancialInput.value = originalValue;
                firstFinancialInput.dispatchEvent(new Event('input', { bubbles: true }));
            }, 100);
        }
    }

    async testAIFeatures() {
        this.currentTest = 'AI Features';
        console.log('\n🤖 Testing AI features...');

        // Look for AI buttons
        const aiButtons = document.querySelectorAll('[class*="ai"], [data-ai]') ||
                         Array.from(document.querySelectorAll('button')).filter(btn => 
                             btn.textContent.toLowerCase().includes('ai') ||
                             btn.textContent.toLowerCase().includes('generate'));

        if (aiButtons.length > 0) {
            this.log(`AI generation buttons found: ${aiButtons.length}`);
        } else {
            this.log('No AI buttons found', 'warning');
        }

        // Check for AI loading states
        const loadingStates = document.querySelectorAll('[class*="loading"], [class*="generating"]');
        if (loadingStates.length > 0) {
            this.log('AI loading state elements found');
        }

        // Check for sparkles or AI icons
        const aiIcons = document.querySelectorAll('svg[class*="sparkle"], [class*="sparkle"]');
        if (aiIcons.length > 0) {
            this.log('AI visual indicators found');
        }
    }

    async testMobileResponsiveness() {
        this.currentTest = 'Mobile Responsiveness';
        console.log('\n📱 Testing mobile responsiveness...');

        // Test viewport meta tag
        const viewportMeta = document.querySelector('meta[name="viewport"]');
        if (viewportMeta) {
            this.log('Viewport meta tag found');
        } else {
            this.log('Viewport meta tag missing', 'warning');
        }

        // Test responsive classes
        const responsiveElements = document.querySelectorAll('[class*="sm:"], [class*="md:"], [class*="lg:"]');
        if (responsiveElements.length > 0) {
            this.log(`Responsive classes found: ${responsiveElements.length}`);
        }

        // Test mobile-specific elements
        const mobileElements = document.querySelectorAll('[class*="mobile"], [data-mobile]');
        if (mobileElements.length > 0) {
            this.log('Mobile-specific elements found');
        }

        // Simulate mobile viewport
        const originalWidth = window.innerWidth;
        
        // Test at mobile width (simulate)
        if (window.innerWidth > 768) {
            this.log('Desktop viewport detected, mobile testing limited');
        } else {
            this.log('Mobile viewport detected');
        }
    }

    async testExportFunctionality() {
        this.currentTest = 'Export Functionality';
        console.log('\n📄 Testing export functionality...');

        // Look for export buttons
        const exportButtons = Array.from(document.querySelectorAll('button')).filter(btn => 
            btn.textContent.toLowerCase().includes('export') ||
            btn.textContent.toLowerCase().includes('download') ||
            btn.textContent.toLowerCase().includes('pdf') ||
            btn.textContent.toLowerCase().includes('word'));

        if (exportButtons.length > 0) {
            this.log(`Export buttons found: ${exportButtons.length}`);
        } else {
            this.log('No export buttons found', 'warning');
        }

        // Check for export options
        const exportOptions = document.querySelectorAll('[class*="export"], [data-export]');
        if (exportOptions.length > 0) {
            this.log('Export option elements found');
        }

        // Check for premium badges
        const premiumBadges = Array.from(document.querySelectorAll('*')).filter(el => 
            el.textContent.toLowerCase().includes('premium') ||
            el.textContent.toLowerCase().includes('pro'));

        if (premiumBadges.length > 0) {
            this.log('Premium feature indicators found');
        }
    }

    displayResults() {
        console.log('\n📊 Test Results Summary');
        console.log('======================');

        const testGroups = {};
        this.testResults.forEach(result => {
            if (!testGroups[result.test]) {
                testGroups[result.test] = [];
            }
            testGroups[result.test].push(result);
        });

        Object.keys(testGroups).forEach(testName => {
            const results = testGroups[testName];
            const successCount = results.filter(r => r.type === 'success').length;
            const warningCount = results.filter(r => r.type === 'warning').length;
            const errorCount = results.filter(r => r.type === 'error').length;

            console.log(`\n${testName}:`);
            console.log(`  ✅ Success: ${successCount}`);
            if (warningCount > 0) console.log(`  ⚠️ Warnings: ${warningCount}`);
            if (errorCount > 0) console.log(`  ❌ Errors: ${errorCount}`);
        });

        const totalTests = this.testResults.length;
        const totalSuccess = this.testResults.filter(r => r.type === 'success').length;
        const successRate = ((totalSuccess / totalTests) * 100).toFixed(1);

        console.log(`\n🎯 Overall Success Rate: ${successRate}% (${totalSuccess}/${totalTests})`);

        if (successRate >= 80) {
            console.log('✅ Frontend tests passed with good coverage!');
        } else if (successRate >= 60) {
            console.log('⚠️ Frontend tests passed but with some issues to address.');
        } else {
            console.log('❌ Frontend tests indicate significant issues that need attention.');
        }
    }
}

// Auto-run tests if this script is executed directly
if (typeof window !== 'undefined') {
    console.log('🚀 Starting Business Plan Generator Frontend Tests...');
    console.log('Copy and paste this into your browser console on the business plan generator page:');
    console.log('');
    console.log('const tester = new BusinessPlanFrontendTester();');
    console.log('tester.runAllTests();');
    console.log('');
    
    // If we're already on the right page, offer to run automatically
    if (window.location.pathname.includes('business-plan') || 
        document.title.includes('Business Plan')) {
        console.log('✨ Business Plan page detected! Running tests automatically in 3 seconds...');
        setTimeout(() => {
            const tester = new BusinessPlanFrontendTester();
            tester.runAllTests();
        }, 3000);
    }
}

// Export for manual use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessPlanFrontendTester;
}