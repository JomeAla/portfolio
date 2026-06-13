<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Guardrails Manager
 * 
 * Loads and enforces the WRITING GUARDRAILS from your file.
 * This service filters all AI-generated content to remove
 * banned words, phrases, and patterns.
 * 
 * @package App\Services
 */
class GuardrailsManager
{
    /**
     * The loaded guardrails data
     */
    protected array $guardrails = [];
    
    /**
     * Banned phrases and patterns
     */
    protected array $bannedPhrases = [];
    
    /**
     * Banned words
     */
    protected array $bannedWords = [];
    
    /**
     * Banned sentence patterns
     */
    protected array $bannedPatterns = [];
    
    /**
     * Whether guardrails are loaded
     */
    protected bool $isLoaded = false;
    
    /**
     * Create a new GuardrailsManager instance
     */
    public function __construct()
    {
        $this->loadGuardrails();
    }
    
    /**
     * Load guardrails from the configuration file
     */
    protected function loadGuardrails(): void
    {
        // Try Documents folder first, then config, then resources
        $possiblePaths = [
            'C:\Users\jomea\Documents\MY-WRITING-GUARDRAILS.md',
            resource_path('guardrails/MY-WRITING-GUARDRAILS.md'),
            base_path('../config/notebooklm/MY-WRITING-GUARDRAILS.md'),
        ];
        
        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }
        
        if (!$filePath) {
            Log::warning("Guardrails file not found in any location");
            return;
        }
        
        $content = file_get_contents($filePath);
        
        $this->parseGuardrails($content);
        $this->isLoaded = true;
        
        Log::info("Guardrails loaded successfully from {$filePath}");
    }
    
    /**
     * Parse the guardrails markdown file
     */
    protected function parseGuardrails(string $content): void
    {
        // Section 1: High-Alarm Verbs
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 1a. High-Alarm Verbs')
        );
        
        // Section 1b: High-Alarm Adjectives
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 1b. High-Alarm Adjectives')
        );
        
        // Section 1c: High-Alarm Nouns
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 1c. High-Alarm Nouns')
        );
        
        // Section 1d: High-Alarm Adverbs
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 1d. High-Alarm Adverbs')
        );
        
        // Section 2a: Throat-Clearing Openers
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2a. Throat-Clearing Openers')
        );
        
        // Section 2b: Pedagogical Openers
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2b. Pedagogical Openers (The "Helpful Bot" Voice)')
        );
        
        // Section 2c: Emphasis phrases
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2c. Emphasis and Signposting Phrases')
        );
        
        // Section 2d: Hedging phrases
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2d. Hedging and Qualifier Phrases')
        );
        
        // Section 2e: Fake-Suspense
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2e. Fake-Suspense Transitions')
        );
        
        // Section 2f: Hype phrases
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2f. Hype and Excitement Phrases')
        );
        
        // Section 2g: Conclusion phrases
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2g. Conclusion Phrases')
        );
        
        // Section 2h: Closers
        $this->bannedPhrases = array_merge(
            $this->bannedPhrases,
            $this->extractList($content, '### 2i. Other Banned Filler')
        );
        
        // Extract individual words (single word bans)
        $this->bannedWords = $this->extractSingleWords($content);
    }
    
    /**
     * Extract a list from the content between headers
     */
    protected function extractList(string $content, string $sectionHeader): array
    {
        $items = [];
        
        // Find the section
        $pattern = '/' . preg_quote($sectionHeader, '/') . '(.*?)(?=== {2,}|##|$)/s';
        if (preg_match($pattern, $content, $matches)) {
            $section = $matches[1];
            
            // Extract each line that starts with a dash or bullet
            preg_match_all('/^[-\s]+(.+)$/m', $section, $lines);
            
            if (!empty($lines[1])) {
                foreach ($lines[1] as $line) {
                    $phrase = trim($line);
                    if (!empty($phrase)) {
                        $items[] = $phrase;
                    }
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Extract single words to ban
     */
    protected function extractSingleWords(string $content): array
    {
        // Extract common banned single words from context
        return [];
    }
    
    /**
     * Filter content to remove all banned phrases
     */
    public function filter(string $content): string
    {
        if (!$this->isLoaded) {
            $this->loadGuardrails();
        }
        
        $filtered = $content;
        
        // Filter each banned phrase
        foreach ($this->bannedPhrases as $phrase) {
            if (empty(trim($phrase))) continue;
            
            // Case-insensitive replacement
            $filtered = preg_replace(
                '/' . preg_quote(trim($phrase), '/') . '/ui',
                '[FILTERED]',
                $filtered
            );
        }
        
        // Remove multiple spaces
        $filtered = preg_replace('/\s+/', ' ', $filtered);
        
        return $filtered;
    }
    
    /**
     * Check if content contains violations
     */
    public function hasViolations(string $content): array
    {
        if (!$this->isLoaded) {
            $this->loadGuardrails();
        }
        
        $violations = [];
        
        foreach ($this->bannedPhrases as $phrase) {
            if (empty(trim($phrase))) continue;
            
            if (preg_match('/' . preg_quote(trim($phrase), '/') . '/ui', $content)) {
                $violations[] = $phrase;
            }
        }
        
        return $violations;
    }
    
    /**
     * Get strict prompt section for AI requests
     */
    public function getStrictPrompt(): string
    {
        if (!$this->isLoaded) {
            $this->loadGuardrails();
        }
        
        $prompt = "IMPORTANT: You must遵守 these strict rules:\n\n";
        
        $prompt .= "NEVER use these phrases/words (NOTHING stops this):\n";
        $prompt .= "- " . implode("\n- ", array_slice($this->bannedPhrases, 0, 30)) . "\n";
        
        $prompt .= "\n\nAlso NEVER use:\n";
        $prompt .= "- Em dashes (use commas instead)\n";
        $prompt .= "- Semicolons (unless explicitly asked)\n";
        $prompt .= "- Oxford commas (unless explicitly asked)\n";
        $prompt .= "- 'Not X, it's Y' pattern\n";
        $prompt .= "- Questions that you then immediately answer\n";
        $prompt .= "- 'In conclusion', 'To summarize', 'At the end of the day'\n";
        $prompt .= "- 'Here is a comprehensive...'\n";
        $prompt .= "- Self-referential AI mentions\n";
        
        return $prompt;
    }
    
    /**
     * Get the complete guardrails context for prompts
     */
    public function getContext(): array
    {
        return [
            'banned_phrases' => $this->bannedPhrases,
            'banned_words' => $this->bannedWords,
            'strict_prompt' => $this->getStrictPrompt(),
            'is_loaded' => $this->isLoaded,
        ];
    }
    
    /**
     * Validate content and return issues
     */
    public function validate(string $content): array
    {
        $violations = $this->hasViolations($content);
        
        return [
            'is_valid' => empty($violations),
            'violations_found' => count($violations),
            'violations' => $violations,
            'needs_regeneration' => !empty($violations),
        ];
    }
}