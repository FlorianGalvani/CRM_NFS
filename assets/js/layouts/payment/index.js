import React from 'react';
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";

import Card from "@mui/material/Card";
import MDButton from "components/MDButton";
import MDBox from "components/MDBox";

import axios from "axios";
import {loadStripe} from '@stripe/stripe-js';
import {Elements, ElementsConsumer} from '@stripe/react-stripe-js';

import {config} from "../../config/config";
import {CheckoutForm, } from "layouts/payment/components/checkoutForm";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import {Alert} from "@mui/material";
import {useLocation} from "react-router-dom";
import { Formatter } from "utils/Formatter.utils";
import {Cookie} from "utils";

const stripePromise = loadStripe(config.STRIPE_PUBLIC_KEY);

async function get(token, param) {
    const response = [];
    await axios.get(`/api/stripe_create/${param}`, {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    }).then(
        (res) => {
            response['success'] = true;
            response['data'] = res.data;
        }
    ).catch((err) => {
        response['error'] = true;
        response['data'] = err.response.data;
    })
    return response;
}

export default function Payment() {
    const [isLoading, setIsLoading] = React.useState(true);
    const [clientSecret, setClientSecret] = React.useState('');
    const [error, setError] = React.useState(false);
    const [transaction, setTransaction] = React.useState(null);
    const [message, setMessage] = React.useState(null);
    const [token, setToken] = React.useState('')

    const location = useLocation();
    const pathnames = location.pathname.split('/');
    const id = pathnames[pathnames.length - 1];

    React.useEffect(() => {
        // js get document.cookie value of token
        setToken(Cookie.getCookie("token"));

        const getTransaction = async () => {
            setIsLoading(true)
            const response = await get(token, id);

            if(response.error) {
                if(response.data.status === 404) {
                    setMessage("Aucune transaction n'a été trouvée");
                }
                setError(true)
                console.log(response.data)
            } else {
                setClientSecret(response.data.clientSecret)
                setTransaction({
                    object: response.data.transaction,
                    factureDate: response.data.factureDate
                })
            }
            setIsLoading(false);
        }
        getTransaction();
    }, [])

    const appearance = {
        theme: 'stripe',
        fontFamily: 'Roboto'
    };
    const options = {
        clientSecret,
        appearance,
    };

    if(error) {
        return (
            <DashboardLayout>
                <DashboardNavbar />
                <Card mt={8} sx={{px: 2, py: 4}} >
                    {
                        message ? <Alert severity={'info'}>{message}</Alert>
                            : <Alert severity={'error'}>Une erreur est survenue</Alert>
                    }
                </Card>
            </DashboardLayout>
        )
    }

    return (
        <DashboardLayout>
            <DashboardNavbar />
            <MDBox mt={4}>
                <Card sx={{px: 2, py: 4}}>
                    {
                        isLoading ? 'loading...'
                            : <Elements options={options} stripe={stripePromise}>
                                <h4>{transaction.object.type}</h4>
                                <h5>
                                    {transaction.object.label},
                                    le {Formatter.formatDate(transaction.factureDate)}
                                </h5>
                                <h5>Montant : {transaction.object.amount}</h5>
                                <CheckoutForm {...transaction} token={token} />
                            </Elements>
                    }
                </Card>
            </MDBox>
        </DashboardLayout>
    )
}