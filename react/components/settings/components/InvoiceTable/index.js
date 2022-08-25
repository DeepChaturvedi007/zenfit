import React from 'react';
import Table from '@material-ui/core/Table';
import TableBody from '@material-ui/core/TableBody';
import TableCell from '@material-ui/core/TableCell';
import TableHead from '@material-ui/core/TableHead';
import TableRow from '@material-ui/core/TableRow';
import ArrowDropUpIcon from '@material-ui/icons/ArrowDropUp';
import ArrowDropDownIcon from '@material-ui/icons/ArrowDropDown';
import SystemUpdateAltIcon from '@material-ui/icons/SystemUpdateAlt';
import TablePagination from '@material-ui/core/TablePagination';

import { INVOICE_EMPTY } from '../../const';
import "./style.scss"

const SortTableHead = (props) => {
    return (
        <div className="sort-icon">
            <ArrowDropUpIcon /><ArrowDropDownIcon className="sort-icon--down" />
        </div>
    );
}

const InvoiceTable = ({ data }) => {
    const [page, setPage] = React.useState(0);
    const [row, setRow] = React.useState(data.slice(0, 5));
    const handleChangePage = (event, newPage) => {
        setPage(newPage);
        setRow(data.slice(newPage * 5, (newPage + 1) * 5))
    }
    return (
        <React.Fragment>
            <div className={`invoice--table ${data.length == 0 ? "" : "visible"}` }>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell><span>Date<SortTableHead /></span></TableCell>
                            <TableCell><span>ID<SortTableHead /></span></TableCell>
                            <TableCell><span>STATUS<SortTableHead /></span></TableCell>
                            <TableCell></TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {row.map((row, index) => (
                            <TableRow key={index}>
                                <TableCell component="th" scope="row">
                                    {row.date}
                                </TableCell>
                                <TableCell>
                                    {row.id}
                                </TableCell>
                                <TableCell>
                                    <span className={(row.status == 'Failed') ? "error" : ""}>{row.status}</span>
                                    {row.status == 'Failed' && <a>&nbsp; &nbsp; Pay now</a>}
                                </TableCell>
                                <TableCell>
                                    <a href={row.url} target="_blank"><SystemUpdateAltIcon /></a>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <div className="invoice--table--pagination">
                    <TablePagination
                        labelRowsPerPage=""
                        component="div"
                        count={data.length}
                        rowsPerPage={5}
                        page={page}
                        onChangePage={handleChangePage}
                    />
                    <span className="left-page">
                        page {page + 1} of {data.length % 5 == 0 ? (data.length == 0 ? 1 : Math.floor(data.length / 5)) : Math.floor(data.length / 5 + 1)}
                    </span>
                </div>
            </div>
            <div className={`table-placeholder ${data.length == 0 ? "visible" : ""}`}>
                    <img src={INVOICE_EMPTY} width="190" />
                    <p className="table-placeholder--title">No invoices</p>
                    <p>You have not received any invoices from Zenfit yet.<br/> When do you do, you'll be able to find it here.</p>
            </div>
        </React.Fragment>
        
    )
}

export default InvoiceTable;

