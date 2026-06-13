const { chromium } = require('playwright');

let browser = null;
let context = null;

/**
 * Check authentication for NotebookLM
 */
async function checkAuth(req, res, next) {
  // Allow health checks without auth
  if (req.path === '/health') {
    return next();
  }
  
  // For now, allow all requests (you can add API key check here)
  next();
}

/**
 * Get or create browser context with authentication
 */
async function getAuthenticatedContext() {
  if (!browser) {
    browser = await chromium.launch({ 
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
  }
  
  // Try to use existing context
  if (context) {
    return context;
  }
  
  // Create new context with cookies from env
  const cookiesJson = process.env.NOTEBOOKLM_COOKIES;
  
  if (cookiesJson) {
    try {
      const cookies = JSON.parse(cookiesJson);
      context = await browser.newContext({ storageState: undefined });
      await context.addCookies(cookies);
      return context;
    } catch (e) {
      console.error('Failed to parse cookies:', e);
    }
  }
  
  // No authenticated context - user needs to authenticate
  return null;
}

/**
 * Close browser
 */
async function closeBrowser() {
  if (browser) {
    await browser.close();
    browser = null;
    context = null;
  }
}

module.exports = { checkAuth, getAuthenticatedContext, closeBrowser };