import React, {useState } from 'react';
import {connect} from 'react-redux';
import * as salesActions from '../../actions/sales-actions'
import "react-dates/initialize";
import { DateRangePicker } from "react-dates";
import "react-dates/lib/css/_datepicker.css";
import IsMobileClass from "../../../../../src/AppBundle/Resources/public/tempo/assets/plugins/isMobile";

const Periods =({start,end,currentPeriod,changePeriodAction,fetchSalesAction}) =>{
    const [focusedInput, setFocusedInput] = useState();
    /*TODO:enable different periodtypes*/
    const [open, setOpen] = useState(false);
    const periodes = ["1w","4w","1y","Mtd","Qtd","Ytd","All"];
    const changePeriod = (val) => changePeriodAction(val);

    return(
        <div className="parentPeriods">
            <div className="chosenPeriod">
                <DateRangePicker
                    startDate={start}
                    startDateId="start-date"
                    endDate={end}
                    endDateId="end-date"
                    firstDayOfWeek={1}
                    showClearDates={true}
                    displayFormat={"DD MMM YYYY"}
                    isOutsideRange={() => false}
                    onDatesChange={({ startDate, endDate }) => {
                        fetchSalesAction(startDate,endDate)
                    }}
                    orientation={IsMobileClass.any ? "vertical" : "horizontal"}
                    focusedInput={focusedInput}
                    onFocusChange={(focusedInput) => setFocusedInput(focusedInput)}
                    hideKeyboardShortcutsPanel={true}
                />
            </div>
        </div>

    )
}

function mapStateToProps(state){
    return{
        start:state.salesDashboard.startDate,
        end:state.salesDashboard.endDate,
    }
}


export default connect(mapStateToProps,{...salesActions}) (Periods)
