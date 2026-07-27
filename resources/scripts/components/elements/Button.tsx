import React from 'react';
import classNames from 'classnames';
import Spinner from '@/components/elements/Spinner';
import styles from '@/components/elements/button/style.module.css';

interface Props {
    isLoading?: boolean;
    size?: 'xsmall' | 'small' | 'large' | 'xlarge';
    color?: 'green' | 'red' | 'primary' | 'grey';
    isSecondary?: boolean;
}

const roleClass = ({ color, isSecondary }: Props) =>
    color === 'red' ? styles.danger : isSecondary || color === 'grey' ? styles.text : styles.primary;

const sizeClass = ({ size }: Props) =>
    size === 'xsmall' || size === 'small' ? styles.small : size === 'large' || size === 'xlarge' ? styles.large : null;

type ComponentProps = Omit<JSX.IntrinsicElements['button'], 'ref' | 'color'> & Props;

const ButtonStyle: React.FC<ComponentProps> = ({ children, className, color, isSecondary, size, ...props }) => (
    <button
        className={classNames(styles.button, roleClass({ color, isSecondary }), sizeClass({ size }), className)}
        {...props}
    >
        {children}
    </button>
);

const Button: React.FC<ComponentProps> = ({ children, isLoading, ...props }) => (
    <ButtonStyle {...props}>
        {isLoading && (
            <span className={'absolute inset-0 flex items-center justify-center'}>
                <Spinner size={'small'} />
            </span>
        )}
        <span className={isLoading ? 'text-transparent' : undefined}>{children}</span>
    </ButtonStyle>
);

type LinkProps = Omit<JSX.IntrinsicElements['a'], 'ref' | 'color'> & Props;

const LinkButton: React.FC<LinkProps> = ({ children, className, color, isSecondary, size, ...props }) => (
    <a
        className={classNames(styles.button, roleClass({ color, isSecondary }), sizeClass({ size }), className)}
        {...props}
    >
        {children}
    </a>
);

export { LinkButton, ButtonStyle };
export default Button;
