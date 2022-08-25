import React from 'react'

const Currency = (props) => {
    const { paymentCurrency, onChangeData, defaultCurrency } = props;

    const changeCurrency = (e) => {
        onChangeData(e.target.value, e.target.name)
    }
    return (
        <div className="row duration-time">
            <div className="col-sm-6">
                <label htmlFor="currency" className="control-label">Choose currency</label>
                <select
                    id="currency"
                    name="currency"
                    className="form-control"
                    defaultValue={paymentCurrency.toLowerCase()}
                    onChange={changeCurrency}
                >
                    <option value="usd">USD</option>
                    <option value="dkk">DKK</option>
                    <option value="nok">NOK</option>
                    <option value="eur">EURO</option>
                    <option value="gbp">GBP</option>
                    <option value="sek">SEK</option>
                </select>
            </div>
        </div>
    )
}

export default Currency;