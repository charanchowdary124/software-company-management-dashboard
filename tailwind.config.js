/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        sams: {
          50: '#f0f7ff',
          100: '#e0effe',
          200: '#bae0fd',
          300: '#7ccbfa',
          400: '#36b2f5',
          500: '#0c98e9',
          600: '#0079cc',
          700: '#0160a5',
          800: '#065287',
          900: '#0b446e',
          950: '#072b4a',
        },
        surface: {
          50: '#fafbfc',
          100: '#f4f6f8',
          200: '#e8ecf0',
          300: '#d3dae0',
          400: '#a8b5c2',
          500: '#7d8e9e',
          600: '#5a6b7c',
          700: '#434f5d',
          800: '#2c3540',
          900: '#1a2029',
          950: '#11161c',
        },
        premium: {
          dark: '#0f172a',
          darker: '#020617',
          purple: '#9333ea',
          cyan: '#06b6d4',
          pink: '#ec4899',
          teal: '#14b8a6',
          indigo: '#6366f1',
        },
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.4s ease-out',
        'slide-right': 'slideRight 0.3s ease-out',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'float': 'float 6s ease-in-out infinite',
        'glow-pulse': 'glowPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'shimmer': 'shimmer 2s infinite',
        'count-up': 'countUp 1s ease-out',
        'spin-slow': 'spin 20s linear infinite',
        'bounce-gentle': 'bounceGentle 2s ease-in-out infinite',
        'scale-in': 'scaleIn 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(12px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideRight: {
          '0%': { opacity: '0', transform: 'translateX(-12px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-12px)' },
        },
        glowPulse: {
          '0%, 100%': { opacity: '0.6' },
          '50%': { opacity: '1' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-1000px 0' },
          '100%': { backgroundPosition: '1000px 0' },
        },
        countUp: {
          '0%': { opacity: '0', transform: 'scale(0.8)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        bounceGentle: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-8px)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
      backdropBlur: {
        xs: '2px',
        sm: '4px',
        md: '8px',
        lg: '12px',
        xl: '16px',
        '2xl': '20px',
      },
      boxShadow: {
        'glow-purple': '0 0 20px rgba(147, 51, 234, 0.3)',
        'glow-cyan': '0 0 20px rgba(6, 182, 212, 0.3)',
        'glow-pink': '0 0 20px rgba(236, 72, 153, 0.3)',
        'glow-teal': '0 0 20px rgba(20, 184, 166, 0.3)',
        'glass-sm': '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
        'glass-md': '0 8px 24px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
        'glass-lg': '0 12px 32px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
      },
    },
  },
  plugins: [],
};
