/* eslint-disable react/prop-types */
/* eslint-disable react/function-component-definition */
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
//api
import React, { useEffect, useState } from "react";
import axios from "axios";
import jwt_decode from "jwt-decode";
import { Cookie } from "utils/index";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDAvatar from "components/MDAvatar";
import MDBadge from "components/MDBadge";

// Images
import team2 from "assets/images/team-2.jpg";
import team3 from "assets/images/team-3.jpg";
import team4 from "assets/images/team-4.jpg";
import {Link} from "react-router-dom";

export default function Data() {
  const [users, setUsers] = useState([]);

  const token = Cookie.getCookie("token");  
  
  const url = `http://localhost:8000/api/commercial-customers/`;
  
  // const urlAdmin = `all-users/`;
  
  // const urlCommercial = `commercial-customers/`;
  
  // const endpoint = tokenDecoded && tokenDecoded.account === "admin" ? urlAdmin : urlCommercial;

  const getAllUsers = () => {
    axios.get(url, {
      headers: {
        "Authorization": `Bearer ${token}`,
      },
    })
      .then((response) => {
        const allUsers = response.data;
        setUsers(allUsers);
        console.log(response.data);
      })
      .catch((error) => console.log(error));
  }

  useEffect(() => {
    getAllUsers()
  }, []);

  const Author = ({ image, name, email }) => (
    <MDBox display="flex" alignItems="center" lineHeight={1}>
      <MDAvatar src={image} name={name} size="sm" />
      <MDBox ml={2} lineHeight={1}>
        <MDTypography display="block" variant="button" fontWeight="medium">
          {name}
        </MDTypography>
        <MDTypography variant="caption">{email}</MDTypography>
      </MDBox>
    </MDBox>
  );

  const Job = ({ title, description }) => (
    <MDBox lineHeight={1} textAlign="left">
      <MDTypography
        display="block"
        variant="caption"
        color="text"
        fontWeight="medium"
      >
        {title}
      </MDTypography>
      <MDTypography variant="caption">{description}</MDTypography>
    </MDBox>
  );

  return {
    columns: [
      { Header: "Nom", accessor: "author", width: "30%", align: "left" },
      { Header: "téléphone", accessor: "phone", align: "left" },
      { Header: "email", accessor: "email", align: "center" },
      { Header: "Entreprise", accessor: "function", align: "center" },
      { Header: "Action", accessor: "action", align: "center" },
    ],

    rows:
      users && users.map(user => (
        {
          author: (
            <Author
              image={[team2, team3, team4][Math.floor(Math.random() * 3)]}
              name={user.user.firstname + " " + user.user.lastname}
            />
          ),
          phone: (
            <MDTypography
              component="a"
              href="#"
              variant="button"
              color="text"
              fontWeight="medium"
            >
              {user.user.phone}
            </MDTypography>
          ),
          email: (
            <MDTypography
              component="a"
              href="#"
              variant="caption"
              color="text"
              fontWeight="medium"
            >
              {user.user.email}
            </MDTypography>
          ),
          function: <Job title={user.user.company} />,
          status: (
            <MDBox ml={-1}>
              <MDBadge
                color="success"
                variant="gradient"
                size="sm"
              />
            </MDBox>
          ),
          employed: (
            <MDTypography
              component="a"
              href="#"
              variant="caption"
              color="text"
              fontWeight="medium"
            >
            </MDTypography>
          ),
          action: (
              <MDBox
                  pt={2}
                  px={2}
                  display="flex"
                  justifyContent="space-between"
                  alignItems="center"
              >
                  <MDTypography
                      component="a"
                      href="#"
                      variant="caption"
                      color="text"
                      fontWeight="medium"
                  >
                      Edit
                  </MDTypography>
                  <MDTypography
                      component="a"
                      href="#"
                      variant="caption"
                      color="text"
                      fontWeight="medium"
                  >
                      <Link to={'/utilisateurs/'+user?.id}>
                          &nbsp;&nbsp;&nbsp;Détail
                      </Link>
                  </MDTypography>
              </MDBox>
          ),
        }
      )),
  };
}
