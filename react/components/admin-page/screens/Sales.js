import React, {Fragment, useEffect, useState} from 'react';
import {connect} from 'react-redux';
import * as salesActions from '../actions/sales-actions'
import UniversalTable from "../components/sales/table/universalTable";
import Periods from "../components/sales/periods";
import SalesStats from "../components/sales/salesStats";

const Sales = ({start,end,salesStats,fetchSalesAction}) => {

    useEffect(()=>{
        if(!salesStats.sales){
            fetchSalesAction(start,end);
        }
    },[salesStats])

    console.log(salesStats,"sales")
    return(
        <Fragment>
            {
                salesStats.sales && (
                    <div className="salesStats">
                        <div className="top">
                            <h2>Sales & commissions</h2>
                            <Periods/>
                        </div>
                        {
                            Object.values(salesStats.sales).length > 0  ? (
                                <Fragment>
                                    <SalesStats connect={salesStats.connect} subscriptions={salesStats.subscriptions} salesStats={salesStats.sales}/>
                                    <div className="table">
                                        <UniversalTable
                                            items={Object.entries(salesStats.sales.employee)}
                                            tableTitles={["Salesman","Trainer","count","revenue","Payout"]}
                                            tableType="client"
                                        />
                                    </div>
                                </Fragment>
                            ) : <span> No sales stats!</span>
                        }


                    </div>
                )
            }
        </Fragment>


  )
}

function mapStateToProps(state){
  return{
    salesStats:state.salesDashboard.salesStats,
    start:state.salesDashboard.startDate,
    end:state.salesDashboard.endDate,
  }

}

export default connect(mapStateToProps,{...salesActions})(Sales);
