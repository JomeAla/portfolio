<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * NotebookLM Content Generation Service
 * 
 * This service generates AI content for your marketing module while
 * strictly following your GUARDRAILS and DNA BIBLE files.
 * 
 * Features:
 * - Blog post generation
 * - Tweet generation
 * - Email sequence generation
 * - Landing page content
 * - Content repurposing
 * - AI Chatbot
 * 
 * @package App\Services
 */
class NotebookLMService
{
    /**
     * Guardrails manager instance
     */
    protected GuardrailsManager $guardrails;
    
    /**
     * DNA Bible manager instance
     */
    protected DNABibleManager $dnaBible;
    
    /**
     * API configuration
     */
    protected array $config;
    
    /**
     * Retry attempts
     */
    protected int $maxRetries = 3;
    
    /**
     * Create a new NotebookLMService instance
     */
    public function __construct(
        GuardrailsManager $guardrails,
        DNABibleManager $dnaBible
    ) {
        $this->guardrails = $guardrails;
        $this->dnaBible = $dnaBible;
        $this->config = config('notebooklm');
    }
    
    /**
     * Generate a blog post
     */
    public function generateBlogPost(string $topic, array $options = []): array
    {
        $contentType = $this->config['content_types']['blog_post'] ?? [];
        
        $prompt = $this->buildPrompt([
            'task' => 'blog_post',
            'topic' => $topic,
            'max_length' => $options['max_length'] ?? $contentType['max_length'] ?? 2000,
            'min_length' => $options['min_length'] ?? $contentType['min_length'] ?? 500,
            'title' => $options['title'] ?? '',
        ]);
        
        $result = $this->callNotebookLM($prompt, 'blog');
        
        if ($result['success']) {
            // Validate against guardrails
            $validation = $this->guardrails->validate($result['content']);
            
            if (!$validation['is_valid']) {
                Log::warning("Blog post generated with violations, retrying...", [
                    'violations' => $validation['violations']
                ]);
                
                // Retry once
                return $this->generateBlogPost($topic, $options);
            }
        }
        
        return $result;
    }
    
    /**
     * Generate tweets from a blog post
     */
    public function generateTweets(string $blogContent, int $count = 5): array
    {
        $contentType = $this->config['content_types']['tweet'] ?? [];
        
        $prompt = $this->buildPrompt([
            'task' => 'tweets',
            'content' => $blogContent,
            'count' => $count,
            'max_length' => $contentType['max_length'] ?? 280,
        ]);
        
        $result = $this->callNotebookLM($prompt, 'tweets');
        
        if ($result['success']) {
            // Split into individual tweets
            $tweets = $this->parseTweets($result['content'], $count);
            
            // Validate each tweet
            $validatedTweets = [];
            foreach ($tweets as $tweet) {
                $validation = $this->guardrails->validate($tweet);
                
                if ($validation['is_valid']) {
                    $validatedTweets[] = [
                        'content' => $tweet,
                        'valid' => true,
                    ];
                } else {
                    $validatedTweets[] = [
                        'content' => $this->guardrails->filter($tweet),
                        'valid' => false,
                        'violations' => $validation['violations'],
                    ];
                }
            }
            
            return [
                'success' => true,
                'tweets' => $validatedTweets,
            ];
        }
        
        return $result;
    }
    
    /**
     * Generate email sequence
     */
    public function generateEmailSequence(string $sequenceName, int $steps): array
    {
        $contentType = $this->config['content_types']['email_sequence'] ?? [];
        
        $prompt = $this->buildPrompt([
            'task' => 'email_sequence',
            'sequence_name' => $sequenceName,
            'steps' => $steps,
            'max_length' => $contentType['max_length'] ?? 500,
        ]);
        
        $result = $this->callNotebookLM($prompt, 'emails');
        
        if ($result['success']) {
            // Parse into individual emails
            $emails = $this->parseEmails($result['content'], $steps);
            
            return [
                'success' => true,
                'emails' => $emails,
            ];
        }
        
        return $result;
    }
    
    /**
     * Generate landing page content
     */
    public function generateLandingPage(string $offer, array $sections): array
    {
        $contentType = $this->config['content_types']['landing_page'] ?? [];
        
        $prompt = $this->buildPrompt([
            'task' => 'landing_page',
            'offer' => $offer,
            'sections' => implode(', ', $sections),
            'max_length' => $contentType['max_length'] ?? 1000,
        ]);
        
        $result = $this->callNotebookLM($prompt, 'content');
        
        return $result;
    }
    
    /**
     * Generate social media posts from content
     */
    public function generateSocialPosts(string $content, array $platforms = ['twitter', 'linkedin']): array
    {
        $prompt = $this->buildPrompt([
            'task' => 'social_posts',
            'content' => $content,
            'platforms' => implode(', ', $platforms),
        ]);
        
        $result = $this->callNotebookLM($prompt, 'posts');
        
        if ($result['success']) {
            return [
                'success' => true,
                'posts' => $this->parseSocialPosts($result['content'], $platforms),
            ];
        }
        
        return $result;
    }
    
    /**
     * AI Chat (Chatbot for leads)
     */
    public function chat(string $message, string $context = ''): array
    {
        $prompt = $this->buildPrompt([
            'task' => 'chat',
            'message' => $message,
            'context' => $context,
        ]);
        
        return $this->callNotebookLM($prompt, 'chat');
    }
    
