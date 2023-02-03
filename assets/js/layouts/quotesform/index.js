import React, { useState, useEffect } from "react";
import "./scss/quotes.scss";
import { initialInvoice, initialProductLine } from "./data/initialData.js";
import EditableInput from "./components/EditableInput.jsx";
import EditableSelect from "./components/EditableSelect.jsx";
import EditableTextarea from "./components/EditableTextarea.jsx";
import EditableCalendarInput from "./components/EditableCalendarInput.jsx";
import EditableFileImage from "./components/EditableFileImage.jsx";
import countryList from "./data/countryList.js";
import Document from "./components/Document.jsx";
import Page from "./components/Page.jsx";
import View from "./components/View.jsx";
import Text from "./components/Text.jsx";
import { Font } from "@react-pdf/renderer";
import Download from "./components/DownloadPDF.jsx";
import format from "date-fns/format";
import DashboardLayout from "examples/LayoutContainers/DashboardLayout";
import MDBox from "components/MDBox";
import DashboardNavbar from "../../examples/Navbars/DashboardNavbar";
import TextField from '@mui/material/TextField';
import Autocomplete from '@mui/material/Autocomplete';
import axios from "axios";
import {useNavigate} from "react-router-dom";

Font.register({
  family: "Nunito",
  fonts: [
    { src: "https://fonts.gstatic.com/s/nunito/v12/XRXV3I6Li01BKofINeaE.ttf" },
    {
      src: "https://fonts.gstatic.com/s/nunito/v12/XRXW3I6Li01BKofA6sKUYevN.ttf",
      fontWeight: 600,
    },
  ],
});
const QuotesFormPage = ({ pdfMode }) => {
  const savedInvoice = window.localStorage.getItem("invoiceData");
  let data = null;
  try {
    if (savedInvoice) {
      data = JSON.parse(savedInvoice);
    }
  } catch (e) {
    console.log(e);
  }

  const onInvoiceUpdated = (invoice) => {
    window.localStorage.setItem("invoiceData", JSON.stringify(invoice));
  };

  const [invoice, setInvoice] = useState(
    data ? { ...data } : { ...initialInvoice }
  );
  const [subTotal, setSubTotal] = useState(null);
  const [saleTax, setSaleTax] = useState(null);
  const [formData, setFormData] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const dateFormat = "MMM dd, yyyy";
  const invoiceDate =
    invoice.invoiceDate !== "" ? new Date(invoice.invoiceDate) : new Date();
  const invoiceDueDate =
    invoice.invoiceDueDate !== ""
      ? new Date(invoice.invoiceDueDate)
      : new Date(invoiceDate.valueOf());

  useEffect(() => {
    // js get document.cookie value of token
    const token = document.cookie.split("=")[1];
    axios.get('/api/commercial/quotes/formdata', {
      headers: {
        'Authorization': 'Bearer ' + token
      }
    }).then(
        (response) => {
          const data = response.data.formData;
          handleChange("name",data.commercial.firstname + data.commercial.lastname)
          handleChange("companyName", data.companyName)
          handleChange("companyAddress", data.companyAddress)
          handleChange("companyAddress2", data.companyAddress2)
          handleChange("companyCountry", data.companyCountry)
          setFormData(data);
          setIsLoading(false);   
        }
    )
  }, [])

  if (invoice.invoiceDueDate === "") {
    invoiceDueDate.setDate(invoiceDueDate.getDate() + 30);
  }

  const handleChange = (name, value) => {
    if (name !== "productLines") {
      const newInvoice = { ...invoice };

      if (name === "logoWidth" && typeof value === "number") {
        newInvoice[name] = value;
      } else if (name !== "logoWidth" && typeof value === "string") {
        newInvoice[name] = value;
      }

      setInvoice(newInvoice);
    }
  };

  const handleProductLineChange = (index, name, value) => {
    const productLines = invoice.productLines.map((productLine, i) => {
      if (i === index) {
        const newProductLine = { ...productLine };

        if (name === "description") {
          newProductLine[name] = value;
        } else {
          if (
            value[value.length - 1] === "." ||
            (value[value.length - 1] === "0" && value.includes("."))
          ) {
            newProductLine[name] = value;
          } else {
            const n = parseFloat(value);

            newProductLine[name] = (n ? n : 0).toString();
          }
        }

        return newProductLine;
      }

      return { ...productLine };
    });

    setInvoice({ ...invoice, productLines });
  };

  const handleRemove = (i) => {
    const productLines = invoice.productLines.filter(
      (productLine, index) => index !== i
    );

    setInvoice({ ...invoice, productLines });
  };

  const handleAdd = () => {
    const productLines = [...invoice.productLines, { ...initialProductLine }];

    setInvoice({ ...invoice, productLines });
  };

  const calculateAmount = (quantity, rate) => {
    const quantityNumber = parseFloat(quantity);
    const rateNumber = parseFloat(rate);
    const amount =
      quantityNumber && rateNumber ? quantityNumber * rateNumber : 0;

    return amount.toFixed(2);
  };

  useEffect(() => {
    let subTotal = 0;

    invoice.productLines.forEach((productLine) => {
      const quantityNumber = parseFloat(productLine.quantity);
      const rateNumber = parseFloat(productLine.rate);
      const amount =
        quantityNumber && rateNumber ? quantityNumber * rateNumber : 0;

      subTotal += amount;
    });

    setSubTotal(subTotal);
  }, [invoice.productLines]);

  useEffect(() => {
    const match = invoice.taxLabel.match(/(\d+)%/);
    const taxRate = match ? parseFloat(match[1]) : 0;
    const saleTax = subTotal ? (subTotal * taxRate) / 100 : 0;

    setSaleTax(saleTax);
  }, [subTotal, invoice.taxLabel]);

  useEffect(() => {
    if (onInvoiceUpdated) {
      onInvoiceUpdated(invoice);
    }
  }, [onInvoiceUpdated, invoice]);

  const navigate = useNavigate();

  const handleCustomerSelectChange = (event, value) => {
    formData.customers.forEach((customer) => {
      if (customer.data.name === value) {
        setInvoice({
          ...invoice,
          clientName: customer.data.name,
          clientAddress: customer.data.address,
          clientAddress2: customer.data.city + ", " + customer.data.zip,
          clientCountry: customer.data.country,
        });
      }
    })
  }

  const handleSaveQuotesButtonClick = () => {

    invoice["name"] = formData.commercial.firstname + ' ' +  formData.commercial.lastname;
    invoice["companyName"] = formData.company.name;
    invoice["companyAddress"] = formData.company.address;
    invoice["companyAddress2"] = formData.company.city + ", " + formData.company.zipCode;
    console.log("invoice",invoice);
    const token = document.cookie.split("=")[1];
    const customer = formData.customers.find((customer) => customer.data.name === invoice.clientName);
    console.log("customer",customer);
    axios.post('/api/commercial/quotes/new', {
      invoice,
      subTotal,
      saleTax,
      customer: customer.data.id
    },{
      headers: {
        'Authorization': 'Bearer ' + token,
        'X-Requested-With': 'XMLHttpRequest'
      },

    } ).then(
        (response) => {
          console.log(response);
          navigate('/devis')
        }
    )
  };

  return (
    <DashboardLayout>
      <DashboardNavbar />
      <MDBox mt={8}>
        <Document pdfMode={pdfMode} className="mt-[100px]">
          <div className="wrap">
            <Page className="invoice-wrapper mt-[100px]" pdfMode={pdfMode}>
              {!pdfMode && <Download data={invoice} />}

              {!isLoading &&
                  <>
              <View className="flex" pdfMode={pdfMode}>
                <View className="w-50" pdfMode={pdfMode}>
                  {/* <EditableFileImage
                      className="logo"
                      placeholder="Your Logo"
                      value={invoice.logo}
                      width={invoice.logoWidth}
                      pdfMode={pdfMode}
                      onChangeImage={(value) => handleChange("logo", value)}
                      onChangeWidth={(value) => handleChange("logoWidth", value)}
                  /> */}
                  <EditableInput
                    className="fs-20 bold disabled"
                    placeholder="Dev Studio"
                    value={formData.company.name}
                    onChange={(value) => handleChange("companyName", value)}
                    pdfMode={pdfMode}
                    disable
                  />
                  <EditableInput
                   
                    placeholder="Nom Commercial"
                    value={formData.commercial.firstname + " " + formData.commercial.lastname}
                    onChange={(value) => handleChange("name", value)}
                    pdfMode={pdfMode}

                  />
                  <EditableInput
                      className="disabled"
                    placeholder="77 Rue Rambuteau"
                    value={formData.company.address}
                    onChange={(value) => handleChange("companyAddress", value)}
                    pdfMode={pdfMode}
                  />
                  <EditableInput
                      className="disabled"
                    placeholder="Paris, 75001"
                    value={formData.company.city +", " + formData.company.zipCode}
                    onChange={(value) => handleChange("companyAddress2", value)}
                    pdfMode={pdfMode}
                    disabled
                  />
                  <EditableSelect
                      className="disabled"
                    options={countryList}
                      value={formData.company.country}
                    onChange={(value) => handleChange("companyCountry", value)}
                    pdfMode={pdfMode}
                      disabled
                  />
                </View>
                <View className="w-50" pdfMode={pdfMode}>
                  <h1 className="fs-45 text-yellow right bold uppercase">Devis</h1>
                </View>
              </View>

              <View className="flex mt-40" pdfMode={pdfMode}>
                <View className="w-55" pdfMode={pdfMode}>

                  <EditableInput
                    className="bold dark mb-5 disabled"
                    value={invoice.billTo}
                    onChange={(value) => handleChange("billTo", value)}
                    pdfMode={pdfMode}
                    disabled
                  />

                  <Autocomplete
                      disablePortal
                      id="combo-box-demo"
                      options={formData.customersLabels}
                      onChange={handleCustomerSelectChange}
                      sx={{ width: 300 }}
                      renderInput={(params) => <TextField {...params} label="Client" />}
                  />

                  <EditableInput
                      className="disabled"
                    placeholder="Nom Client"
                    value={invoice.clientName}
                    onChange={(value) => handleChange("clientName", value)}
                    pdfMode={pdfMode}
                      disabled
                  />

                  <EditableInput
                      className="disabled"
                    placeholder="Adresse Client"
                    value={invoice.clientAddress}
                    onChange={(value) => handleChange("clientAddress", value)}
                    pdfMode={pdfMode}
                      disabled
                  />
                  <EditableInput
                      className="disabled"
                    placeholder="Ville, Code Postal"
                    value={invoice.clientAddress2}
                    onChange={(value) => handleChange("clientAddress2", value)}
                    pdfMode={pdfMode}
                      disabled
                  />
                  <EditableSelect
                      className="disabled"
                    options={countryList}
                    value={invoice.clientCountry}
                    onChange={(value) => handleChange("clientCountry", value)}
                    pdfMode={pdfMode}
                      disabled
                  />
                </View>
                <View className="w-45" pdfMode={pdfMode}>
                  <View className="flex mb-5" pdfMode={pdfMode}>
                    <View className="w-40" pdfMode={pdfMode}>
                      <EditableInput
                        className="bold"
                        value={invoice.invoiceTitleLabel}
                        onChange={(value) =>
                          handleChange("invoiceTitleLabel", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-60" pdfMode={pdfMode}>
                      <EditableInput
                        placeholder="INV-12"
                        value={invoice.invoiceTitle}
                        onChange={(value) =>
                          handleChange("invoiceTitle", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                  </View>
                  <View className="flex mb-5" pdfMode={pdfMode}>
                    <View className="w-40" pdfMode={pdfMode}>
                      <EditableInput
                        className="bold"
                        value={invoice.invoiceDateLabel}
                        onChange={(value) =>
                          handleChange("invoiceDateLabel", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-60" pdfMode={pdfMode}>
                      <EditableCalendarInput
                        value={format(invoiceDate, dateFormat)}
                        selected={invoiceDate}
                        onChange={(date) =>
                          handleChange(
                            "invoiceDate",
                            date && !Array.isArray(date)
                              ? format(date, dateFormat)
                              : ""
                          )
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                  </View>
                  <View className="flex mb-5" pdfMode={pdfMode}>
                    <View className="w-40" pdfMode={pdfMode}>
                      <EditableInput
                        className="bold"
                        value={invoice.invoiceDueDateLabel}
                        onChange={(value) =>
                          handleChange("invoiceDueDateLabel", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-60" pdfMode={pdfMode}>
                      <EditableCalendarInput
                        value={format(invoiceDueDate, dateFormat)}
                        selected={invoiceDueDate}
                        onChange={(date) =>
                          handleChange(
                            "invoiceDueDate",
                            date && !Array.isArray(date)
                              ? format(date, dateFormat)
                              : ""
                          )
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                  </View>
                </View>
              </View>

              <View className="mt-30 bg-dark flex" pdfMode={pdfMode}>
                <View className="w-48 p-4-8" pdfMode={pdfMode}>
                  <EditableInput
                    className="white bold"
                    value={invoice.productLineDescription}
                    onChange={(value) =>
                      handleChange("productLineDescription", value)
                    }
                    pdfMode={pdfMode}
                  />
                </View>
                <View className="w-17 p-4-8" pdfMode={pdfMode}>
                  <EditableInput
                    className="white bold right"
                    value={invoice.productLineQuantity}
                    onChange={(value) =>
                      handleChange("productLineQuantity", value)
                    }
                    pdfMode={pdfMode}
                  />
                </View>
                <View className="w-17 p-4-8" pdfMode={pdfMode}>
                  <EditableInput
                    className="white bold right"
                    value={invoice.productLineQuantityRate}
                    onChange={(value) =>
                      handleChange("productLineQuantityRate", value)
                    }
                    pdfMode={pdfMode}
                  />
                </View>
                <View className="w-18 p-4-8" pdfMode={pdfMode}>
                  <EditableInput
                    className="white bold right"
                    value={invoice.productLineQuantityAmount}
                    onChange={(value) =>
                      handleChange("productLineQuantityAmount", value)
                    }
                    pdfMode={pdfMode}
                  />
                </View>
              </View>

              {invoice.productLines.map((productLine, i) => {
                return pdfMode && productLine.description === "" ? (
                  <Text key={i}></Text>
                ) : (
                  <View key={i} className="row flex" pdfMode={pdfMode}>
                    <View className="w-48 p-4-8 pb-10" pdfMode={pdfMode}>
                      <EditableTextarea
                        className="dark"
                        rows={2}
                        placeholder="Saisissez une description"
                        value={productLine.description}
                        onChange={(value) =>
                          handleProductLineChange(i, "description", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-17 p-4-8 pb-10" pdfMode={pdfMode}>
                      <EditableInput
                        className="dark right"
                        value={productLine.quantity}
                        onChange={(value) =>
                          handleProductLineChange(i, "quantity", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-17 p-4-8 pb-10" pdfMode={pdfMode}>
                      <EditableInput
                        className="dark right"
                        value={productLine.rate}
                        onChange={(value) =>
                          handleProductLineChange(i, "rate", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-18 p-4-8 pb-10" pdfMode={pdfMode}>
                      <Text className="dark right" pdfMode={pdfMode}>
                        {calculateAmount(
                          productLine.quantity,
                          productLine.rate
                        )}
                      </Text>
                    </View>
                    {!pdfMode && (
                      <button
                        className="link row__remove"
                        aria-label="Remove Row"
                        title="Remove Row"
                        onClick={() => handleRemove(i)}
                      >
                        <span className="icon icon-remove bg-red"></span>
                      </button>
                    )}
                  </View>
                );
              })}

              <View className="flex" pdfMode={pdfMode}>
                <View className="w-50 mt-10" pdfMode={pdfMode}>
                  {!pdfMode && (
                    <button className="link" onClick={handleAdd}>
                      <span className="icon icon-add bg-green mr-10"></span>
                      Ajouter une ligne
                    </button>
                  )}
                </View>
                <View className="w-50 mt-20" pdfMode={pdfMode}>
                  <View className="flex" pdfMode={pdfMode}>
                    <View className="w-50 p-5" pdfMode={pdfMode}>
                      <EditableInput
                        value={invoice.subTotalLabel}
                        onChange={(value) =>
                          handleChange("subTotalLabel", value)
                        }
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-50 p-5" pdfMode={pdfMode}>
                      <Text className="right bold dark" pdfMode={pdfMode}>
                        {subTotal?.toFixed(2)}
                      </Text>
                    </View>
                  </View>
                  <View className="flex" pdfMode={pdfMode}>
                    <View className="w-50 p-5" pdfMode={pdfMode}>
                      <EditableInput
                        value={invoice.taxLabel}
                        onChange={(value) => handleChange("taxLabel", value)}
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-50 p-5" pdfMode={pdfMode}>
                      <Text className="right bold dark" pdfMode={pdfMode}>
                        {saleTax?.toFixed(2)}
                      </Text>
                    </View>
                  </View>
                  <View className="flex bg-gray p-5" pdfMode={pdfMode}>
                    <View className="w-50 p-5" pdfMode={pdfMode}>
                      <EditableInput
                        className="bold"
                        value={invoice.totalLabel}
                        onChange={(value) => handleChange("totalLabel", value)}
                        pdfMode={pdfMode}
                      />
                    </View>
                    <View className="w-50 p-5 flex" pdfMode={pdfMode}>
                      <Text
                        className="right bold dark w-auto"
                        pdfMode={pdfMode}
                      >
                        {(typeof subTotal !== "undefined" &&
                        typeof saleTax !== "undefined"
                          ? subTotal + saleTax
                          : 0
                        ).toFixed(2)}
                      </Text>
                      <EditableInput
                        className="dark bold"
                        value={invoice.currency}
                        onChange={(value) => handleChange("currency", value)}
                        pdfMode={pdfMode}
                      />
                    </View>
                  </View>
                </View>
              </View>

              <View className="mt-20" pdfMode={pdfMode}>
                <EditableInput
                  className="bold w-100"
                  value={invoice.notesLabel}
                  onChange={(value) => handleChange("notesLabel", value)}
                  pdfMode={pdfMode}
                />
                <EditableTextarea
                  className="w-100"
                  rows={2}
                  value={invoice.notes}
                  onChange={(value) => handleChange("notes", value)}
                  pdfMode={pdfMode}
                />
              </View>
              <View className="mt-20" pdfMode={pdfMode}>
                <EditableInput
                  className="bold w-100"
                  value={invoice.termLabel}
                  onChange={(value) => handleChange("termLabel", value)}
                  pdfMode={pdfMode}
                />
                <EditableTextarea
                  className="w-100"
                  rows={2}
                  value={invoice.term}
                  onChange={(value) => handleChange("term", value)}
                  pdfMode={pdfMode}
                />
              </View>

              <div className="saveButton">
                <button onClick={handleSaveQuotesButtonClick}>
                  Enregistrer le devis
                </button>
              </div>
                  </>
              }
              <div className="contentConditions">
                <span className="conditions">
                  Conditions et modalités:
                </span>
                <p>
                Le délai de réglement et de 30 jours, pas descompte pour le paiement anticipé.
                    En cas de retard de paiement, applications des interet de retard en vigeur
                </p>
                <p>
                <span className="conditions">Sarl</span> au capital de 90 120,00€ <span className="conditions">SIRET:</span>6598956<span className="conditions"> NII(TVA intracommunautaire):</span>FR 70 556 216 941 <span className="conditions">Code APE:</span> 735K
                </p>
              </div>
            </Page>
          </div>
        </Document>
      </MDBox>
    </DashboardLayout>
  );
};

export default QuotesFormPage;
