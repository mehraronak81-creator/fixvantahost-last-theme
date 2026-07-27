const lockedScale = (value) => ({
    50: value,
    100: value,
    200: value,
    300: value,
    400: value,
    500: value,
    600: value,
    700: value,
    800: value,
    900: value,
});

const neutral = {
    50: '#E7E9EC',
    100: '#E7E9EC',
    200: '#E7E9EC',
    300: '#8A93A0',
    400: '#8A93A0',
    500: '#5B6570',
    600: '#262B31',
    700: '#1B1F24',
    800: '#14171B',
    900: '#0B0D10',
};

module.exports = {
    content: ['./resources/scripts/**/*.{js,ts,tsx}'],
    theme: {
        colors: {
            inherit: 'inherit',
            current: 'currentColor',
            transparent: 'transparent',
            black: '#0B0D10',
            white: '#E7E9EC',
            gray: neutral,
            neutral,
            primary: lockedScale('#5B6570'),
            blue: lockedScale('#5B6570'),
            cyan: lockedScale('#5B6570'),
            orange: lockedScale('#8A93A0'),
            red: lockedScale('#F0575D'),
            green: lockedScale('#3ECF8E'),
            yellow: lockedScale('#E8A33D'),
            accent: lockedScale('#4F7CFF'),
        },
        extend: {
            fontFamily: {
                header: ['IBM Plex Sans', 'system-ui', 'sans-serif'],
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: (theme) => ({
                default: theme('colors.neutral.600', 'currentColor'),
            }),
            borderRadius: {
                xl: '10px',
                '2xl': '10px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ],
};
