import tw from 'twin.macro';
import { createGlobalStyle } from 'styled-components/macro';
// @ts-expect-error untyped font file
import font from '@fontsource-variable/ibm-plex-sans/files/ibm-plex-sans-latin-wght-normal.woff2';

export default createGlobalStyle`
    @font-face {
        font-family: 'IBM Plex Sans';
        font-style: normal;
        font-display: swap;
        font-weight: 100 700;
        src: url(${font}) format('woff2-variations');
        unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
    }

    body {
        ${tw`font-sans`};
        background-color: var(--background-color);
        color: var(--text-main);
        letter-spacing: 0;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-medium tracking-normal font-header`};
        color: var(--text-main);
    }

    p {
        color: var(--text-secondary);
        ${tw`leading-snug font-sans`};
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button {
        ${tw`outline-none`};
    }

    :where(a, button, input, select, textarea, [tabindex]):focus-visible {
        outline: 2px solid var(--color-accent);
        outline-offset: 3px;
        box-shadow: none;
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    /* Scroll Bar Style */
    ::-webkit-scrollbar {
        background: none;
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--scrollbar-thumb, #5b6570);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--scrollbar-thumb, #5b6570);
    }

    ::-webkit-scrollbar-track-piece {
        margin: 4px 0;
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }

    /* Selection */
    ::selection {
        background: var(--color-surface-hover);
        color: var(--text-main);
    }

    code, kbd, samp, pre, .font-mono {
        font-variant-ligatures: none;
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: 0.01ms !important;
        }
    }
`;
