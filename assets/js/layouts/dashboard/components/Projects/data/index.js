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


// @mui material components
import Tooltip from "@mui/material/Tooltip";
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDAvatar from "components/MDAvatar";
import MDProgress from "components/MDProgress";

// Images
import team1 from "assets/images/team-1.jpg";
import team2 from "assets/images/team-2.jpg";
import team3 from "assets/images/team-3.jpg";
import team4 from "assets/images/team-4.jpg";

//api
import React, { useEffect, useState } from "react";
import axios from "axios";
import { Cookie } from "utils/index";

export default function data() {

  const [users, setUsers] = useState([]);

  const getAllUsers = () => {
    const token = Cookie.getCookie("token");

    axios.get(`http://localhost:8000/api/commercial-customers`, {
      headers: {
        "Authorization": `Bearer ${token}`,
      },
    })
      .then((response) => {
        const allUsers = response.data;
        setUsers(allUsers);
      })
      .catch((error) => console.log(error));
  }

  useEffect(() => {
    getAllUsers()
  }, []);

  const avatars = (members) =>
    members.map(([image, name]) => (
      <Tooltip key={name} title={name} placeholder="bottom">
        <MDAvatar
          src={image}
          alt="name"
          size="xs"
          sx={{
            border: ({ borders: { borderWidth }, palette: { white } }) =>
              `${borderWidth[2]} solid ${white.main}`,
            cursor: "pointer",
            position: "relative",

            "&:not(:first-of-type)": {
              ml: -1.25,
            },

            "&:hover, &:focus": {
              zIndex: "10",
            },
          }}
        />
      </Tooltip>
    ));

  const Company = ({ image, name, email }) => (
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

  return {
    columns: [
      {
        Header: "Clients",
        accessor: "companies",
        width: "45%",
        align: "left",
      },
      { Header: "members", accessor: "members", width: "10%", align: "left" },
      { Header: "Montant factures", accessor: "budget", align: "center" },
      { Header: "Total réglé", accessor: "completion", align: "center" },
    ],

    rows: 
    users && users.map(user => (
      {
        companies: <Company image={[team2, team3, team4][Math.floor(Math.random() * 3)]}  name={user.user.firstname + " " + user.user.lastname} 
        email={user.user.email} />,
        members: (
          <MDBox display="flex" py={1}>
            {avatars([
              [team1, "Ryan Tompson"],
              [team2, "Romina Hadid"],
              [team3, "Alexander Smith"],
              [team4, "Jessica Doe"],
            ])}
          </MDBox>
        ),
        budget: (
          <MDTypography variant="caption" color="text" fontWeight="medium">
            $14,000
          </MDTypography>
        ),
        completion: (
          <MDBox width="8rem" textAlign="left">
            <MDProgress
              value={60}
              color="info"
              variant="gradient"
              label={false}
            />
          </MDBox>
        ),
      }
    ))
  };
}
