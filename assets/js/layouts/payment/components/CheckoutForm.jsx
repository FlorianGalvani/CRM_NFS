import React, { useEffect, useState } from "react";
import {
    PaymentElement,
    useStripe,
    useElements
} from "@stripe/react-stripe-js";
import MDButton from "components/MDButton";
import MDBox from "components/MDBox";
import {Alert} from "@mui/material";
import axios from "axios";
import {useNavigate} from "react-router-dom";

export const CheckoutForm = ({token, transaction}) => {
    const stripe = useStripe();
    const elements = useElements();
    const [message, setMessage] = useState({
        data: '',
        success: false
    });
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();


    useEffect(() => {
        if (!stripe) {
            return;
        }

        const clientSecret = new URLSearchParams(window.location.search).get(
            "payment_intent_client_secret"
        );

        if (!clientSecret) {
            return;
        }

        stripe.retrievePaymentIntent(clientSecret).then(({ paymentIntent }) => {
            switch (paymentIntent.status) {
                case "succeeded":
                    setMessage({data: "Paiement effectué !", success: true});
                    break;
                case "processing":
                    setMessage({data: "Paiement en cours de vérification...", success: true});
                    break;
                case "requires_payment_method":
                    setMessage({data: "Le paiement a échoué, veuillez réessayer.", success: false});
                    break;
                default:
                    setMessage({data: "Une erreur est survenue.", success: false});
                    break;
            }
        });
    }, [stripe]);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        setIsLoading(true);

        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            redirect: 'if_required'
        });

        if ((error && error.type === "card_error") || (error && error.type === "validation_error")) {
            setMessage({data: error.message, success: false});
        } else if(error) {
            setMessage({data: "Une erreur est survenue.", success: false});
        } else {
            axios.get(`/api/payment_success/${transaction.id}?pm=${paymentIntent.payment_method}`, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }).then((res) => {
                navigate('/factures');
            }).catch((err) => {
                console.log(err)
                setMessage({data: "Une erreur est survenue.", success: false});
            })
        }

        setIsLoading(false);
    };

    const paymentElementOptions = {
        layout: "tabs"
    }

    return (
        <form
            id="payment-form"
            onSubmit={handleSubmit}
            style={{width: "75%", margin: "auto"}}
        >
            <MDBox mt={2}>
                <PaymentElement id="payment-element" options={paymentElementOptions} />
            </MDBox>
            <MDBox mt={2}>
                <MDButton
                    id="submit"
                    type="submit"
                    disabled={isLoading || !stripe || !elements}
                    variant="gradient"
                    color="info"
                >
                     <span id="button-text">
                      {isLoading ? <div className="spinner" id="spinner"></div> : "Payer"}
                    </span>
                </MDButton>
            </MDBox>
            {/* Show any error or success messages */}
            <MDBox mt={2}>
                {message.data && <Alert severity={message.success ? 'success' : 'error'} id="payment-message">{message.data}</Alert>}
            </MDBox>
        </form>
    );
}