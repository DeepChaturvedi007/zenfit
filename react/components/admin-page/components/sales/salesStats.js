import {HorizontalBar} from 'react-chartjs-2';
import React, {useEffect, useState} from 'react'
import CoolCard, {CardContent, CardNumber, CoolCardTitle} from "../common/CoolCard";
import {OPTIONS, PRIMARYCOLOR} from "../../const";
import RoundedChart from "./roundedChart";
import NumberFormat from "../common/numberFormat";

const SalesStats = ({connect,salesStats,subscriptions}) =>{

    const [amountBarChart, setAmountBarchart] = useState()
    const [countBarChart, setCountBarchart] = useState()

    useEffect(() => {
        let connectAmounts =[]
        let connectCounts =[]
        const connectValues = Object.values(connect)
        connectValues.map(val =>{
            connectAmounts.push( Math.round(val.amount))
            connectCounts.push( val.count)
        })
        const connectY = Object.keys(connect)
        const connectYUpper = connectY.map(val => val.toUpperCase())

        setAmountBarchart({
            /*Currentperiode*/
            labels: connectYUpper,
            datasets: [{
                label: 'Sales revenue',
                data: connectAmounts,
                fill:true,
                backgroundColor:PRIMARYCOLOR
            }]
        })
        setCountBarchart({
            /*Currentperiode*/
            labels: connectYUpper,
            datasets: [{
                label: 'Sales count',
                data: connectCounts,
                fill:true,
                backgroundColor:PRIMARYCOLOR
            }]
        })
    }, [connect]);

    RoundedChart();

    const NoDataProvided = <CardNumber> No data was found </CardNumber>

    return(
        <div className="stats">
            <CoolCard>
                <CoolCardTitle> Connect Revenue </CoolCardTitle>
                { Object.keys(connect).length >0  ? <HorizontalBar data={amountBarChart} options={OPTIONS}/>: NoDataProvided}
            </CoolCard>
            <CoolCard>
                <CoolCardTitle> Connect Amount </CoolCardTitle>
                { Object.keys(connect).length >0 ? <HorizontalBar data={countBarChart} options={OPTIONS}/> :NoDataProvided}
            </CoolCard>
            <CoolCard>
                <CardContent>
                    <CoolCardTitle>Sales count </CoolCardTitle>
                    <CardNumber>{ NumberFormat(salesStats.count)}</CardNumber>
                </CardContent>
                <CardContent>
                    <CoolCardTitle>Sales revenue </CoolCardTitle>
                    <CardNumber>{ NumberFormat(salesStats.revenue)} <span>{salesStats.currency}</span></CardNumber>
                </CardContent>
                <CardContent>
                    <CoolCardTitle>Sales Commission </CoolCardTitle>
                    <CardNumber>{NumberFormat(salesStats.zfCommission)} <span>{salesStats.currency}</span></CardNumber>
                </CardContent>
            </CoolCard>

            <CoolCard>
                <CardContent>
                    <CoolCardTitle>Subscriptions Total</CoolCardTitle>
                    <CardNumber>{ NumberFormat(subscriptions.total)}</CardNumber>
                </CardContent>
                <CardContent>
                    <CoolCardTitle>Churned Customers LastMonth</CoolCardTitle>
                    <CardNumber>{ NumberFormat(subscriptions.churnedCustomersLastMonth)}</CardNumber>
                </CardContent>
                <CardContent>
                    <CoolCardTitle>New customers last month </CoolCardTitle>
                    <CardNumber>{NumberFormat(subscriptions.newCustomersLastMonth)}</CardNumber>
                </CardContent>
            </CoolCard>

        </div>)
}

export default SalesStats;

