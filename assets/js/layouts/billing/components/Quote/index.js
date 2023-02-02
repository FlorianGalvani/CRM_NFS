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

// prop-types is a library for typechecking of props
import PropTypes from "prop-types";

// @mui material components
import Icon from "@mui/material/Icon";
import DownloadIcon from '@mui/icons-material/Download';

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import { PDFDownloadLink } from '@react-pdf/renderer';
import QuotePdf from "layouts/pdf/Quotes";
function Quote({ date, customer, price, noGutter, pdfData, formData }) {
    return (
        <MDBox
            component="li"
            display="flex"
            justifyContent="space-between"
            alignItems="center"
            py={1}
            pr={1}
            mb={noGutter ? 0 : 1}
        >
            <MDBox lineHeight={1.125}>
                <MDTypography display="block" variant="button" fontWeight="medium">
                    {customer}
                </MDTypography>
                <MDTypography variant="caption" fontWeight="regular" color="text">
                    {date}
                </MDTypography>
            </MDBox>
            <MDBox display="flex" alignItems="center">
                <MDTypography variant="button" fontWeight="regular" color="text">
                    {price}
                </MDTypography>
                <MDBox
                    display="flex"
                    alignItems="center"
                    lineHeight={1}
                    ml={3}
                    sx={{ cursor: "pointer" }}
                >
                    {
                        pdfData !== null &&
                        <PDFDownloadLink
                            document={<QuotePdf formData={formData} pdfMode={true} data={pdfData} />}
                            fileName={`${pdfData.invoiceTitle ? pdfData.invoiceTitle.toLowerCase() : 'invoice'}.pdf`}
                            aria-label="Save PDF"
                        
                        >
                          <Icon sx={{ fontWeight: "bold" , marginTop: 1 }} size="small" style={{ color: 'black' }}>download</Icon>
                        </PDFDownloadLink>
                    }
                </MDBox>
            </MDBox>
        </MDBox>
    );
}

// Setting default values for the props of Invoice
Quote.defaultProps = {
    noGutter: false,
};

// Typechecking props for the Invoice
Quote.propTypes = {
    date: PropTypes.string.isRequired,
    customer: PropTypes.string.isRequired,
    price: PropTypes.string.isRequired,
    noGutter: PropTypes.bool,
    pdfData: PropTypes.object.isRequired,
};

export default Quote;
