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
const QuotesPage = ({ pdfMode }) => {
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

  useEffect(() => {
    if (onInvoiceUpdated) {
      onInvoiceUpdated(invoice);
    }
  }, [onInvoiceUpdated, invoice]);

  const handleSaveQuotesButtonClick = () => {
    // {
    //     "customer": 108,
    //     "fileName": "Devis-01",
    //     "fileExtension": ".pdf",
    //     "data": {
    //     "customerFirstname": "John",
    //         "customerLastname": "Doe",
    //         "commercialFirstname": "Jane",
    //         "commercialLastname": "Die",
    //         "items": [
    //         {
    //             "name": "Objet 1",
    //             "price": 1225
    //         },
    //         {
    //             "name": "Objet 2",
    //             "price": 154
    //         }
    //     ]
    // }
    // }
  };

  return (
    <DashboardLayout>
      <div>
        <button
          className="text-white fixed z-10 bg-dark p-6 rounded"
          onClick={handleSaveQuotesButtonClick}
        >
          Sauvegarder
        </button>
      </div>
      <MDBox mt={8}>
        <Document pdfMode={pdfMode} className="mt-[100px]">
          <Page className="invoice-wrapper mt-[100px]" pdfMode={pdfMode}>
            {!pdfMode && <Download data={invoice} />}

            <View className="flex" pdfMode={pdfMode}>
              <View className="w-50" pdfMode={pdfMode}>
                <EditableFileImage
                  className="logo"
                  placeholder="Your Logo"
                  value={invoice.logo}
                  width={invoice.logoWidth}
                  pdfMode={pdfMode}
                  onChangeImage={(value) => handleChange("logo", value)}
                  onChangeWidth={(value) => handleChange("logoWidth", value)}
                />
                <EditableInput
                  className="fs-20 bold"
                  placeholder="Your Company"
                  value={invoice.companyName}
                  onChange={(value) => handleChange("companyName", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="Your Name"
                  value={invoice.name}
                  onChange={(value) => handleChange("name", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="Company's Address"
                  value={invoice.companyAddress}
                  onChange={(value) => handleChange("companyAddress", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="City, State Zip"
                  value={invoice.companyAddress2}
                  onChange={(value) => handleChange("companyAddress2", value)}
                  pdfMode={pdfMode}
                />
                <EditableSelect
                  options={countryList}
                  value={invoice.companyCountry}
                  onChange={(value) => handleChange("companyCountry", value)}
                  pdfMode={pdfMode}
                />
              </View>
              <View className="w-50" pdfMode={pdfMode}>
                <h1 className="fs-45 right bold uppercase">Devis</h1>
              </View>
            </View>

            <View className="flex mt-40" pdfMode={pdfMode}>
              <View className="w-55" pdfMode={pdfMode}>
                <EditableInput
                  className="bold dark mb-5"
                  value={invoice.billTo}
                  onChange={(value) => handleChange("billTo", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="Your Client's Name"
                  value={invoice.clientName}
                  onChange={(value) => handleChange("clientName", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="Client's Address"
                  value={invoice.clientAddress}
                  onChange={(value) => handleChange("clientAddress", value)}
                  pdfMode={pdfMode}
                />
                <EditableInput
                  placeholder="City, State Zip"
                  value={invoice.clientAddress2}
                  onChange={(value) => handleChange("clientAddress2", value)}
                  pdfMode={pdfMode}
                />
                <EditableSelect
                  options={countryList}
                  value={invoice.clientCountry}
                  onChange={(value) => handleChange("clientCountry", value)}
                  pdfMode={pdfMode}
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
                      onChange={(value) => handleChange("invoiceTitle", value)}
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
                      placeholder="Enter item name/description"
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
                      {calculateAmount(productLine.quantity, productLine.rate)}
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
                    Add Line Item
                  </button>
                )}
              </View>
              <View className="w-50 mt-20" pdfMode={pdfMode}>
                <View className="flex" pdfMode={pdfMode}>
                  <View className="w-50 p-5" pdfMode={pdfMode}>
                    <EditableInput
                      value={invoice.subTotalLabel}
                      onChange={(value) => handleChange("subTotalLabel", value)}
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
                    <EditableInput
                      className="dark bold right ml-30"
                      value={invoice.currency}
                      onChange={(value) => handleChange("currency", value)}
                      pdfMode={pdfMode}
                    />
                    <Text className="right bold dark w-auto" pdfMode={pdfMode}>
                      {(typeof subTotal !== "undefined" &&
                      typeof saleTax !== "undefined"
                        ? subTotal + saleTax
                        : 0
                      ).toFixed(2)}
                    </Text>
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
          </Page>
        </Document>
      </MDBox>
    </DashboardLayout>
  );
};

export default QuotesPage;
