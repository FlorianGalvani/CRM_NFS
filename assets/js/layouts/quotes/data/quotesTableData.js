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
import { Users } from "utils/index";
import jwt_decode from "jwt-decode";

// Material Dashboard 2 React components
import MDBox from "components/MDBox";
import MDTypography from "components/MDTypography";
import MDAvatar from "components/MDAvatar";
import MDBadge from "components/MDBadge";

export default function Data() {
    const [quotes, setQuotes] = useState([]);
    const [decodedToken, setDecodedToken] = useState(null);
    const getAllQuotes = () => {
        const token = Cookie.getCookie("token");
        if (Cookie.getCookie("token") !== undefined) {
            const jwtToken = jwt_decode(Cookie.getCookie("token"));
            console.log(jwtToken);
            setDecodedToken(jwtToken);
        }
        axios.get(`http://localhost:8000/api/quotes/list`, {
            headers: {
                "Authorization": `Bearer ${token}`,
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => {
                response.data.forEach((element) => {
                    element.data = JSON.parse(element.data);
                });
                console.log(response.data);
                setQuotes(response.data);
            })
            .catch((error) => console.log(error));
    }

    useEffect(() => {
        getAllQuotes();
    }, []);

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


    function timeConverter(UNIX_timestamp) {
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

    return {
        columns: [
            { Header: "user", accessor: "user", width: "45%", align: "left" },
            { Header: "date", accessor: "date", align: "left" },
            { Header: "dueDate", accessor: "dueDate", align: "left" },
        ],

        rows: quotes.length > 0 ? quotes.map((quote) => {
            console.log(quote)
            return {
                user: (
                    <User
                        name={decodedToken !== null && (decodedToken.account === 'commercial' || decodedToken.account === 'admin') ? quote.data.clientName : quote.data.name}
                    />
                ),
                date: timeConverter(Date.parse(quote.data.invoiceDate.date)),
                dueDate: timeConverter(Date.parse(quote.data.invoiceDueDate.date))
            }
        }) : []
    };
}