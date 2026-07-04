const puppeteer = require('puppeteer');

async function testApp() {
    console.log('Starting Puppeteer test on Display 3 (HDMI-1-0)...');
    console.log('Current DISPLAY:', process.env.DISPLAY);
    
    // Display 3 (HDMI-1-0) is at x=1356,y=0 with resolution 1280x800
    // Windsurf is at x=2712, so we'll position Puppeteer at x=1356 to avoid overlap
    const browser = await puppeteer.launch({
        headless: false,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--window-position=1356,0',  // Position to Display 3 coordinates
            '--window-size=1280,800',
            '--force-device-scale-factor=1',
            '--disable-features=VizDisplayCompositor'
        ]
    });

    const page = await browser.newPage();
    
    // Set viewport to match Display 3 resolution
    await page.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to application...');
    
    try {
        // Navigate to the application
        await page.goto('http://localhost/kewer/dashboard.php', {
            waitUntil: 'networkidle2',
            timeout: 30000
        });
        
        console.log('Page loaded successfully');
        
        // Take a screenshot
        await page.screenshot({ path: 'tests/screenshots/dashboard.png' });
        console.log('Screenshot saved');
        
        console.log('Test completed. Browser window should be at x=1356,y=0 (Display 3).');
        console.log('Press Ctrl+C to close the browser and exit.');
        
        // Keep browser open for observation
        await new Promise(resolve => {
            process.on('SIGINT', resolve);
        });
        
    } catch (error) {
        console.error('Error during test:', error);
    } finally {
        await browser.close();
        console.log('Browser closed');
    }
}

testApp().catch(console.error);
