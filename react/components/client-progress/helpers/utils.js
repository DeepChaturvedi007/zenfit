import moment from "moment";

export const roundToDecimals = (number, decimals = 1) => {
  return Math.round( number * Math.pow(10, decimals) ) / Math.pow(10, decimals);
};

export const getHumanizedDifference = (d1 = new Date, d2 = new Date) => {
  const diff = Math.abs(d1.getTime() - d2.getTime());
  return moment.duration(diff).humanize() + ' ago';
};
