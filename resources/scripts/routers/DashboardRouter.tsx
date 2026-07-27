import React from 'react';
import { NavLink, Route, Switch } from 'react-router-dom';
import NavigationBar from '@/components/NavigationBar';
import DashboardContainer from '@/components/dashboard/DashboardContainer';
import { NotFound } from '@/components/elements/ScreenBlock';
import TransitionRouter from '@/TransitionRouter';
import { useLocation } from 'react-router';
import Spinner from '@/components/elements/Spinner';
import routes from '@/routers/routes';
import Sidebar from '@/components/Sidebar';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faServer, faUser } from '@fortawesome/free-solid-svg-icons';
import { IconProp } from '@fortawesome/fontawesome-svg-core';

export default () => {
    const location = useLocation();

    return (
        <>
            <NavigationBar />
            <Sidebar>
                <div className='customer-sidebar-brand'>
                    <span className='customer-sidebar-mark'>V</span>
                    <span>VANTAHOST</span>
                </div>
                <NavLink to={'/'} exact>
                    <div className='icon'><FontAwesomeIcon icon={faServer} /></div>
                    Servers
                </NavLink>
                <NavLink to={'/account'}>
                    <div className='icon'><FontAwesomeIcon icon={faUser} /></div>
                    Account
                </NavLink>
                {location.pathname.startsWith('/account') && (
                    <div className='customer-sidebar-subnav'>
                    {routes.account
                        .filter((route) => !!route.name)
                        .map(({ path, name, exact = false, iconProp }) => (
                            <NavLink key={path} to={`/account/${path}`.replace('//', '/')} exact={exact}>
                                <div className='icon'>
                                    <FontAwesomeIcon icon={iconProp as IconProp} />
                                </div>
                                {name}
                            </NavLink>
                        ))}
                    </div>
                )}
                <div className='customer-sidebar-footer'>
                    <span className='customer-sidebar-live'></span> Secure client panel
                </div>
            </Sidebar>

            <TransitionRouter>
                <React.Suspense fallback={<Spinner centered />}>
                    <Switch location={location}>
                        <Route path={'/'} exact>
                            <DashboardContainer />
                        </Route>
                        {routes.account.map(({ path, component: Component }) => (
                            <Route key={path} path={`/account/${path}`.replace('//', '/')} exact>
                                <Component />
                            </Route>
                        ))}
                        <Route path={'*'}>
                            <NotFound />
                        </Route>
                    </Switch>
                </React.Suspense>
            </TransitionRouter>
        </>
    );
};
