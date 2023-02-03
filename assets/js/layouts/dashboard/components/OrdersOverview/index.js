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
import Icon from "@mui/material/Icon";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";

// Material Dashboard 2 React example components
import TimelineItem from "examples/Timeline/TimelineItem";

function OrdersOverview() {
  return (
    <Card sx={{ height: "100%" }}>
      <MDBox pt={3} px={3}>
        <MDTypography variant="h6" fontWeight="medium">
          Résumé action
        </MDTypography>
      </MDBox>
      <MDBox p={2}>
        <TimelineItem
          color="success"
          icon="notifications"
          title="2400€, devis #1832405"
          dateTime="27 DEC 9:34"
        />
        <TimelineItem
          color="error"
          icon="inventory_2"
          title="Nouveau devis #1832412"
          dateTime="21 DEC 15:34"
        />
        <TimelineItem
          color="info"
          icon="shopping_cart"
          title="Nouveaux produits ajoutés pour le devis #1832405"
          dateTime="21 DEC 19:23"
        />
        <TimelineItem
          color="warning"
          icon="payment"
          title="Nouvelle facture #1832405"
          dateTime="20 DEC 2:20"
        />
        <TimelineItem
          color="success"
          icon="notifications"
          title="25000€, devis #1832400"
          dateTime="18 DEC 9:54"
        />
        <TimelineItem
          color="primary"
          icon="vpn_key"
          title="Nouveau client"
          dateTime="18 DEC 4:54"
          lastItem
        />
        <TimelineItem
          color="error"
          icon="inventory_2"
          title="Nouveau devis #1832400"
          dateTime="17 DEC 20:54"
        />
        <TimelineItem
          color="info"
          icon="shopping_cart"
          title="Nouveaux produits ajoutés pour le devis #1832400"
          dateTime="17 DEC 19:54"
        />
        <TimelineItem
          color="info"
          icon="shopping_cart"
          title="Nouveaux produits ajoutés pour le devis #1832400"
          dateTime="17 DEC 19:54"
        />
        <TimelineItem
          color="primary"
          icon="vpn_key"
          title="Nouveau client"
          dateTime="17 DEC 14:20"
          lastItem
        />
      </MDBox>
    </Card>
  );
}

export default OrdersOverview;
