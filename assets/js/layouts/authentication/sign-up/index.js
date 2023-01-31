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

// @mui material components
import Card from "@mui/material/Card";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDInput from "components/MDInput";
import MDButton from "components/MDButton";
import InputLabel from "@mui/material/InputLabel";
import MenuItem from "@mui/material/MenuItem";
import FormControl from "@mui/material/FormControl";
import Select from "@mui/material/Select";
import NativeSelect from '@mui/material/NativeSelect';

// @mui material components
import Grid from "@mui/material/Grid";

// Material Dashboard 2 React example components
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";

function Cover() {
  const [token, setDecodedToken] = useState();

  console.log(token);

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
                <MDBox component="form" role="form">
                  <MDBox mb={2}>
                    <MDInput
                      type="text"
                      label="Nom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="text"
                      label="Prénom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="email"
                      label="Email"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>

                  <MDBox mb={2}>
                    <FormControl fullWidth>
                      <InputLabel variant="standard" htmlFor="uncontrolled-native">
                        Type
                      </InputLabel>
                      <NativeSelect
                        defaultValue={'commercial'}
                        inputProps={{
                          name: 'type',
                          id: 'uncontrolled-native',
                        }}
                      >
                        {token?.account === "admin" && (
                          <option value={'commercial'}>Commercial</option>
                        )}
                        {token?.account !== "admin" && (
                          <>
                            <option value={'client'}>Client</option>
                            <option value={'prospect'}>Prospect</option>
                          </>
                        )}
                      </NativeSelect>
                    </FormControl>
                  </MDBox>

                  <MDBox mb={2}>
                    <MDInput
                      type="tel"
                      label="Téléphone"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      type="textarea"
                      multiline
                      rows={5}
                      label="Description"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={8}>
                    <MDInput
                      type="password"
                      label="Mot de passe"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mt={4} mb={1}>
                    <MDButton variant="gradient" color="info" fullWidth>
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
