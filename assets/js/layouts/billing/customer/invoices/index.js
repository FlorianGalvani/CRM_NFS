import React from "react";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import Grid from "@mui/material/Grid";
import MDBox from "components/MDBox";
import {Link} from "react-router-dom";
import {Cookie, Formatter} from "utils";
import axios from "axios";
import Invoice from "layouts/billing/components/Invoice";

const get = async (token) => {
    const response = [];
    await axios.get('api/customer-invoices', {
        headers: {
            Authorization: 'Bearer ' + token
        }
    }).then((res) => {
        response['success'] = true;
        response['data'] = res.data;
    }).catch((err) => {
        response['error'] = true;
        response['data'] = err.response.data;
    })
    return response;
}

const Invoices = () => {
    const [token] = React.useState(Cookie.getCookie("token"));
    const [invoices, setInvoices] = React.useState([]);

    React.useEffect(() => {
        const getInvoices = async () => {

            const response = await get(token);

            if(response.error) {
                console.log(response.data)
            } else {
                console.log(response.data)
                setInvoices(response.data)
            }
        }
        getInvoices();
    },  [])

    const invoiceDetailLink = (invoice) => {
        return invoice?.transaction?.paymentStatus === 'payment_success' ? '/'+invoice?.id : '/paiement/'+invoice?.transaction?.id
    }

    return (
        <DashboardLayout>
            <DashboardNavbar absolute isMini />
            <MDBox mt={8}>
                <MDBox mb={3}>
                    <Grid container spacing={3}>
                        {
                            invoices.map((invoice, index) => (
                                <Grid key={index} item xs={12} lg={7}>
                                    <MDBox p={2}>
                                        <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                                            <Link to={invoiceDetailLink(invoice)}>
                                                <Invoice date={Formatter.formatDate(invoice?.createdAt)} id={'#'+invoice?.fileName} price={invoice?.data.amount+' €'} />
                                            </Link>
                                        </MDBox>
                                    </MDBox>
                                </Grid>
                            ))
                        }
                    </Grid>
                </MDBox>
            </MDBox>
        </DashboardLayout>
    )
}

export default Invoices;
