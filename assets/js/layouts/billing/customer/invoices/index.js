import React from "react";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import Grid from "@mui/material/Grid";
import MDBox from "components/MDBox";
import {Link} from "react-router-dom";
import {Axios, Formatter} from "utils";
import Card from "@mui/material/Card";
import MDTypography from "components/MDTypography";
import Icon from "@mui/material/Icon";
import PropTypes from "prop-types";
import {Alert} from "@mui/material";

const CustomerInvoices = () => {
    const [invoices, setInvoices] = React.useState([]);

    React.useEffect(() => {
        const getInvoices = async () => {

            const response = await Axios.get('/customer-invoices');

            if (response.error) {
                console.log(response.data)
            } else {
                setInvoices(response.data)
            }
        }
        getInvoices();
    }, [])

    const invoiceDetailLink = (invoice) => {
        return invoice?.transaction?.paymentStatus === 'payment_success'
            || invoice?.transaction?.paymentStatus === 'quotation_sent' ?
                '/transactions/mes-factures/' + invoice?.id
                : '/transactions/mes-factures/paiement/' + invoice?.transaction?.id
    }

    return (
        <DashboardLayout>
            <DashboardNavbar/>
            <MDBox mt={8}>
                <MDBox mb={3}>
                    <Grid container spacing={3}>
                        {
                            invoices ? invoices.map((invoice, index) => (
                                <Grid item xs={12} key={index}>
                                    <Card sx={{height: "100%"}}>
                                        <MDBox p={2}>
                                            <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                                                <Invoice
                                                    date={Formatter.formatDate(invoice?.createdAt)}
                                                    id={'#' + invoice?.fileName}
                                                    price={invoice?.data.amount + ' €'}
                                                    status={invoice?.transaction.paymentStatus}
                                                />
                                                <Link to={invoiceDetailLink(invoice)}>
                                                    Voir
                                                </Link>
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

export default CustomerInvoices;

export function Invoice({date, id, price, noGutter, status}) {
    const invoiceStatus = () => {
        let paymentStatus = {};
        switch (status) {
            case 'invoice_sent':
                paymentStatus = {severity: 'warning', label: 'En attente de paiement'};
                break;
            case 'payment_intent':
                paymentStatus = {severity: 'warning', label: 'En attente de paiement'};
                break;
            case 'payment_failure':
                paymentStatus = {severity: 'error', label: 'Echec de paiement'};
                break;
            default:
                paymentStatus = {severity: '', label: ''};
        }
        return paymentStatus;
    };

    return (
        <MDBox
            component="li"
            display="flex"
            justifyContent="space-between"
            alignItems="center"
            py={1}
            pr={1}
            mb={noGutter ? 0 : 1}
        >
            <MDBox lineHeight={1.125}>
                <MDTypography display="block" variant="button" fontWeight="medium">
                    {date}
                </MDTypography>
                <MDTypography variant="caption" fontWeight="regular" color="text">
                    {id}
                </MDTypography>
            </MDBox>
            <MDBox display="flex" alignItems="center">
                <MDTypography variant="button" fontWeight="regular" color="text">
                    {price}
                </MDTypography>
                <MDBox
                    display="flex"
                    alignItems="center"
                    lineHeight={1}
                    ml={3}
                    sx={{cursor: "pointer"}}
                >
                    <Icon fontSize="small">picture_as_pdf</Icon>
                    <MDTypography variant="button" fontWeight="bold">
                        &nbsp;PDF
                    </MDTypography>
                </MDBox>
            </MDBox>
            {
                status ? <MDBox display="flex" alignItems="center">
                    <Alert severity={invoiceStatus().severity}>{invoiceStatus().label}</Alert>
                </MDBox> : null
            }
        </MDBox>
    );
}

// Setting default values for the props of Invoice
Invoice.defaultProps = {
    noGutter: false,
};

// Typechecking props for the Invoice
Invoice.propTypes = {
    date: PropTypes.string.isRequired,
    id: PropTypes.string.isRequired,
    price: PropTypes.string.isRequired,
    noGutter: PropTypes.bool,
    status: PropTypes.string,
}