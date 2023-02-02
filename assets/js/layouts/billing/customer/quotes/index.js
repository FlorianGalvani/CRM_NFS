import React from "react";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import Grid from "@mui/material/Grid";
import MDBox from "components/MDBox";
import {Link} from "react-router-dom";
import {Axios, Formatter} from "utils";
import Card from "@mui/material/Card";
import {Bill} from "layouts/billing/customer/components/Quotes";
import MDButton from "components/MDButton";

const CustomerQuotes = () => {
    const [quotes, setQuotes] = React.useState([]);

    React.useEffect(() => {
        const getQuotes = async () => {

            const response = await Axios.get('/customer-quotes');

            if (response.error) {
                console.log(response.data)
            } else {
                setQuotes(response.data)
            }
        }
        getQuotes();
    }, [])

    function getQuoteCommercial(quote) {
        return quote?.customer.commercial.user
    }

    return (
        <DashboardLayout>
            <DashboardNavbar/>
            <MDBox mt={8}>
                <MDBox mb={3}>
                    <Grid container spacing={3}>
                        {
                            quotes ? quotes.filter((quote) => quote.transaction !== undefined)
                                .filter((quote) => quote.transaction !== null)
                                .map((quote, index) => (
                                <Grid item xs={12} key={index}>
                                    <Card sx={{height: "100%"}}>
                                        <MDBox p={2}>
                                            <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                                                <Bill
                                                    key={index}
                                                    name={quote?.fileName}
                                                    company={quote?.transaction?.type}
                                                    email={getQuoteCommercial(quote).email}
                                                    amount={quote?.transaction?.amount}
                                                    link={'/transactions/mes-devis/paiement/' + quote?.transaction?.id}
                                                />
                                            </MDBox>
                                        </MDBox>
                                    </Card>
                                </Grid>
                            )) : null
                        }
                    </Grid>
                </MDBox>
            </MDBox>
        </DashboardLayout>
    )
}

export default CustomerQuotes;