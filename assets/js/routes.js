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

/** 
  All of the routes for the Material Dashboard 2 React are added here,
  You can add a new route, customize the routes and delete the routes here.

  Once you add a new route on this file it will be visible automatically on
  the Sidenav.

  For adding a new route you can follow the existing routes in the routes array.
  1. The `type` key with the `collapse` value is used for a route.
  2. The `type` key with the `title` value is used for a title inside the Sidenav. 
  3. The `type` key with the `divider` value is used for a divider between Sidenav items.
  4. The `name` key is used for the name of the route on the Sidenav.
  5. The `key` key is used for the key of the route (It will help you with the key prop inside a loop).
  6. The `icon` key is used for the icon of the route on the Sidenav, you have to add a node.
  7. The `collapse` key is used for making a collapsible item on the Sidenav that has other routes
  inside (nested routes), you need to pass the nested routes inside an array as a value for the `collapse` key.
  8. The `route` key is used to store the route location which is used for the react router.
  9. The `href` key is used to store the external links location.
  10. The `title` key is only for the item with the type of `title` and its used for the title text on the Sidenav.
  10. The `component` key is used to store the component of its route.
*/

import React from "react";

// Material Dashboard 2 React layouts
import Dashboard from "./layouts/dashboard";
import Tables from "./layouts/tables";
import Billing from "./layouts/billing";
import Quotes from "./layouts/quotes";
import QuoteForm from "./layouts/quotesform";
import Payment from "./layouts/payment";

// import RTL from "./layouts/rtl";
// import Notifications from "./layouts/notifications";
import Profile from "./layouts/profile";
import SignIn from "./layouts/authentication/sign-in";
import SignUp from "./layouts/authentication/sign-up";

// @mui icons
import Icon from "@mui/material/Icon";
import CustomerInvoice from "layouts/billing/customer/invoices/show";
import CustomerInvoices from "layouts/billing/customer/invoices";
import Home from "./Pages/Home";
import CustomerQuote from "layouts/billing/customer/quotes/show";
import CustomerQuotes from "layouts/billing/customer/quotes";
import UserProfile from "layouts/profile/user";

const routes = [
  {
    type: "collapse",
    name: "Tableau de bord",
    key: "dashboard",
    icon: <Icon fontSize="small">dashboard</Icon>,
    route: "/dashboard",
    component: <Home/>,
  },
  {
    type: "collapse",
    name: "Tableau des utilisateurs",
    key: "utilisateurs",
    icon: <Icon fontSize="small">group</Icon>,
    route: "/utilisateurs",
    component: <Tables />,
  },
  {
    type: "collapse",
    name: "Factures",
    key: "factures",
    icon: <Icon fontSize="small">receipt_long</Icon>,
    route: "/factures",
    component: <Billing />,
  },
  {
    type: "collapse",
    name: "Profil",
    key: "profil",
    icon: <Icon fontSize="small">person</Icon>,
    route: "/profil",
    component: <Profile />,
  },
  {
    type: "collapse",
    name: "Connexion",
    key: "connexion",
    icon: <Icon fontSize="small">login</Icon>,
    route: "/connexion",
    component: <SignIn />,
  },
  {
    type: "collapse",
    name: "Créer un compte",
    key: "compte",
    icon: <Icon fontSize="small">person_add</Icon>,
    route: "/compte",
    component: <SignUp />,
  },
  {
    type: "collapse",
    name: "Devis",
    key: "devistemp",
    icon: <Icon fontSize="small">receipt</Icon>,
    route: "/devis",
    component: <Quotes />,
  },
  {
    type: "collapse",
    name: "Créer un Devis",
    key: "devisform",
    icon: <Icon fontSize="small">receipt</Icon>,
    route: "/devis/nouveau",
    component: <QuoteForm />,
  },
  {
    key: "utilisateur",
    route: "/utilisateurs/:id",
    component: <UserProfile />,
  },
    // Routes client
  // {
  //   type: "collapse",
  //   name: "Mes transactions",
  //   key: "transactions",
  //   icon: <Icon fontSize="small">receipt_long</Icon>,
  //   route: "/transactions",
  //   component: <CustomerBilling />,
  // },
  {
    key: "mes-factures",
    route: "/transactions/mes-factures",
    component: <CustomerInvoices />
  },{
    key: "mes-devis",
    route: "/transactions/mes-devis",
    component: <CustomerQuotes />
  },
  {
    key: "devis",
    route: "/transactions/mes-devis/:id",
    component: <CustomerQuote />,
  },
  {
    key: "facture",
    route: "/transactions/mes-factures/:id",
    component: <CustomerInvoice />,
  },
  {
    key: "paiement",
    route: "/transactions/mes-devis/paiement/:id",
    component: <Payment />,
  },
];

export default routes;
