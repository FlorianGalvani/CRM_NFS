import React from "react";
import styled from '@emotion/styled'
// @mui material components
import Grid from "@mui/material/Grid";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDButton from "components/MDButton";
import Icon from "@mui/material/Icon";

// Material Dashboard 2 React examples
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import Footer from "examples/Footer";

// Billing page components
import Transaction from "layouts/billing/components/Transaction";

// click for quotes
import {Link} from "react-router-dom";
import jwt_decode from "jwt-decode";
import {Cookie} from "utils";
import {useMaterialUIController} from "context";
import Tooltip from "@mui/material/Tooltip";
import Card from "@mui/material/Card";
import MDTypography from "components/MDTypography";

// Images
import masterCardLogo from "assets/images/logos/mastercard.png";
import visaLogo from "assets/images/logos/visa.png";
import pattern from "assets/images/illustrations/pattern-tree.svg";
import PropTypes from "prop-types";

import {Formatter} from 'utils/Formatter.utils';
import Quotes from "layouts/billing/customer/components/Quotes";
import {Axios} from "utils/Axios.utils";
import {Invoice} from "layouts/billing/customer/invoices";

function CustomerBilling() {
    const [account, setAccount] = React.useState(null);
    const [transactions, setTransactions] = React.useState([]);
    const [lastInvoices, setLastInvoinces] = React.useState([]);
    const [lastThreeQuotes, setLastThreeQuotes] = React.useState([]);

    const getAccount = () => {
        if (Cookie.getCookie("token") !== undefined) {
            const jwtToken = jwt_decode(Cookie.getCookie("token"));
            setAccount(jwtToken.user.account)
        }
    };

    React.useEffect(() => {
        getAccount();

        const getTransactions = async () => {

            const response = await Axios.get('/customer-transactions');

            if(response.error) {
                console.log(response.data)
            } else {
                setTransactions(response.data.transactions)
                setLastInvoinces(response.data.lastInvoice)
                setLastThreeQuotes(response.data.lastThreeQuotes)
            }
        }
        getTransactions();
    },  [])

    return (
        <DashboardLayout>
            <DashboardNavbar />
            <MDBox mt={3}>
                <MDBox mb={3}>
                    <Grid container spacing={3}>
                        <Grid item xs={12} lg={7}>
                            <Grid container spacing={3}>
                                <Grid display="grid" justifyContent="center" alignItems="center"item xs={12} xl={6}>
                                    {
                                        account?.paymentMethod ?
                                            <MasterCard
                                                number={`************${account?.paymentMethod.card?.last4}`}
                                                holder={account?.name}
                                                expires={`
                                                    ${account?.paymentMethod.card?.exp_month}/
                                                    ${account?.paymentMethod.card?.exp_year}
                                                `}
                                            />
                                            : null
                                    }
                                </Grid>
                                <Grid item xs={12}>
                                    {
                                        account?.paymentMethod ?
                                            <PaymentMethod account={account} />
                                            : null
                                    }
                                </Grid>
                            </Grid>
                        </Grid>
                        <Grid item xs={12} lg={5}>
                            <Card sx={{ height: "100%" }}>
                                <MDBox
                                    pt={2}
                                    px={2}
                                    display="flex"
                                    justifyContent="space-between"
                                    alignItems="center"
                                >
                                    <MDTypography variant="h6" fontWeight="medium">
                                        Factures
                                    </MDTypography>
                                    <Link to={'/transactions/mes-factures'}>
                                        <MDButton variant="outlined" color="info" size="small">
                                            voir tout
                                        </MDButton>
                                    </Link>
                                </MDBox>
                                <MDBox p={2}>
                                    <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                                        {
                                            lastInvoices.map((lastInvoice, index) => (
                                                <Invoice date={Formatter.formatDate(lastInvoice?.createdAt)} id={'#'+lastInvoice?.fileName} price={lastInvoice?.data.amount+' €'} />
                                            ))
                                        }
                                    </MDBox>
                                </MDBox>
                            </Card>
                        </Grid>
                    </Grid>
                </MDBox>
                <MDBox mb={3}>
                    <Grid container spacing={3}>
                        <Grid item xs={12} md={7}>
                            {
                                lastThreeQuotes ? <Quotes quotes={lastThreeQuotes} /> : null
                            }
                        </Grid>
                        <Grid item xs={12} md={5}>
                            {
                                transactions ?
                                    <Transactions transactions={transactions}/>
                                    : null
                            }
                        </Grid>
                    </Grid>
                </MDBox>
            </MDBox>
            <Footer />
        </DashboardLayout>
    );
}

