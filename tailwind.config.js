/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Foundation/Console/Resources/**/*.js",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Geist', 'Satoshi', 'Cabinet Grotesk', 'system-ui', 'sans-serif'],
        body: ['Geist', 'Satoshi', 'system-ui', 'sans-serif'],
        display: ['Cabinet Grotesk', 'Satoshi', 'Geist', 'system-ui', 'sans-serif'],
      },
      colors: {
        primary: 'var(--primary-color, #0f172a)',
        accent: 'var(--accent-color, #3b82f6)',
      },
      animation: {
        'float': 'float 6s ease-in-out infinite',
        'shimmer': 'shimmer 2s linear infinite',
        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
      },
    },
  },
  plugins: [],
}