import React from 'react';
import nodeIcon from '@/assets/runtime/nodedotjs.svg';
import pythonIcon from '@/assets/runtime/python.svg';
import javaIcon from '@/assets/runtime/openjdk.svg';
import dockerIcon from '@/assets/runtime/docker.svg';
import discordIcon from '@/assets/runtime/discord.svg';
import styles from './runtime.module.css';

const runtimes = [
    { matches: ['discord'], label: 'Discord', icon: discordIcon },
    { matches: ['node', 'javascript', 'bun'], label: 'Node.js', icon: nodeIcon },
    { matches: ['python'], label: 'Python', icon: pythonIcon },
    { matches: ['java', 'openjdk', 'temurin'], label: 'Java', icon: javaIcon },
    { matches: ['docker'], label: 'Docker', icon: dockerIcon },
];

export default ({ image }: { image: string }) => {
    const source = (image || '').toLowerCase();
    const runtime = runtimes.find(({ matches }) => matches.some((value) => source.includes(value))) || {
        label: source ? 'Container' : 'Server',
        icon: dockerIcon,
    };

    return (
        <span className={styles.badge} title={'Runtime: ' + runtime.label}>
            <img src={runtime.icon} alt='' aria-hidden={'true'} />
            <span>{runtime.label}</span>
        </span>
    );
};
