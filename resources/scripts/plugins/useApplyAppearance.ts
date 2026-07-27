import { useEffect } from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { AppearanceSettings, FontScale, ThemeMode } from '@/state/appearance';

const FONT_SCALE_MAP: Record<FontScale, string> = {
    sm: '0.9',
    md: '1',
    lg: '1.1',
};

const resolveTheme = (theme: ThemeMode): 'dark' | 'light' => {
    if (theme !== 'auto') return theme;
    if (typeof window !== 'undefined' && window.matchMedia) {
        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }
    return 'dark';
};

export const applyAppearance = (data: AppearanceSettings): void => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;

    root.setAttribute('data-theme', resolveTheme(data.theme));
    root.setAttribute('data-density', data.density);
    root.setAttribute('data-sidebar', data.sidebarStyle);
    root.setAttribute('data-contrast', data.highContrast ? 'high' : 'normal');
    root.style.setProperty('--font-scale', FONT_SCALE_MAP[data.fontScale] ?? '1');
    root.style.setProperty('--color-accent', '#4F7CFF');
    root.style.setProperty('--color-accent-hover', '#4F7CFF');
};

export default (): void => {
    const data = useStoreState((state: ApplicationStore) => state.appearance.data);

    useEffect(() => {
        applyAppearance(data);
    }, [data]);

    useEffect(() => {
        if (data.theme !== 'auto' || typeof window === 'undefined' || !window.matchMedia) {
            return;
        }
        const mq = window.matchMedia('(prefers-color-scheme: light)');
        const handler = () => applyAppearance(data);
        if (mq.addEventListener) {
            mq.addEventListener('change', handler);
            return () => mq.removeEventListener('change', handler);
        }
        mq.addListener(handler);
        return () => mq.removeListener(handler);
    }, [data]);
};
