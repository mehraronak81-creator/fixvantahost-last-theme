import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-xl no-underline items-center p-4 transition-all duration-200 overflow-hidden`};
    background: var(--card-bg, #14171b);
    color: var(--text-main, #e7e9ec);
    border: 1px solid var(--color-border, #262b31);
    border-radius: var(--vh-radius-card, 10px);
    box-shadow: var(--vh-shadow);

    ${(props) =>
        props.$hoverable !== false &&
        `
        &:hover {
            background: var(--card-bg-hover);
            border-color: var(--color-border, #262b31);
        }
    `};

    & .icon {
        ${tw`rounded-xl w-16 flex items-center justify-center p-3`};
        background: var(--color-surface, #14171b);
    }
`;
