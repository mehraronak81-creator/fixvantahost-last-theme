import React from 'react';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock from '@/components/elements/PageContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Switch from '@/components/elements/Switch';
import { Button } from '@/components/elements/button/index';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { Density, FontScale, SidebarStyle, ThemeMode } from '@/state/appearance';
import { faAdjust, faColumns, faFont, faMoon, faSun } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { IconProp } from '@fortawesome/fontawesome-svg-core';

const OptionRow = styled.div`
    ${tw`flex flex-wrap gap-2`};
`;

const OptionButton = styled.button<{ selected?: boolean }>`
    ${tw`flex items-center gap-2 px-4 py-2 rounded text-sm cursor-pointer transition-all duration-150`};
    border: 1px solid var(--color-border);
    background: ${(props) => (props.selected ? 'var(--color-surface-hover)' : 'var(--color-surface)')};
    color: ${(props) => (props.selected ? 'var(--text-main)' : 'var(--text-secondary)')};

    &:hover {
        border-color: var(--color-border);
        color: var(--text-main);
    }
`;

const Box = styled(TitledGreyBox)`
    ${tw`mb-6`};
`;

interface Option<T> {
    value: T;
    label: string;
    icon?: IconProp;
}

const THEME_OPTIONS: Option<ThemeMode>[] = [
    { value: 'dark', label: 'Dark', icon: faMoon },
    { value: 'light', label: 'Light', icon: faSun },
    { value: 'auto', label: 'Auto', icon: faAdjust },
];

const FONT_OPTIONS: Option<FontScale>[] = [
    { value: 'sm', label: 'Small' },
    { value: 'md', label: 'Default' },
    { value: 'lg', label: 'Large' },
];

const DENSITY_OPTIONS: Option<Density>[] = [
    { value: 'comfortable', label: 'Comfortable' },
    { value: 'compact', label: 'Compact' },
];

const SIDEBAR_OPTIONS: Option<SidebarStyle>[] = [
    { value: 'expanded', label: 'Expanded' },
    { value: 'collapsed', label: 'Collapsed' },
];

export default () => {
    const appearance = useStoreState((state: ApplicationStore) => state.appearance.data);
    const setAppearance = useStoreActions((actions: Actions<ApplicationStore>) => actions.appearance.setAppearance);
    const resetAppearance = useStoreActions((actions: Actions<ApplicationStore>) => actions.appearance.resetAppearance);

    return (
        <PageContentBlock title={'Appearance'}>
            <div css={tw`max-w-3xl mx-auto`}>
                <Box title={'Theme'} icon={faAdjust}>
                    <p css={tw`text-sm text-neutral-400 mb-3`}>
                        Choose a colour scheme. &ldquo;Auto&rdquo; follows your operating system preference.
                    </p>
                    <OptionRow>
                        {THEME_OPTIONS.map((option) => (
                            <OptionButton
                                key={option.value}
                                type={'button'}
                                selected={appearance.theme === option.value}
                                onClick={() => setAppearance({ theme: option.value })}
                            >
                                {option.icon && <FontAwesomeIcon icon={option.icon} />}
                                {option.label}
                            </OptionButton>
                        ))}
                    </OptionRow>
                </Box>

                <Box title={'Font Size'} icon={faFont}>
                    <OptionRow>
                        {FONT_OPTIONS.map((option) => (
                            <OptionButton
                                key={option.value}
                                type={'button'}
                                selected={appearance.fontScale === option.value}
                                onClick={() => setAppearance({ fontScale: option.value })}
                            >
                                {option.label}
                            </OptionButton>
                        ))}
                    </OptionRow>
                </Box>

                <Box title={'Layout Density'} icon={faColumns}>
                    <OptionRow>
                        {DENSITY_OPTIONS.map((option) => (
                            <OptionButton
                                key={option.value}
                                type={'button'}
                                selected={appearance.density === option.value}
                                onClick={() => setAppearance({ density: option.value })}
                            >
                                {option.label}
                            </OptionButton>
                        ))}
                    </OptionRow>
                </Box>

                <Box title={'Sidebar Style'} icon={faColumns}>
                    <p css={tw`text-sm text-neutral-400 mb-3`}>Collapse the sidebar to icons only on wider screens.</p>
                    <OptionRow>
                        {SIDEBAR_OPTIONS.map((option) => (
                            <OptionButton
                                key={option.value}
                                type={'button'}
                                selected={appearance.sidebarStyle === option.value}
                                onClick={() => setAppearance({ sidebarStyle: option.value })}
                            >
                                {option.label}
                            </OptionButton>
                        ))}
                    </OptionRow>
                </Box>

                <Box title={'Accessibility'} icon={faAdjust}>
                    <Switch
                        name={'high_contrast'}
                        label={'High Contrast Mode'}
                        description={'Increase text and border contrast for improved readability.'}
                        defaultChecked={appearance.highContrast}
                        onChange={(e) => setAppearance({ highContrast: e.currentTarget.checked })}
                    />
                </Box>

                <div css={tw`flex justify-end`}>
                    <Button.Text type={'button'} onClick={() => resetAppearance()}>
                        Reset to defaults
                    </Button.Text>
                </div>
            </div>
        </PageContentBlock>
    );
};
