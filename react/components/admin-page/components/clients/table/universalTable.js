import React, {Fragment, useEffect, useState} from 'react';
import {Table, TableBody, TableHead, TableCell, TableRow} from '@material-ui/core';
import styles from './scss/main.module.css'
import ClientItem from "./clientItem";

export default function UniversalTable({tableTitles,tableType,trainers,isMoreLoading,items}) {
    /*table title*/
    const TableTitleCells = tableTitles.map((tableTitle,index) => {
        return (<TableCell key={index} style={{textTransform:"uppercase",borderBottom:0,textAlign:"left"}}>
            <strong> {tableTitle}  </strong>
        </TableCell>)
    });
    const tableBody = items.length > 0 ? items.map(item => {
        return(
                <ClientItem
                    key={item.id}
                    item={item}
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
                        {
                            items.length > 19 && (
                                <div className={"limit"}>
                                    <h4>The limit is 20 results, so be more specific</h4>
                                </div>
                            )
                        }
                    </div>
                ) : (
                    <div className={"empty"}>
                        <h3>Search for a {tableType} by name or Email </h3>
                    </div>
                )
            }
        </Fragment>
    )

}