export default CustomerBilling;

function MasterCard({ color, number, holder, expires }) {
    const numbers = [...`${number}`];

    const num1 = numbers.slice(0, 4).join("");
    const num2 = numbers.slice(4, 8).join("");
    const num3 = numbers.slice(8, 12).join("");
    const num4 = numbers.slice(12, 16).join("");

    return (
        <Card
            sx={({
                     palette: { gradients },
                     functions: { linearGradient },
                     boxShadows: { xl },
                 }) => ({
                background: gradients[color]
                    ? linearGradient(gradients[color].main, gradients[color].state)
                    : linearGradient(gradients.dark.main, gradients.dark.state),
                boxShadow: xl,
                position: "relative",
            })}
        >
            <MDBox
                position="absolute"
                top={0}
                left={0}
                width="100%"
                height="100%"
                opacity={0.2}
                sx={{
                    backgroundImage: `url(${pattern})`,
                    backgroundSize: "cover",
                }}
            />
            <MDBox position="relative" zIndex={2} p={2}>
                <MDBox color="white" p={1} lineHeight={0} display="inline-block">
                    <Icon fontSize="default">wifi</Icon>
                </MDBox>
                <MDTypography
                    variant="h5"
                    color="white"
                    fontWeight="medium"
                    sx={{ mt: 3, mb: 5, pb: 1 }}
                >
                    {num1}&nbsp;&nbsp;&nbsp;{num2}&nbsp;&nbsp;&nbsp;{num3}
                    &nbsp;&nbsp;&nbsp;{num4}
                </MDTypography>
                <MDBox
                    display="flex"
                    justifyContent="space-between"
                    alignItems="center"
                >
                    <MDBox display="flex" alignItems="center">
                        <MDBox mr={3} lineHeight={1}>
                            <MDTypography
                                variant="button"
                                color="white"
                                fontWeight="regular"
                                opacity={0.8}
                            >
                                Card Holder
                            </MDTypography>
                            <MDTypography
                                variant="h6"
                                color="white"
                                fontWeight="medium"
                                textTransform="capitalize"
                            >
                                {holder}
                            </MDTypography>
                        </MDBox>
                        <MDBox lineHeight={1}>
                            <MDTypography
                                variant="button"
                                color="white"
                                fontWeight="regular"
                                opacity={0.8}
                            >
                                Expires
                            </MDTypography>
                            <MDTypography variant="h6" color="white" fontWeight="medium">
                                {expires}
                            </MDTypography>
                        </MDBox>
                    </MDBox>
                    <MDBox display="flex" justifyContent="flex-end" width="20%">
                        <MDBox
                            component="img"
                            src={masterCardLogo}
                            alt="master card"
                            width="60%"
                            mt={1}
                        />
                    </MDBox>
                </MDBox>
            </MDBox>
        </Card>
    );
}

MasterCard.defaultProps = {
    color: "dark",
};

MasterCard.propTypes = {
    color: PropTypes.oneOf([
        "primary",
        "secondary",
        "info",
        "success",
        "warning",
        "error",
        "dark",
    ]),
    number: PropTypes.string.isRequired,
    holder: PropTypes.string.isRequired,
    expires: PropTypes.string.isRequired,
};

