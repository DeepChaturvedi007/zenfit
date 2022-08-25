import React from 'react'
import {connect} from 'react-redux';

import * as leads from '../store/leads/actions';

//style
import withStyle from './styles';

const Filters = (props) => {
    const {
        classes,
        filterList,
        selectedKey,
        changeFilter,
        count
    } = props;

    return(
        <div className={classes.filterContent}>
            {filterList.map((item, i) => {
                return(
                    <div
                        className={classes.filterItem+" "+(selectedKey === item.key ? classes.filterSelect : "")}
                        key={i}
                        onClick={(e) => {changeFilter(item.key, props[item.key+'Leads'])}}
                    >
                        <p className={classes.filterCounter}>{count[item.key]}</p>
                        <p className={classes.filterName}>{item.value}</p>
                    </div>
                )
            })}
        </div>
    )
}

function mapStateToProps(state) {
    return{

    }
}

export default connect(mapStateToProps, {...leads})(withStyle(Filters));
