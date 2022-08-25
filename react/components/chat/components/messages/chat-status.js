import React, {Component} from 'react';
import moment from 'moment';
require('moment-precise-range-plugin');

export default class ChatStatus extends Component {
    STRINGS = {
        year: 'year',
        years: 'years',
        month: 'month',
        months: 'months',
        day: 'day',
        days: 'days',
        hour: 'hour',
        hours: 'hours',
        minute: 'minute',
        minutes: 'minutes',
        second: 'second',
        seconds: 'seconds',
        delimiter: ' '
    };

    constructor(props) {
        super(props);

        this.state = {
            currentTime: moment(new Date())
        };
    }

    componentDidMount() {
        setInterval(() => {
            this.setState({
                currentTime: moment(new Date())
            });
        }, 1000);
    }

    render() {
        const {
            lastMessageDate,
            emptyLastMessage
        } = this.props;
        const {
            currentTime
        } = this.state;

        let chatStatus = emptyLastMessage;
        if(lastMessageDate) {
            const {years, months, days, hours, minutes, seconds} = moment.preciseDiff(currentTime, lastMessageDate, true);
            let diffTime = this.pluralize(seconds, 'second') + ' ago';
            if(days >= 7) {
                diffTime = lastMessageDate.format("MMM DD, YYYY");
            } else if(minutes != 0) {
                diffTime = this.buildStringFromValues(years, months, days, hours, minutes) + ' ago';
            }
            chatStatus = <div className="chat-status">{`last message was sent ${diffTime}`}</div>;
        }

        return chatStatus;
    }

    pluralize(num, word) {
        return num + ' ' + this.STRINGS[word + (num === 1 ? '' : 's')];
    }

    buildStringFromValues(yDiff, mDiff, dDiff, hourDiff, minDiff){
        var result = [];

        if (yDiff) {
            result.push(this.pluralize(yDiff, 'year'));
        }
        if (mDiff) {
            result.push(this.pluralize(mDiff, 'month'));
        }
        if (dDiff) {
            result.push(this.pluralize(dDiff, 'day'));
        }
        if (hourDiff) {
            result.push(this.pluralize(hourDiff, 'hour'));
        }
        if (minDiff) {
            result.push(this.pluralize(minDiff, 'minute'));
        }

        return result.join(this.STRINGS.delimiter);
    }
}
