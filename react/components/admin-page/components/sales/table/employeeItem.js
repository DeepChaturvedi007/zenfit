
import React, {useEffect, useState} from "react";
import {Table, TableBody, TableHead, TableCell, TableRow, ClickAwayListener} from '@material-ui/core';

const EmployeeItem = ({name,employee}) =>{
    return(
        <TableRow>
            <TableCell>
                {name}
            </TableCell>
            <TableCell>
                {employee.trainer}
            </TableCell>
            <TableCell>
                {employee.count}
            </TableCell>
            <TableCell>
                {employee.revenue} {employee.currency}
            </TableCell>
            <TableCell>
                {employee.zfCommission} {employee.currency}
            </TableCell>
        </TableRow>
    )
}

export default EmployeeItem
