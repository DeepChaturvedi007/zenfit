/*jshint esversion: 6 */
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

export default {
    formatDate: (date, format) => {
        const o = {
            'M+': date.getMonth() + 1,
            'D+': date.getDate(),
            'h+': date.getHours(),
            'm+': date.getMinutes(),
            's+': date.getSeconds()
        };

        let str = format;

        if (/(Y+)/.test(format)) {
            str = str.replace(RegExp.$1, date.getFullYear().toString().substr(4 - RegExp.$1.length));
        }

        for (const k in o) {
            if (new RegExp('(' + k + ')').test(format)) {
                str = str.replace(RegExp.$1, RegExp.$1.length === 1 ? o[k] : ('00' + o[k]).substr(o[k].toString().length));
            }
        }

        return str;
    },
    getMonthName: (monthNum) => {
        return monthNames[monthNum];
    },
    getMonthNameShort: (monthNum) => {
        return monthNamesShort[monthNum];
    }
};
