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
import InputLabel from "@mui/material/InputLabel";
import FormControl from "@mui/material/FormControl";
import NativeSelect from '@mui/material/NativeSelect';

// @mui material components
import Grid from "@mui/material/Grid";

// Material Dashboard 2 React example components
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";

function Cover() {
  const [token, setDecodedToken] = useState();

  const decodedToken = () => {
    if (Cookie.getCookie("token") !== undefined) {
      const jwtToken = jwt_decode(Cookie.getCookie("token"));
      setDecodedToken(jwtToken);
    }
  };

  useEffect(() => {
    decodedToken();
  }, []);

  const [state, setState] = useState({
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    adresse: '',
  });

  const handleChange = (event) => {
    const value = event.target.value;
    setState({
      ...state,
      [event.target.name]: value,
    });
    console.log(state)
  };

  const handleSubmit = (event) => {
    event.preventDefault();

    const token = Cookie.getCookie("token");

    const data = new FormData(event.currentTarget);
    const roles = data.get('type');

    const user = {
      roles: roles,
      lastname: state.nom,
      firstname: state.prenom,
      email: state.email,
      phone: state.telephone,
      address: state.adresse,
    };

    const url = `http://localhost:8000/api/users`;

    const config = {
      headers: {
        Authorization: `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest'
      }
    }

    axios.post(url, user, config)
      .then(res => {
        console.log(res);
      }, (error) => {
        console.log(error);
      })
  };

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
                {/* {...{
                    onSubmit: (e) => {
                      e.preventDefault();
                      const data = new FormData(e.currentTarget);

                      const token = Cookie.getCookie("token");

                      const user = {
                        roles: data.get('type'),
                        firstname: data.get('prenom'),
                        lastname: data.get('nom'),
                        email: data.get('email'),
                        phone: data.get('telephone'),
                        address: data.get('adresse'),
                        password: data.get('password')
                      }

                      const config = {
                        headers: {
                          Authorization: `Bearer ${token}`
                        }
                      }

                      const url = 'http://localhost:8000/api/users';

                      axios.post(url, user, config)
                        .then((response) => {
                          console.log(response);
                        }, (error) => {
                          console.log(error);
                          console.log(token)
                        });
                    }
                  }} */}
                <MDBox component="form" role="form" onSubmit={handleSubmit}>
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
                      onChange={handleChange}
                      type="text"
                      label="Nom"
                      name="nom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      onChange={handleChange}
                      type="text"
                      name="prenom"
                      label="Prénom"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      onChange={handleChange}
                      type="email"
                      name="email"
                      label="Email"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      onChange={handleChange}
                      type="tel"
                      name="telephone"
                      label="Téléphone"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  <MDBox mb={2}>
                    <MDInput
                      onChange={handleChange}
                      type="text"
                      name="adresse"
                      label="Adresse"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox>
                  {/* <MDBox mb={2}>
                    <MDInput
                      type="textarea"
                      name="description"
                      multiline
                      rows={5}
                      label="Description"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox> */}
                  {/* <MDBox mb={8}>
                    <MDInput
                      onChange={handleChange}
                      type="password"
                      name="password"
                      label="Mot de passe"
                      variant="standard"
                      fullWidth
                    />
                  </MDBox> */}
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
