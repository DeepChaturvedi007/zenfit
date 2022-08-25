import moment from "moment";

export const FILTER_DEFAULT_PERIODS = (timeArray, periodInMonths, timeKey) => {
    const periodToSubstract = moment().subtract(periodInMonths,'months')
    let returnedArr;
    periodInMonths === 12
        ? returnedArr = timeArray
        : returnedArr = timeArray.filter(item => moment(item[timeKey]).isBetween(periodToSubstract, moment()))
    return returnedArr;
}
