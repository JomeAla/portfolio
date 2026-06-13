const express = require('express');
const router = express.Router();
const { generateContent, chatWithNotebookLM } = require('../services/notebooklm');
const { checkAuth } = require('../services/auth');

// POST /api/v1/generate
router.post('/generate', checkAuth, async (req, res) => {
  try {
    const { prompt, type = 'general' } = req.body;
    
    if (!prompt) {
      return res.status(400).json({ 
        success: false, 
        error: 'Prompt is required' 
      });
    }
    
    const result = await generateContent(prompt, type);
    
    res.json(result);
  } catch (error) {
    console.error('Generate error:', error);
    res.status(500).json({ 
      success: false, 
      error: error.message 
    });
  }
});

// POST /api/v1/chat
router.post('/chat', checkAuth, async (req, res) => {
  try {
    const { message, context = '' } = req.body;
    
    if (!message) {
      return res.status(400).json({ 
        success: false, 
        error: 'Message is required' 
      });
    }
    
    const result = await chatWithNotebookLM(message, context);
    
    res.json(result);
  } catch (error) {
    console.error('Chat error:', error);
    res.status(500).json({ 
      success: false, 
      error: error.message 
    });
  }
});

module.exports = router;