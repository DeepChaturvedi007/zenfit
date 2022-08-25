import React, {Fragment, useEffect, useState} from 'react';
import {Table, TableBody, TableHead, TableCell, TableRow} from '@material-ui/core';
import styles from './scss/main.module.css'
import EmployeeItem from "./employeeItem";

export default function UniversalTable({tableTitles,tableType,trainers,isMoreLoading,items}) {
    /*table title*/
    const TableTitleCells = tableTitles.map((tableTitle,index) => {
        return (<TableCell key={index} style={{textTransform:"uppercase",textAlign:"left"}}>
            <strong> {tableTitle}  </strong>
        </TableCell>)
    });

    const tableBody = items.length > 0 ? items.map((item,index) => {
        return(
            <EmployeeItem
                key={index}
                name={item[0]}
                employee={item[1]}
            />
        )
    }): null

    return (
        <Fragment>
            { items.length > 0 ?
                (
                    <div className={"tableContainer"}>
                        <Table size="small" className={styles.universalTable}>
                            <TableHead>
                                <TableRow >
                                    {TableTitleCells}
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {tableBody}
                            </TableBody>
                        </Table>
                    </div>
                ) : (
                    <div className={"empty"}>
                        <h2> No employee data was found </h2>
                    </div>
                )
            }
        </Fragment>
    )

}
