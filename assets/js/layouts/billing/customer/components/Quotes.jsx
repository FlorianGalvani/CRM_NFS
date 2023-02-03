import React from "react";
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import Card from "@mui/material/Card";
import PropTypes from "prop-types";
import {useMaterialUIController} from "context";
import MDButton from "components/MDButton";
import Icon from "@mui/material/Icon";
import {Link} from "react-router-dom";

const Quotes = ({quotes}) => {
    function getQuoteCommercial(quote) {
       return quote?.customer.commercial.user
    }

    return (
        <Card id="">
            <MDBox
                pt={2}
                px={2}
                display="flex"
                justifyContent="space-between"
                alignItems="center"
            >
                <MDTypography variant="h6" fontWeight="medium">
                    Devis
                </MDTypography>
                <Link to={'/transactions/mes-devis'}>
                    <MDButton variant="outlined" color="info" size="small">
                        voir tout
                    </MDButton>
                </Link>
            </MDBox>
            <MDBox pt={1} pb={2} px={2}>
                <MDBox component="ul" display="flex" flexDirection="column" p={0} m={0}>
                    {
                        quotes.map((quote, index) => (
                            <Bill
                                key={index}
                                name={quote?.fileName}
                                company={quote?.transaction?.type}
                                email={getQuoteCommercial(quote).email}
                                amount={quote?.transaction?.amount}
                                link={'/transactions/mes-devis/paiement/' + quote?.transaction?.id}
                            />
                        ))
                    }
                </MDBox>
            </MDBox>
        </Card>
    )
}

Quotes.propTypes = {
    quotes: PropTypes.array,
};

export default Quotes;

export function Bill({ name, company, email, noGutter, link, amount }) {
    const [controller] = useMaterialUIController();
    const { darkMode } = controller;

    return (
        <MDBox
            component="li"
            display="flex"
            justifyContent="space-between"
            alignItems="flex-start"
            bgColor={darkMode ? "transparent" : "grey-100"}
            borderRadius="lg"
            p={3}
            mb={noGutter ? 0 : 1}
            mt={2}
        >
            <MDBox width="100%" display="flex" flexDirection="column">
                <MDBox
                    display="flex"
                    justifyContent="space-between"
                    alignItems={{ xs: "flex-start", sm: "center" }}
                    flexDirection={{ xs: "column", sm: "row" }}
                    mb={2}
                >
                    <MDTypography
                        variant="button"
                        fontWeight="medium"
                        textTransform="capitalize"
                    >
                        {name}
                    </MDTypography>

                    <MDBox
                        display="flex"
                        alignItems="center"
                        mt={{ xs: 2, sm: 0 }}
                        ml={{ xs: -1.5, sm: 0 }}
                    >
                        <MDButton variant="text" color={darkMode ? "white" : "dark"}>
                            <Icon>download</Icon>&nbsp;Télecharger
                        </MDButton>
                        <Link to={link}>
                            <MDButton variant="text" color={darkMode ? "white" : "dark"}>
                                <Icon>payment</Icon>&nbsp;Paiement
                            </MDButton>
                        </Link>
                    </MDBox>
                </MDBox>
                <MDBox mb={1} lineHeight={0}>
                    <MDTypography variant="caption" color="text">
                        Object :&nbsp;&nbsp;&nbsp;
                        <MDTypography
                            variant="caption"
                            fontWeight="medium"
                            textTransform="capitalize"
                        >
                            {company}
                        </MDTypography>
                    </MDTypography>
                </MDBox>
                <MDBox mb={1} lineHeight={0}>
                    <MDTypography variant="caption" color="text">
                        Commercial :&nbsp;&nbsp;&nbsp;
                        <MDTypography variant="caption" fontWeight="medium">
                            {email}
                        </MDTypography>
                    </MDTypography>
                </MDBox>
                <MDBox mb={1} lineHeight={0}>
                    <MDTypography variant="caption" color="text">
                        Montant :&nbsp;&nbsp;&nbsp;
                        <MDTypography variant="caption" fontWeight="medium">
                            {amount} €
                        </MDTypography>
                    </MDTypography>
                </MDBox>
            </MDBox>
        </MDBox>
    );
}

// Setting default values for the props of Bill
Bill.defaultProps = {
    noGutter: false,
};

// Typechecking props for the Bill
Bill.propTypes = {
    name: PropTypes.string.isRequired,
    company: PropTypes.string.isRequired,
    email: PropTypes.string.isRequired,
    noGutter: PropTypes.bool,
    link: PropTypes.string,
    amount: PropTypes.string | PropTypes.number
};