function PaymentMethod({account}) {
    const [controller] = useMaterialUIController();
    const { darkMode } = controller;

    return (
        <Card>
            <MDBox
                pt={2}
                px={2}
                display="flex"
                justifyContent="space-between"
                alignItems="center"
            >
                <MDTypography variant="h6" fontWeight="medium">
                    Moyen de paiement
                </MDTypography>
                <MDButton disabled variant="gradient" color="dark">
                    <Icon sx={{ fontWeight: "bold" }}>add</Icon>
                    &nbsp;changer de carte
                </MDButton>
            </MDBox>
            <MDBox p={2}>
                <Grid container spacing={3}>
                    <Grid item xs={12} md={6}>
                        <MDBox
                            borderRadius="lg"
                            display="flex"
                            justifyContent="space-between"
                            alignItems="center"
                            p={3}
                            sx={{
                                border: ({ borders: { borderWidth, borderColor } }) =>
                                    `${borderWidth[1]} solid ${borderColor}`,
                            }}
                        >
                            <MDBox
                                component="img"
                                src={
                                    account?.paymentMethod?.card?.brand === 'visa' ?
                                        visaLogo
                                     : masterCardLogo
                                }
                                alt="master card"
                                width="10%"
                                mr={2}
                            />
                            <MDTypography variant="h6" fontWeight="medium">
                                ****&nbsp;&nbsp;****&nbsp;&nbsp;****&nbsp;&nbsp;{account?.paymentMethod?.card?.last4}
                            </MDTypography>
                            <MDBox
                                ml="auto"
                                lineHeight={0}
                                color={darkMode ? "white" : "dark"}
                            >
                                <Tooltip title="Edit Card" placement="top">
                                    <Icon sx={{ cursor: "pointer" }} fontSize="small">
                                        edit
                                    </Icon>
                                </Tooltip>
                            </MDBox>
                        </MDBox>
                    </Grid>
                </Grid>
            </MDBox>
        </Card>
    );
}

PaymentMethod.propTypes = {
    account: PropTypes.object,
};

function Transactions({transactions}) {
    const transactionProps = (transaction) => {
        let props = {
            color: '',
            value: '',
            icon: ''
        }

        switch(transaction.paymentStatus) {
            case 'payment_failure':
                props = {color: 'error', icon: 'priority_high'}
                break;
            case 'payment_abandoned':
                props = {color: 'error', icon: 'priority_high'}
                break;
            case 'payment_success':
                props = {color: 'success', icon: 'check'}
                break;
        }

        return props;
    }

    return (
        <Card sx={{ height: "100%" }}>
            <MDBox
                display="flex"
                justifyContent="space-between"
                alignItems="center"
                pt={3}
                px={2}
            >
                <MDTypography
                    variant="h6"
                    fontWeight="medium"
                    textTransform="capitalize"
                >
                    Transactions
                </MDTypography>
                <MDBox display="flex" alignItems="flex-start">
                    <MDBox color="text" mr={0.5} lineHeight={0}>
                        <Icon color="inherit" fontSize="small">
                            date_range
                        </Icon>
                    </MDBox>
                    <MDTypography variant="button" color="text" fontWeight="regular">
                        Janvier - Février 2023
                    </MDTypography>
                </MDBox>
            </MDBox>
            <MDBox pt={3} pb={2} px={2}>
                <MDBox
                    component="ul"
                    display="flex"
                    flexDirection="column"
                    p={0}
                    m={0}
                    sx={{ listStyle: "none" }}
                >
                    {
                        transactions.map((transaction, index) => (
                            <Transaction
                                key={index}
                                color={transactionProps(transaction).color}
                                icon={transactionProps(transaction).icon}
                                name={transaction.type}
                                description={transaction.label}
                                value={transaction.amount+' €'}
                            />
                        ))
                    }
                </MDBox>
            </MDBox>
        </Card>
    )
}

Transactions.propTypes = {
    transactions: PropTypes.array,
};

const Button = styled.button`
  height: 13.5rem;
  background-color: #fff;
  font-family: "Roboto","Helvetica","Arial",sans-serif;
  color: #344767;
  font-weight: 700;
  letter-spacing: 0.0075em;
  opacity: 1;
  text-transform: none;
  border: 0;
  outline: 0;
  padding: 1.5rem 2rem;
  transition: all 0.3s;
  cursor: pointer;
  border-radius: 0.75rem;
  border-bottom: 4px solid #d9d9d9;
    :hover {
      box-shadow: 0px 2px 1px -1px rgb(0 0 0 / 20%), 
                  0px 1px 1px 0px rgb(0 0 0 / 14%),  
                  0px 1px 3px 0px rgb(0 0 0 / 12%);
      transform: scale(1.03);
}
    :active {
      box-shadow: 0px 2px 1px -1px rgb(0 0 0 / 20%), 
      0px 1px 1px 0px rgb(0 0 0 / 14%), 
      0px 1px 3px 0px rgb(0 0 0 / 12%);
      transform: scale(0.98);
}
`