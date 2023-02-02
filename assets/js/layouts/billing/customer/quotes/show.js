import React from 'react';
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";

import Card from "@mui/material/Card";
import MDBox from "components/MDBox";

import axios from "axios";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import {Alert} from "@mui/material";
import {useLocation} from "react-router-dom";
import { Formatter } from "utils/Formatter.utils";
import {Axios} from "utils";
import Quote from "layouts/billing/components/Quote";

export default function CustomerQuote() {
    const [isLoading, setIsLoading] = React.useState(true);
    const [error, setError] = React.useState(false);
    const [message, setMessage] = React.useState(null);
    const [quote, setQuote] = React.useState(null);

    const location = useLocation();
    const pathnames = location.pathname.split('/');
    const id = pathnames[pathnames.length - 1];

    React.useEffect(() => {
        // js get document.cookie value of token
        const getQuote = async () => {
            setIsLoading(true)
            const response = await Axios.get(`/customer-quotes/${id}`);

            if(response.error) {
                if(response.data.status === 404) {
                    setMessage("Aucun devis n'a été trouvé");
                }
                setError(true)
                console.log(response.data)
            } else {
                console.log(response.data)
                setQuote(response.data)
            }
            setIsLoading(false);
        }
        getQuote();
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
                            : <Quote date={Formatter.formatDate(quote?.createdAt)} id={'#'+quote?.fileName} price={quote?.data.amount+' €'} customer={quote?.customer?.user} />
                    }
                </Card>
            </MDBox>
        </DashboardLayout>
    )
}