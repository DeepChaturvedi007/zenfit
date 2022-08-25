import React from 'react';
import axios from 'axios';

import {GET_DEFAULT_MESSAGES_LOCAL} from "../../../api/default-messages";
const Terms = (props) => {
    const {clientId, locale, terms, onChangeData} = props;
    const [defaultMessage, setDefaultMessage] = React.useState('');

    const termsSelect = (e) => {
        onChangeData(terms !== null ? null : defaultMessage, 'terms')
    }
    const changeTerms = (e) => {
        onChangeData(e.target.value, e.target.name);
    }
    React.useEffect(() => {
        axios.get(GET_DEFAULT_MESSAGES_LOCAL(14, clientId, locale))
            .then(({data}) => {
                if(data.defaultMessages.length !== 0){
                    setDefaultMessage(data.defaultMessages[data.defaultMessages.length-1].message)
                }
            })
    }, [])
    return (
        <div className="checkbox">
            <label>
                <input type="checkbox" id="terms" checked={terms !== null ? true : false} onChange={() => {termsSelect()}}/>
                Add Terms
            </label>
            {terms !== null && (
                <div className="row duration-time">
                    <div className="form-group">
                        <label htmlFor="exampleFormControlTextarea1">Terms</label>
                        <textarea className="form-control" name="terms" value={terms} onChange={changeTerms} rows={5}></textarea>
                    </div>
                </div>
            )}
        </div>
    )
}
export default Terms
