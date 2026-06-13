<?php

/**
 * NotebookLM AI Configuration
 * 
 * This file configures how AI-generated content interacts with your
 * GUARDRAILS and DNA BIBLE files to ensure all content follows 
 * your strict writing guidelines.
 * 
 * @package App\config
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    
    'api' => [
        // Bridge service URL (to be deployed)
        'bridge_url' => env('NOTEBOOKLM_BRIDGE_URL', 'https://notebooklm-bridge.onrender.com'),
        
        // API key for bridge service authentication
        'api_key' => env('NOTEBOOKLM_API_KEY', ''),
        
        // Request timeout in seconds
        'timeout' => env('NOTEBOOKLM_TIMEOUT', 120),
        
        // Retry attempts on failure
        'retry_attempts' => 3,
        
        // Retry delay in seconds
        'retry_delay' => 2,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Guardrails Configuration
    |--------------------------------------------------------------------------
    */
    
    'guardrails' => [
        // Path to your GUARDRAILS file
        'file_path' => resource_path('guardrails/MY-WRITING-GUARDRAILS.md'),
        
        // Enable strict enforcement
        'strict_mode' => true,
        
        // Auto-filter banned phrases before returning
        'auto_filter' => true,
        
        // Block content if violations found (vs warn)
        'hard_block' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | DNA Bible Configuration
    |--------------------------------------------------------------------------
    */
    
    'dna_bible' => [
        // Path to your DNA BIBLE file
        'file_path' => resource_path('guardrails/MY-TEXT-DNA-BIBLE.md'),
        
        // Apply voice/style rules
        'apply_voice_rules' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Content Generation Settings
    |--------------------------------------------------------------------------
    */
    
    'generation' => [
        // Default model to use
        'model' => 'gemini-2.0-flash',
        
        // Temperature for creativity (0 = strict, 1 = creative)
        'temperature' => 0.7,
        
        // Maximum tokens per generation
        'max_tokens' => 4096,
        
        // Number of retries for content regeneration
        'max_retries' => 3,
        
        // Include guardrails in prompt
        'include_guardrails' => true,
        
        // Include DNA bible in prompt
        'include_dna_bible' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Account Rotation Settings
    |--------------------------------------------------------------------------
    */
    
    'rotation' => [
        // Enable account rotation
        'enabled' => true,
        
        // Maximum queries per account per day
        'max_daily_queries' => 50,
        
        // Accounts to rotate between
        'accounts' => [
            [
                'email' => env('NOTEBOOKLM_ACCOUNT_1', 'jomealawuru@gmail.com'),
                'cookie_auth' => env('NOTEBOOKLM_COOKIE_1', ''),
                'daily_used' => 0,
                'last_reset' => now()->toDateString(),
            ],
            // Add more accounts as needed
        ],
        
        // Auto-switch when limit reached
        'auto_switch' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    */
    
    'content_types' => [
        'blog_post' => [
            'max_length' => 2000,
            'min_length' => 500,
            'default_structure' => 'introduction,body,conclusion',
        ],
        
        'tweet' => [
            'max_length' => 280,
            'min_length' => 50,
            'count' => 5, // Generate 5 tweets per blog
        ],
        
        'email_sequence' => [
            'max_length' => 500,
            'min_length' => 100,
            'subject_max_length' => 60,
        ],
        
        'landing_page' => [
            'max_length' => 1000,
            'min_length' => 200,
        ],
        
        'social_post' => [
            'max_length' => 500,
            'platforms' => ['twitter', 'linkedin', 'facebook'],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Caching Settings
    |--------------------------------------------------------------------------
    */
    
    'cache' => [
        // Enable prompt caching
        'enabled' => true,
        
        // Cache duration in minutes
        'duration' => 60 * 24, // 24 hours
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    
    'logging' => [
        // Log all generation requests
        'log_requests' => true,
        
        // Log content violations
        'log_violations' => true,
        
        // Log account rotations
        'log_rotations' => true,
    ],
];