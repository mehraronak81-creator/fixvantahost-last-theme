import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-xl no-underline items-center p-4 transition-all duration-200 overflow-hidden`};
    background: var(--card-bg, #1d1f22);
    color: var(--text-main, #f3f1ea);
    border: 1px solid var(--color-border, #35383d);
    border-radius: var(--vh-radius-card, 8px);
    box-shadow: var(--vh-shadow-1);

    ${(props) =>
        props.$hoverable !== false &&
        `
        &:hover {
            border-color: var(--color-accent, #4f8cff);
            background: var(--card-bg-hover);
            border-color: #4a4e54;
        }
    `};

    & .icon {
        ${tw`rounded-xl w-16 flex items-center justify-center p-3`};
        background: var(--color-surface, #1e1e2a);
    }
`;
