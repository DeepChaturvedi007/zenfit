export const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

export const transformToMoney = (number, currency = '') =>
  `${currency} ${number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;

export const roundToDecimals = (number, decimals = 1) =>
  Math.round( number * Math.pow(10, decimals) ) / Math.pow(10, decimals);

export const currencyCode = (currency) => {
  switch (currency) {
    case 'eur':
      return '€';
    case 'gbp':
      return '£';
    case 'usd':
      return '$';
    case 'nok':
    case 'dkk':
    case 'sek':
    default: {
      return `${currency ? currency : ''}`.toUpperCase();
    }
  }
};
