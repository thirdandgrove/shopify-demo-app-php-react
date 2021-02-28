import React from 'react';
import ReactDOM from 'react-dom';

import { Provider, useAppBridge } from '@shopify/app-bridge-react';
import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client';
import { ApolloProvider } from '@apollo/client';
import { authenticatedFetch } from '@shopify/app-bridge-utils';
import translations from '@shopify/polaris/locales/en.json';
import { AppProvider } from '@shopify/polaris';
import '@shopify/polaris/dist/styles.css';
import AppRouter from './routers/AppRouter'
import AppContext from './contexts/appContext'

const appElement = document.getElementById('app');
const appSettings = JSON.parse(appElement.dataset.appSettings);

const AppWithApolloProvider = () => {
    // Use authenticatedFetch to add authorization header with JWT.
    const link = createHttpLink({
        // Add &XDEBUG_SESSION_START=PHPSTORM to debug proxy with PHPSTORM and xdebug.
        uri: `/graphql?shop=${appSettings.shopOrigin}`,
        fetch: authenticatedFetch(useAppBridge())
    });

    const client = new ApolloClient({
        link: link,
        cache: new InMemoryCache(),
        credentials: 'same-origin',
    });

    return (
        <ApolloProvider client={client}>
            <AppRouter/>
        </ApolloProvider>
    );
};

const template = (
    <AppContext.Provider value={appSettings}>
        <Provider config={appSettings}>
            <AppProvider i18n={translations}>
                <AppWithApolloProvider/>
            </AppProvider>
        </Provider>
    </AppContext.Provider>
);

ReactDOM.render(template, appElement);
