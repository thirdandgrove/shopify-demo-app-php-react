import React from 'react';
import {BrowserRouter, Route, Switch} from 'react-router-dom';
import HomePage from '../home/components/HomePage';
import ProductRoot from '../product/components/ProductRoot';

const AppRouter = () => (
    <BrowserRouter>
            <Switch>
                <Route path="/" component={HomePage} exact={true} />
                <Route path="/edit-product" component={ProductRoot} />
            </Switch>
    </BrowserRouter>
);

export default AppRouter;
