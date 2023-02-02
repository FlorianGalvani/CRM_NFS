/**
 =========================================================
 * Material Dashboard 2 React - v2.1.0
 =========================================================

 * Product Page: https://www.creative-tim.com/product/material-dashboard-react
 * Copyright 2022 Creative Tim (https://www.creative-tim.com)

 Coded by www.creative-tim.com

 =========================================================

 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 */
import React from "react";
// @mui material components
import Card from "@mui/material/Card";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDButton from "components/MDButton";

// Billing page components
import Quote from "layouts/billing/components/Quote";
import { Link, redirect } from "react-router-dom";
import axios from 'axios'
function Quotes() {
    const [quotes, setQuotes] = React.useState(null);
    const token = document.cookie.split("=")[1];
    const [formData, setFormData] = React.useState(null);

    React.useEffect(() => {
        axios.get('api/quotes/list/latest', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then((response) => {
            response.data.forEach(element => {
                element.data = JSON.parse(element.data);
            });
            console.log(response.data)
            setQuotes(response.data);
        })

        axios.get('/api/commercial/quotes/formdata', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        }).then(
            (response) => {
                console.log('LOOK AT ME : ',response.data)
                setIsLoading(false);
                setFormData(response.data.formData);
            }
        )

    }, [])

    return (
        <Card sx={{ height: "100%" }}>
            <MDBox
                pt={2}
                px={2}
                display="flex"
                justifyContent="space-between"
                alignItems="center"
            >
                <MDTypography variant="h6" fontWeight="medium">
                    Devis
                </MDTypography>
                <Link to={'/devis/nouveau'} variant="outlined" color="info" size="small">
                    Nouveau devis
                </Link>
                <Link to={'/devis'} variant="outlined" color="info" size="small">
                    voir tout
                </Link>
            </MDBox>
            <MDBox p={2}>
                <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                    {
                        quotes !== null && quotes.map((quote, key) => (
                            <Quote key={'Quote_' + key} formData={formData} pdfData={quote.data} date="March, 01, 2020" customer={quote.data.clientName} price="$180" />
                        ))
                    }
                </MDBox>
            </MDBox>
        </Card>
    );
}

export default Quotes;
