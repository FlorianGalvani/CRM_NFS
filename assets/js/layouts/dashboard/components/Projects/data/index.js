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
import React, { useEffect, useState } from "react";
import axios from "axios";
import { Cookie } from "utils/index";

// @mui material components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDAvatar from "components/MDAvatar";

// Images
import team1 from "assets/images/team-1.jpg";
import team2 from "assets/images/team-2.jpg";
import team3 from "assets/images/team-3.jpg";
import team4 from "assets/images/team-4.jpg";
import team5 from "assets/images/team-5.jpg";

export default function data() {
  const [prospects, setProspects] = useState([]);
  const token = Cookie.getCookie("token");

  const getAllProspects = () => {
    axios.get(`http://localhost:8000/api/all-prospects`, {
      headers: {
        "Authorization": `Bearer ${token}`,
      },
    })
      .then((response) => {
        const allProspects = response.data;
        setProspects(allProspects);
      })
      .catch((error) => console.log(error));
  }

  useEffect(() => {
    getAllProspects()
  }, []);

  const Prospect = ({ image, name }) => (
    <MDBox display="flex" alignItems="center" lineHeight={1}>
      <MDAvatar src={image} name={name} size="sm" variant="rounded" />
      <MDTypography
        display="block"
        variant="button"
        fontWeight="medium"
        ml={1}
        lineHeight={1}
      >
        {name}
      </MDTypography>
    </MDBox>
  );

  return {
    columns: [
      { Header: "prospect", accessor: "prospects", width: "30%", align: "left" },
      { Header: "téléphone", accessor: "phone", align: "left" },
      { Header: "email", accessor: "email", align: "center" }
    ],

    rows:
      prospects.map((prospect) => (
        {
          prospects: <Prospect image={[team1, team2, team3, team4, team5][Math.floor(Math.random() * 5)]} name={prospect.firstname + ' ' + prospect.lastname} />,
          phone: (
            <MDTypography
              component="a"
              href="#"
              variant="button"
              color="text"
              fontWeight="medium"
            >
              {prospect.phone}
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
              {prospect.email}
            </MDTypography>
          ),
        }
      ))
  };
}
