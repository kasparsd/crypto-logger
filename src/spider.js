const axios = require('axios');
const createCsvWriter = require('csv-writer').createArrayCsvWriter;

const fiatSymbols = [
    "USD",
    "GBP",
    "JPY",
    "CHF",
    "EUR",
    "CAD",
    "AUD",
    "KRW",
    "RUB",
    "CNY",
    "ARS",
    "HKD",
    "INR",
    "SGD",
    "AED",
];

const csvFields = [
    'fromsymbol',
    'tosymbol',
    'price',
    'lastupdate',
    'lastvolume',
];

const apiUrl = `https://min-api.cryptocompare.com/data/pricemultifull?fsyms=ETH&tsyms=${fiatSymbols.join(',')}`;

const csvDate = new Date();

function csvFilename(date, symbolFrom, symbolTo) {
    const dateStamp = `${date.getFullYear()}${date.getMonth()+1}${date.getDate()}`;
    return `${dateStamp}-${symbolFrom}-${symbolTo}.csv`;
}

axios.get(apiUrl)
    .then(resp => {
        for (let refSymbol in resp.data.RAW.ETH) {
            const filename = csvFilename(csvDate, 'eth', refSymbol.toLowerCase());
            const rowData = csvFields.map(field => {
                return resp.data.RAW.ETH[refSymbol][field.toUpperCase()];
            });

            createCsvWriter({
                path: `data/${filename}`,
                header: csvFields,
                append: true,
            }).writeRecords([rowData]).catch((err) => {
                console.error('Failed to write records to the CSV file: ' + err);
            });
        }
    }).catch('error', err => {
        console.error( 'Failed to fetch data from the API: ' + err.message )
    });
