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
import Icon from "@mui/material/Icon";

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
                flexDirection="column"
            >
                <MDTypography variant="h6" fontWeight="medium">
                    Devis
                </MDTypography>
                <MDButton variant="gradient" color="dark">
                    <Icon sx={{ fontWeight: "bold" }} size="small">add</Icon>
                    <Link to={'/devis/nouveau'} size="small">
                    &nbsp; Nouveau devis
                    </Link>
                 </MDButton>
                 <MDButton variant="outlined" color="info" size="small"  sx={{ marginTop: 2 }}>
                    <Link to={'/devis'}size="small">
                        voir tout
                    </Link>
                </MDButton>
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
