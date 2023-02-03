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
import jwt_decode from "jwt-decode";
// Utils
import { Cookie } from "utils/index";
import axios from "axios";

// @mui material components
import Card from "@mui/material/Card";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDInput from "components/MDInput";
import MDButton from "components/MDButton";

// @mui material components
import Grid from "@mui/material/Grid";

// Material Dashboard 2 React example components
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import {Alert} from "@mui/material";
import {useNavigate} from "react-router-dom";

function Cover() {
  const [token, setDecodedToken] = useState();
  const [error, setError] = useState(null);

  const navigate = useNavigate();

  const decodedToken = () => {
    if (Cookie.getCookie("token") !== undefined) {
      const jwtToken = jwt_decode(Cookie.getCookie("token"));
      setDecodedToken(jwtToken);
    }
  };

  useEffect(() => {
    decodedToken();
  }, []);

  return (
    <DashboardLayout>
      <DashboardNavbar />
      <MDBox mt={10}>
        <Grid container spacing={1} justifyContent="center">
          <Grid item>
            <Card>
              <MDBox
                variant="gradient"
                bgColor="info"
                borderRadius="lg"
                coloredShadow="success"
                mx={2}
                mt={-3}
                p={3}
                mb={1}
                textAlign="center"
              >
                <MDTypography
                  variant="h4"
                  fontWeight="medium"
                  color="white"
                  mt={1}
                >
                  Enregistrer un nouveau compte
                </MDTypography>
                <MDTypography
                  display="block"
                  variant="button"
                  color="white"
                  my={1}
                >
                  Ajouter les coordonnées du nouveau compte
                </MDTypography>
              </MDBox>
              <MDBox pt={4} pb={3} px={3}>
                
                <MDBox component="form" role="form" {...{
                    onSubmit: (e) => {
                      e.preventDefault()
                      const data = new FormData(e.currentTarget)

                      const token = Cookie.getCookie("token")

                      const user = {
                        firstname: data.get('prenom'),
                        lastname: data.get('nom'),
                        email: data.get('email'),
                        phone: data.get('telephone'),
                        address: data.get('adresse'),
                        account: data.get('account')
                      }

                      const config = {
                        headers: {
                          'Authorization': `Bearer ${token}`,
                        }
                      }

                      const url = 'http://localhost:8000/api/signup';

                      console.log(url, user, config)

                      axios.post(url, user, config)
                        .then((response) => {
                          console.log(response);
                          navigate('/utilisateurs')
                        }, (error) => {
                          console.log(error);
                          setError(error.response.data.message);
                        });
                    }
                  }}>
                  {
                    error ?
                        <Alert severity={'error'}> {error} </Alert>
                        : null
                  }
                  <MDBox mb={2}>
                    <input type={'hidden'} name={'account'} value={token?.account === 'admin' ? 'commercial' : 'customer'}/>
                    <MDInput
                      type="text"
                      label="Nom"
                      name="nom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="text"
                      name="prenom"
                      label="Prénom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="email"
                      name="email"
                      label="Email"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="tel"
                      name="telephone"
                      label="Téléphone"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="text"
                      name="adresse"
                      label="Adresse"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mt={4} mb={1}>
                    <MDButton type="submit" variant="gradient" color="info" fullWidth>
                      Ajouter le compte
                    </MDButton>
                  </MDBox>
                </MDBox>
              </MDBox>
            </Card>
          </Grid>
        </Grid>
      </MDBox>
    </DashboardLayout>
  );
}

export default Cover;
