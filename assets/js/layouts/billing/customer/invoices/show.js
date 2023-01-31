import React from 'react';
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";

import Card from "@mui/material/Card";
import MDBox from "components/MDBox";

import axios from "axios";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import {Alert} from "@mui/material";
import {useLocation} from "react-router-dom";
import { Formatter } from "utils/Formatter.utils";
import {Cookie} from "utils";

async function get(token, param) {
    const response = [];
    await axios.get(`/api/documents/${param}`, {
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

export default function Invoice() {
    const [isLoading, setIsLoading] = React.useState(true);
    const [error, setError] = React.useState(false);
    const [message, setMessage] = React.useState(null);
    const [token] = React.useState(Cookie.getCookie("token"));
    const [invoice, setInvoice] = React.useState(null);

    const location = useLocation();
    const pathnames = location.pathname.split('/');
    const id = pathnames[pathnames.length - 1];

    React.useEffect(() => {
        // js get document.cookie value of token
        const getInvoice = async () => {
            setIsLoading(true)
            const response = await get(token, id);

            if(response.error) {
                if(response.data.status === 404) {
                    setMessage("Aucune facture n'a été trouvée");
                }
                setError(true)
                console.log(response.data)
            } else {
                setInvoice(response.data)
            }
            setIsLoading(false);
        }
        getInvoice();
    }, [])

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
                            : <Invoice date={Formatter.formatDate(invoice?.createdAt)} id={'#'+invoice?.fileName} price={invoice?.data.amount+' €'} />
                    }
                </Card>
            </MDBox>
        </DashboardLayout>
    )
}