export const roundToDecimals = (number, decimals = 1) =>
  Math.round( number * Math.pow(10, decimals) ) / Math.pow(10, decimals);


export const heightToFeetAndInches = (cm) => {
  const totalInches = +cm / 2.54;
  const feet = (totalInches - totalInches%12) / 12;
  const inches = totalInches % 12;
  return {
    feet: roundToDecimals(feet, 0),
    inches: roundToDecimals(inches, 0)
  }
};

export const inchesAndFeetToHeight = (inches, feet) => {
  const totalInches = (+feet * 12) + +inches;
  return roundToDecimals(+totalInches * 2.54, 0);
};

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
    default: {
      return `${currency ? currency : ''}`.toUpperCase();
    }
  }
};

export const normalizeText = (text) => {
  return text.replace(/(?:<br>|<br\/>|<br \/>)/gi, '\n');
};