    /**
     * Build the comprehensive prompt with guardrails and DNA bible
     */
    protected function buildPrompt(array $params): string
    {
        // Get strict rules from guardrails
        $guardrailsPrompt = $this->guardrails->getStrictPrompt();
        
        // Get voice/style from DNA Bible
        $voicePrompt = $this->dnaBible->getVoicePrompt();
        
        // Build the full prompt
        $prompt = "You are writing content for my marketing. Follow these rules EXACTLY:\n\n";
        
        // Add guardrails (strict rules)
        $prompt .= $guardrailsPrompt . "\n\n";
        
        // Add DNA Bible (voice/style)
        $prompt .= $voicePrompt . "\n\n";
        
        // Add task-specific instructions
        $prompt .= "TASK: ";
        
        switch ($params['task']) {
            case 'blog_post':
                $prompt .= "Write a blog post about: {$params['topic']}\n";
                $prompt .= "Title: {$params['title']}\n";
                $prompt .= "Length: {$params['min_length']}-{$params['max_length']} words\n";
                break;
                
            case 'tweets':
                $prompt .= "Create {$params['count']} tweets from this content.\n";
                $prompt .= "Each tweet max {$params['max_length']} characters.\n";
                $prompt .= "Content:\n{$params['content']}\n";
                break;
                
            case 'email_sequence':
                $prompt .= "Create a {$params['steps']}-email sequence for: {$params['sequence_name']}\n";
                $prompt .= "Each email max {$params['max_length']} characters.\n";
                break;
                
            case 'landing_page':
                $prompt .= "Write landing page content for: {$params['offer']}\n";
                $prompt .= "Include these sections: {$params['sections']}\n";
                break;
                
            case 'social_posts':
                $prompt .= "Create social posts for: {$params['platforms']}\n";
                $prompt .= "From content:\n{$params['content']}\n";
                break;
                
            case 'chat':
                $prompt .= "Respond to this message as my marketing assistant.\n";
                if (!empty($params['context'])) {
                    $prompt .= "Context: {$params['context']}\n";
                }
                $prompt .= "Message: {$params['message']}\n";
                break;
        }
        
        $prompt .= "\n\nIMPORTANT: Do not use any banned phrases from above. Write naturally like a human.";
        
        return $prompt;
    }
    
    /**
     * Call the NotebookLM Bridge API
     */
    protected function callNotebookLM(string $prompt, string $type): array
    {
        $apiConfig = $this->config['api'];
        $bridgeUrl = $apiConfig['bridge_url'];
        $timeout = $apiConfig['timeout'] ?? 120;
        
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::timeout($timeout)
                    ->post("{$bridgeUrl}/api/v1/generate", [
                        'prompt' => $prompt,
                        'type' => $type,
                    ])
                    ->json();
                
                if (!empty($response['success']) && !empty($response['content'])) {
                    return [
                        'success' => true,
                        'content' => $response['content'],
                        'usage' => $response['usage'] ?? null,
                    ];
                }
                
                Log::warning("NotebookLM API returned unsuccessful response", $response);
                
            } catch (\Exception $e) {
                Log::error("NotebookLM API error (attempt {$attempt}): " . $e->getMessage());
                
                // Wait before retry
                sleep(2);
            }
        }
        
        return [
            'success' => false,
            'error' => 'Failed to generate content after ' . $this->maxRetries . ' attempts',
        ];
    }
    
    /**
     * Parse tweets from response
     */
    protected function parseTweets(string $content, int $count): array
    {
        $tweets = [];
        
        // Split by numbered list or newlines
        $lines = preg_split('/\n\d+[\.\)]\s*/', $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 20 && strlen($line) <= 280) {
                $tweets[] = $line;
            }
        }
        
        // If parsing failed, split by double newlines
        if (empty($tweets)) {
            $tweets = array_filter(array_map('trim', explode("\n\n", $content)));
        }
        
        return array_slice($tweets, 0, $count);
    }
    
    /**
     * Parse emails from response
     */
    protected function parseEmails(string $content, int $steps): array
    {
        $emails = [];
        
        // Split by numbered emails
        $sections = preg_split('/\d+[\.\)]\s*(?:Email|Step)/i', $content);
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (strlen($section) > 50) {
                // Try to extract subject line
                $lines = explode("\n", $section);
                $subject = array_shift($lines);
                $body = implode("\n", $lines);
                
                $emails[] = [
                    'subject' => trim($subject),
                    'body' => trim($body),
                ];
            }
        }
        
        return array_slice($emails, 0, $steps);
    }
    
    /**
     * Parse social posts from response
     */
    protected function parseSocialPosts(string $content, array $platforms): array
    {
        $posts = [];
        
        foreach ($platforms as $platform) {
            $pattern = '/(?:' . $platform . '|' . ucfirst($platform) . ')(.*?)(?=' . ucfirst($platform) . '|$)/is';
            
            if (preg_match($pattern, $content, $matches)) {
                $posts[$platform] = trim($matches[1]);
            }
        }
        
        return $posts;
    }
    
    /**
     * Check account quota and rotate if needed
     */
    protected function checkAndRotateAccount(): void
    {
        if (!$this->config['rotation']['enabled'] ?? false) {
            return;
        }
        
        $accounts = $this->config['rotation']['accounts'] ?? [];
        $maxQueries = $this->config['rotation']['max_daily_queries'] ?? 50;
        
        $today = now()->toDateString();
        
        foreach ($accounts as &$account) {
            // Reset if new day
            if ($account['last_reset'] !== $today) {
                $account['daily_used'] = 0;
                $account['last_reset'] = $today;
            }
            
            // Use this account if under limit
            if ($account['daily_used'] < $maxQueries) {
                return;
            }
        }
        
        // All accounts over limit - log warning
        Log::warning("All NotebookLM accounts at daily limit");
    }
}