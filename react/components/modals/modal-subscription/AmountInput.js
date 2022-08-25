import React from 'react';

const AmountInput = (props) => {
    const { upfrontFee, monthlyAmount, months, onChangeData, defaultRecurring, defaultUpfront } = props;

    const handleChange = (e) => {
        onChangeData(e.target.value, e.target.name);
    }
    return (
        <div className="row duration-time top-padding">
            <div className="col-sm-6 col-upfront-fee">
                <label htmlFor="signUpFee" className="control-label">Upfront fee</label>
                <input type="text"
                    name="signUpFee"
                    id="signUpFee"
                    placeholder="eg. 1500"
                    className="form-control"
                    value={upfrontFee}
                    onChange={handleChange}
                />
            </div>
            <div className="col-sm-6 col-recurring-fee">
                <label htmlFor="monthlyAmount" className="control-label">Monthly amount</label>
                <input type="text"
                    name="monthlyAmount"
                    id="monthlyAmount"
                    placeholder="eg. 1000"
                    className="form-control"
                    value={monthlyAmount}
                    onChange={handleChange}
                    disabled={months == 0 ? true : false}
                />
            </div>
        </div>
    )
}

export default AmountInput
