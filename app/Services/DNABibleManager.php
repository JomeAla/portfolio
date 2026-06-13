<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * DNA Bible Manager
 * 
 * Loads and applies your TEXT DNA BIBLE rules - your voice, style,
 * and personality preferences for all AI-generated content.
 * 
 * @package App\Services
 */
class DNABibleManager
{
    /**
     * The DNA Bible configuration
     */
    protected array $dnaBible = [];
    
    /**
     * Whether DNA Bible is loaded
     */
    protected bool $isLoaded = false;
    
    /**
     * Voice and style rules
     */
    protected array $voiceRules = [];
    
    /**
     * Create a new DNABibleManager instance
     */
    public function __construct()
    {
        $this->loadDNABible();
    }
    
    /**
     * Load DNA Bible from configuration file
     */
    protected function loadDNABible(): void
    {
        // Try Documents folder first, then config, then resources
        $possiblePaths = [
            'C:\Users\jomea\Documents\MY-TEXT-DNA-BIBLE.md',
            resource_path('guardrails/MY-TEXT-DNA-BIBLE.md'),
            base_path('../config/notebooklm/MY-TEXT-DNA-BIBLE.md'),
        ];
        
        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }
        
        if (!$filePath) {
            Log::warning("DNA Bible file not found in any location");
            return;
        }
        
        $content = file_get_contents($filePath);
        
        $this->parseDNABible($content);
        $this->isLoaded = true;
        
        Log::info("DNA Bible loaded successfully from {$filePath}");
    }
    
    /**
     * Parse the DNA Bible markdown file
     */
    protected function parseDNABible(string $content): void
    {
        // Extract each section's starter default
        $this->voiceRules = [
            'talk_vs_write' => $this->extractSection($content, '## 1. HOW I TALK VS. HOW I WRITE'),
            'sentence_rhythm' => $this->extractSection($content, '## 2. MY NATURAL SENTENCE RHYTHM'),
            'words_i_use' => $this->extractSection($content, '## 3. WORDS AND PHRASES I ACTUALLY USE'),
            'words_i_never_use' => $this->extractSection($content, '## 4. WORDS AND PHRASES I NEVER USE'),
            'disagreement' => $this->extractSection($content, '## 5. HOW I HANDLE DISAGREEMENT AND CRITICISM'),
            'humor_style' => $this->extractSection($content, '## 6. MY HUMOR STYLE'),
            'annoying_to_read' => $this->extractSection($content, '## 7. WHAT I FIND ANNOYING TO READ'),
            'open_close' => $this->extractSection($content, '## 8. HOW I OPEN AND CLOSE THINGS'),
            'formatting' => $this->extractSection($content, '## 9. FORMATTING PREFERENCES'),
            'content_philosophy' => $this->extractSection($content, '## 10. CONTENT PHILOSOPHY'),
        ];
        
        $this->dnaBible = [
            'voice_rules' => $this->voiceRules,
            'is_loaded' => true,
        ];
    }
    
    /**
     * Extract a section's STARTER DEFAULT
     */
    protected function extractSection(string $content, string $sectionHeader): string
    {
        $pattern = '/' . preg_quote($sectionHeader, '/') . '(.*?)(?=## |$)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $section = $matches[1];
            
            // Get the STARTER DEFAULT content
            if (preg_match('/STARTER DEFAULT:(.*?)(YOUR VERSION:|$)/s', $section, $defaultMatch)) {
                return trim($defaultMatch[1]);
            }
        }
        
        return '';
    }
    
    /**
     * Get the voice prompt section for AI requests
     */
    public function getVoicePrompt(): string
    {
        if (!$this->isLoaded) {
            $this->loadDNABible();
        }
        
        $prompt = "YOUR WRITING STYLE (DNA BIBLE - Follow these rules):\n\n";
        
        // Section 1: How I talk vs write
        if (!empty($this->voiceRules['talk_vs_write'])) {
            $prompt .= "TONE: {$this->voiceRules['talk_vs_write']}\n\n";
        }
        
        // Section 2: Sentence rhythm
        if (!empty($this->voiceRules['sentence_rhythm'])) {
            $prompt .= "RHYTHM: {$this->voiceRules['sentence_rhythm']}\n\n";
        }
        
        // Section 3: Words I use
        if (!empty($this->voiceRules['words_i_use'])) {
            $prompt .= "PREFERRED WORDS: {$this->voiceRules['words_i_use']}\n\n";
        }
        
        // Section 4: Words I never use
        if (!empty($this->voiceRules['words_i_never_use'])) {
            $prompt .= "NEVER USE THESE: {$this->voiceRules['words_i_never_use']}\n\n";
        }
        
        // Section 5: How I handle disagreement
        if (!empty($this->voiceRules['disagreement'])) {
            $prompt .= "CRITICISM STYLE: {$this->voiceRules['disagreement']}\n\n";
        }
        
        // Section 6: Humor style
        if (!empty($this->voiceRules['humor_style'])) {
            $prompt .= "HUMOR: {$this->voiceRules['humor_style']}\n\n";
        }
        
        // Section 7: What I find annoying
        if (!empty($this->voiceRules['annoying_to_read'])) {
            $prompt .= "AVOID READING: {$this->voiceRules['annoying_to_read']}\n\n";
        }
        
        // Section 8: How I open and close
        if (!empty($this->voiceRules['open_close'])) {
            $prompt .= "OPENINGS/CLOSINGS: {$this->voiceRules['open_close']}\n\n";
        }
        
        // Section 9: Formatting
        if (!empty($this->voiceRules['formatting'])) {
            $prompt .= "FORMATTING: {$this->voiceRules['formatting']}\n\n";
        }
        
        // Section 10: Content philosophy
        if (!empty($this->voiceRules['content_philosophy'])) {
            $prompt .= "PHILOSOPHY: {$this->voiceRules['content_philosophy']}\n\n";
        }
        
        $prompt .= "GENERAL REMINDERS:\n";
        $prompt .= "- Use short sentences (most under 18 words)\n";
        $prompt .= "- Vary sentence length naturally\n";
        $prompt .= "- Sound like a smart friend, not a robot\n";
        $prompt .= "- Be direct, cut the fluff\n";
        $prompt .= "- Have an opinion\n";
        $prompt .= "- Use contractions naturally\n";
        $prompt .= "- Never say 'I hope this helps' or similar closers\n";
        
        return $prompt;
    }
    
    /**
     * Get the complete DNA Bible context
     */
    public function getContext(): array
    {
        return [
            'voice_rules' => $this->voiceRules,
            'voice_prompt' => $this->getVoicePrompt(),
            'is_loaded' => $this->isLoaded,
        ];
    }
    
    /**
     * Apply voice rules to content
     */
    public function applyToContent(string $content): string
    {
        // Apply formatting preferences
        $processed = $content;
        
        // Remove em dashes (replace with commas)
        $processed = str_replace('—', ',', $processed);
        $processed = str_replace(' -- ', ', ', $processed);
        
        // Clean up multiple spaces
        $processed = preg_replace('/\s+/', ' ', $processed);
        
        return $processed;
    }
}