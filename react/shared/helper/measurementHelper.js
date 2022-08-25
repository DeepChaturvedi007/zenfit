export const OneFeetInInches = 12;
export const roundToTwo = (val) => val !== 0 ? parseFloat(val).toFixed(2) : '';
export const roundToNone = (val) => val !== 0 ? parseFloat(val).toFixed(0) : '';
export const GetFeetFromInches = (inches) => (parseFloat(inches) / OneFeetInInches);
export const GetKGFromLBS = (lbs) => (parseFloat(lbs) / 2.2046);
export const isKgOrPound = (measureType) => measureType == 2 ? 'lbs' : 'kg';
export const GetCMFromFeetInches = (feet, inches) => Math.round((Math.floor(feet) + GetFeetFromInches(inches)) * 30.48)
export const GetFeetInchesFromCM = (cm) => {
    const feet = parseFloat(cm) / 30.48;
    const inches = roundToTwo((feet - parseInt(feet)) * OneFeetInInches)
    return {
        feet: parseInt(feet),
        inches: parseFloat(inches)
    }
}
