import React, { useState, useEffect } from "react";
import "../../quotes/scss/quotes.scss";
import { initialInvoice, initialProductLine } from "../../quotes/data/initialData.js";
import EditableInput from "../../quotes/components/EditableInput.jsx";
import EditableSelect from "../../quotes/components/EditableSelect.jsx";
import EditableTextarea from "../../quotes/components/EditableTextarea.jsx";
import EditableCalendarInput from "../../quotes/components/EditableCalendarInput.jsx";
import countryList from "../../quotes/data/countryList.js";
import Document from "../../quotes/components/Document.jsx";
import Page from "../../quotes/components/Page.jsx";
import View from "../../quotes/components/View.jsx";
import Text from "../../quotes/components/Text.jsx";
import { Font } from "@react-pdf/renderer";
import format from "date-fns/format";

import axios from "axios";

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

const QuotePdf = ({ pdfMode,data }) => {
    const [formData, setFormData] = useState(null);
    useEffect(() => {
        // js get document.cookie value of token
        const token = document.cookie.split("=")[1];
        axios.get('/api/commercial/quotes/formdata', {
          headers: {
            'Authorization': 'Bearer ' + token
          }
        }).then(
            (response) => {
              setIsLoading(false);
              setFormData(response.data.formData);
            }
        )
      }, [])


  const [invoice, setInvoice] = useState(
    data ? { ...data } : { ...initialInvoice }
  );
  const [subTotal, setSubTotal] = useState(null);
  const [saleTax, setSaleTax] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const dateFormat = "MMM dd, yyyy";
  const invoiceDate =
    invoice.invoiceDate !== "" ? new Date(invoice.invoiceDate) : new Date();
  const invoiceDueDate =
    invoice.invoiceDueDate !== ""
      ? new Date(invoice.invoiceDueDate)
      : new Date(invoiceDate.valueOf());


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

  return (

        <Document pdfMode={pdfMode} className="mt-[100px]">
            {formData !== null &&
            <Page className="invoice-wrapper mt-[100px]" pdfMode={pdfMode}>
        
                  <>
              <View className="flex" pdfMode={pdfMode}>
                <View className="w-50" pdfMode={pdfMode}>
                  <EditableInput
                    className="fs-20 bold"
                    placeholder="Dev Studio"
                    value={data.companyName}
                    onChange={(value) => handleChange("companyName", value)}
                    pdfMode={pdfMode}
                  />
                   <EditableInput
                  
                    placeholder="Nom Commercial"
                    value={data.name}
                    onChange={(value) => handleChange("name", value)}
                    pdfMode={pdfMode}
                      disable
                  />
                  <EditableInput
                    
                    placeholder="77 Rue Rambuteau"
                    value={data.companyAddress}
                    onChange={(value) => handleChange("companyAddress", value)}
                    pdfMode={pdfMode}
                  />
                  <EditableInput
                  
                    placeholder="Paris, 75001"
                    value={data.companyAddress2}
                    onChange={(value) => handleChange("companyAddress2", value)}
                    pdfMode={pdfMode}
                  
                  />
                  <EditableSelect
         
                    options={countryList}
                      value={data.companyCountry}
                    onChange={(value) => handleChange("companyCountry", value)}
                    pdfMode={pdfMode}
                 
                  /> 
                </View>
                <View className="w-50" pdfMode={pdfMode}>
                  <h1 className="fs-45 text-black right bold uppercase">Devis</h1>
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

            
                  </>
            </Page>
            }
        </Document>
  );
};

export default QuotePdf;
