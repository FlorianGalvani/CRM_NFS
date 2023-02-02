import { Button } from "@mui/material";
import React from "react";
import {Cookie} from "utils";
import jwt_decode from "jwt-decode";
import Dashboard from "layouts/dashboard";
import CustomerBilling from "layouts/billing/customer";

export default function Home() {
    const [token, setToken] = React.useState(null);

    const getUserFromCookie = () => {
        if(Cookie.getCookie("token") !== undefined) {
            const jwtToken = jwt_decode(Cookie.getCookie("token"));
            setToken(jwtToken);
        }
    }

    React.useEffect(() => {
        getUserFromCookie()
    }, [])

    if(token?.account === 'customer') {
        return (
            <CustomerBilling/>
        )
    }

    return (
        <Dashboard/>
    );

}
