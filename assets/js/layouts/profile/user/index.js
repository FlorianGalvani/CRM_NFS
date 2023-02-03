import React from "react";
import {Axios, Cookie} from "utils";
import jwt_decode from "jwt-decode";
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import DashboardNavbar from "examples/Navbars/DashboardNavbar";
import MDBox from "components/MDBox";
import Header from "layouts/profile/components/Header";
import Grid from "@mui/material/Grid";
import PlatformSettings from "layouts/profile/components/PlatformSettings";
import Divider from "@mui/material/Divider";
import ProfileInfoCard from "examples/Cards/InfoCards/ProfileInfoCard";
import ProfilesList from "examples/Lists/ProfilesList";
import profilesListData from "layouts/profile/data/profilesListData";
import MDTypography from "components/MDTypography";
import DefaultProjectCard from "examples/Cards/ProjectCards/DefaultProjectCard";
import homeDecor1 from "assets/images/home-decor-1.jpg";
import team1 from "assets/images/team-1.jpg";
import team2 from "assets/images/team-2.jpg";
import team3 from "assets/images/team-3.jpg";
import team4 from "assets/images/team-4.jpg";
import homeDecor2 from "assets/images/home-decor-2.jpg";
import homeDecor3 from "assets/images/home-decor-3.jpg";
import homeDecor4 from "assets/images/home-decor-4.jpeg";
import Footer from "examples/Footer";
import {useLocation} from "react-router-dom";
import UserHeader from "layouts/profile/user/header";
import moment from "moment";



function UserProfile() {
    const [token, setToken] = React.useState(null);
    const [account, setAccount] = React.useState(null);
    const [user, setUser] = React.useState(null);
    const [customerEvents, setCustomerEvents] = React.useState([]);

    const getUserFromCookie = () => {
        if(Cookie.getCookie("token") !== undefined) {
            const jwtToken = jwt_decode(Cookie.getCookie("token"));
            setToken(jwtToken);
        }
    }

    const location = useLocation();
    const pathnames = location.pathname.split('/');
    const id = pathnames[pathnames.length - 1];

    React.useEffect(() => {
        getUserFromCookie();

        const getUser = async () => {

            const response = await Axios.get('/user/'+id);

            if(response.error) {
                console.log(response.data)
            } else {
                setAccount(response.data);
                setUser(response.data.user);
                console.log()
                setCustomerEvents(response.data.events[0].events)
            }
        }

        getUser();
    },  [])

    const events = [];

    Object.entries((customerEvents)).map((e) => {
        const eventDate = moment(new Date(e[1].date)).locale('fr').format('DD/MM/YYYY HH:mm')
        events.push({value: getEventLabel(e[0]), date: eventDate})
    })

    return (
        <DashboardLayout>
            <DashboardNavbar />
            <MDBox mb={2} />
            <UserHeader username={account?.name} accountType={token?.account}>
                <MDBox mt={5} mb={3}>
                    <Grid container spacing={1}>
                        <Grid item xs={12} md={6} sx={{ display: "flex" }}>
                            <Divider orientation="vertical" sx={{ ml: -2, mr: 1 }} />
                            <ProfileInfoCard
                                title="A propos"
                                description={account?.about ?? ''}
                                info={{
                                    fullName: account?.name,
                                    mobile: user?.phone,
                                    email: user?.email,
                                    location: "France",
                                }}
                                social={[]}
                                action={{ route: "", tooltip: "email", icon: "email" }}
                                shadow={false}
                            />
                            <Divider orientation="vertical" sx={{ mx: 0 }} />
                        </Grid>
                        {
                            events.length > 0 ?
                                <Grid item xs={12} md={6}>
                                    <MDTypography
                                        variant="h6"
                                        fontWeight="medium"
                                        textTransform="capitalize"
                                    >
                                        Logs
                                    </MDTypography>
                                    <MDBox pt={2}>
                                        {
                                            events.map((event, index) => (
                                                <MDBox
                                                    key={index}
                                                    display="flex"
                                                    justifyContent="flex-start"
                                                    alignItems="center"
                                                >
                                                    <MDTypography variant="button" color="text" fontWeight="light">
                                                        {event.date} : &nbsp;&nbsp;&nbsp;
                                                    </MDTypography>
                                                    <MDTypography variant="button" color="text" fontWeight="medium">
                                                        {event.value}
                                                    </MDTypography>
                                                </MDBox>
                                            ))
                                        }
                                    </MDBox>
                                </Grid>
                                : null
                        }
                    </Grid>
                </MDBox>
            </UserHeader>
            <Footer />
        </DashboardLayout>
    );
}

export default UserProfile;
const getEventLabel = (e) => {
    let label = '';
    switch(e) {
        case 'prospect_created':
            label = 'Création de prospect';
            break;
        case 'customer_created':
            label = 'Inscription';
            break;
        case 'email_sent':
            label = 'Email envoyé';
            break;
        case 'meeting_customer_requested':
            label = 'Entretien demandé par le client';
            break;
        case 'meeting_commercial_requested':
            label = 'Prise de rendez-vous';
            break;
        case 'meeting':
            label = 'Entretien';
            break;
        case 'quotation_requested':
            label = 'Devis demandé par le client';
            break;
        case 'quotation_sent':
            label = 'Devis envoyé';
            break;
        case 'invoice_sent':
            label = 'Facture envoyé';
            break;
        case 'invoice_paid':
            label = 'Facture réglée';
            break;
        default:
            label = '';
    }
    return label;
}