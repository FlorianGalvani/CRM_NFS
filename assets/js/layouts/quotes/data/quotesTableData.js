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
import { Cookie } from "utils/index";
import jwt_decode from "jwt-decode";

// @mui material components
import Icon from "@mui/material/Icon";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import moment from "moment/moment";

export default function Data() {
    const [quotes, setQuotes] = useState([]);

    const [decodedToken, setDecodedToken] = useState(null);

    const token = Cookie.getCookie("token");

    const getAllQuotes = () => {
        if (Cookie.getCookie("token") !== undefined) {
            const jwtToken = jwt_decode(Cookie.getCookie("token"));

            setDecodedToken(jwtToken);
        }
        axios.get(`http://localhost:8000/api/quotes/list`, {
            headers: {
                "Authorization": `Bearer ${token}`,
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => {
                setQuotes(response.data);
            })
            .catch((error) => console.log(error));
    }

    useEffect(() => {
        getAllQuotes();
    }, []);

    const deleteQuotes = (id) => {
        axios.delete(`http://localhost:8000/api/documents/${id}`, {
            headers: {
                "Authorization": `Bearer ${token}`,
            },
        })
            .then((response) => {
                getAllQuotes();
            })
            .catch((error) => console.log(error));
    }

    const User = ({ image, name, email }) => (
        <MDBox display="flex" alignItems="center" lineHeight={1}>
            {/* <MDAvatar src={image} name={name} size="sm" /> */}
            <MDBox ml={2} lineHeight={1}>
                <MDTypography display="block" variant="button" fontWeight="medium">
                    {name}
                </MDTypography>
                <MDTypography variant="caption">{email}</MDTypography>
            </MDBox>
        </MDBox>
    );

    const calculateAmount = (quantity, rate) => {
        const quantityNumber = parseFloat(quantity);
        const rateNumber = parseFloat(rate);
        const amount =
            quantityNumber && rateNumber ? quantityNumber * rateNumber : 0;

        return amount.toFixed(2);
    };


    function timeConverter(UNIX_timestamp) {
        console.log(UNIX_timestamp)
        var a = new Date(UNIX_timestamp);
        var months = ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        var time = (date > 9 ? date : '0' + date) + ' ' + month + ' ' + year + ' ' + (hour > 9 ? hour : '0' + hour) + ':' + (min > 9 ? min : '0' + min) + ':' + (sec > 9 ? sec : '0' + sec);
        return time;
    }

    function momentConvert(date) {
        return moment(new Date(date)).locale('fr').format('LLL')
    }

    return {
        columns: [
            { Header: decodedToken !== null && (decodedToken.account === 'commercial' || decodedToken.account === 'admin') ? 'clients' : 'commercials', accessor: "user", width: "30%", align: "left" },
            { Header: "date", accessor: "date", align: "center" },
            { Header: "prix", accessor: "price", align: "center" },
            { Header: "échéance", accessor: "dueDate", align: "center" },
            { Header: "Supprimer", accessor: "action", align: "center" },
        ],

        rows: quotes.length > 0 ? quotes.map((quote) => {
            return {
                user: (
                    <User
                        name={decodedToken !== null && (decodedToken.account === 'commercial' || decodedToken.account === 'admin') ? quote.data.clientName : quote.data.name}
                    />
                ),
                // date: timeConverter(Date.parse(quote.data.invoiceDate.date)),
                date: momentConvert(quote.data.invoiceDate.date ?? quote.data.invoiceDate),
                // dueDate: timeConverter(Date.parse(quote.data.invoiceDueDate.date)),
                dueDate: momentConvert(quote.data.invoiceDueDate.date ?? quote.data.invoiceDueDate),
                price: (
                    <MDTypography
                        component="a"
                        href="#"
                        variant="caption"
                        color="text"
                        fontWeight="medium"
                    >
                        {quote.transaction?.amount} € HT
                        {/*{quote.data.productLines.map((productLine) => {*/}
                        {/*    return calculateAmount(productLine.quantity, productLine.rate);*/}
                        {/*})}*/}
                    </MDTypography>
                ),
                action: (
                    <MDBox display="flex" justifyContent="center">
                        <MDBox
                            component="a"
                            href="#"
                            color="text"
                            mr={1}
                            onClick={() => console.log(quote)}
                        >
                            <Icon fontSize="small">delete</Icon>
                        </MDBox>
                    </MDBox>
                )
            }
        }) : []
    };
}