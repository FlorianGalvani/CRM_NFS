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
import MasterCard from "examples/Cards/MasterCard";
import DefaultInfoCard from "examples/Cards/InfoCards/DefaultInfoCard";

// Billing page components
import PaymentMethod from "layouts/billing/components/PaymentMethod";
import Invoices from "layouts/billing/components/Invoices";
import BillingInformation from "layouts/billing/components/BillingInformation";
import Transactions from "layouts/billing/components/Transactions";
import Quotes from "layouts/billing/components/Quotes";

// click for quotes
import { useNavigate } from "react-router-dom";





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


function Billing() {
  
  
  const navigate = useNavigate();
  const handleQuotes = () => {
    navigate("/factures/nouveau");
  };
  const handleStripe = () => {
    navigate("/stripe");
  };

 
  return (
    <DashboardLayout>
      <DashboardNavbar absolute isMini />
      <MDBox mt={8}>
        <MDBox mb={3}>
          <Grid container spacing={3}>
            <Grid item xs={12} lg={8}>
              <Grid container spacing={3}>
                <Grid  display="grid"
          justifyContent="center"
          alignItems="center"item xs={12} xl={6}>
                  <MasterCard
                    number={4562112245947852}
                    holder="jack peterson"
                    expires="11/22"
                  />
                </Grid>
                <Grid item xs={12} md={6} xl={3}>
                <Button
                color="linear-gradient(195deg, #49a3f1, #1A73E8);"
                onClick={handleQuotes}>
                  <Icon sx={{ fontWeight: "bold" }}>add</Icon>
                  &nbsp;Facture
                </Button>
                </Grid>
                <Grid item xs={12} md={6} xl={3}>
                <Button 
                // onClick={handleStripe} 
                  >
                  <Icon sx={{ fontWeight: "bold" }}>credit_card</Icon>
                  &nbsp;Stripe
                </Button>
                </Grid>
                <Grid item xs={12}>
                  <PaymentMethod />
                </Grid>
              </Grid>
            </Grid>
            <Grid item xs={12} lg={4}>
              <Invoices />
            </Grid>
            <Grid item xs={12} lg={4}>
              <Quotes />
            </Grid>
          </Grid>
        </MDBox>
        <MDBox mb={3}>
          <Grid container spacing={3}>
            <Grid item xs={12} md={7}>
              <BillingInformation />
            </Grid>
            <Grid item xs={12} md={5}>
              <Transactions />
            </Grid>
          </Grid>
        </MDBox>
      </MDBox>
      <Footer />
    </DashboardLayout>
  );
}

export default Billing;
