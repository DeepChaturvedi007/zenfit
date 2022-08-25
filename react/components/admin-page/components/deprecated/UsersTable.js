import React from 'react';
import Table, {
  TableRow,
  TableBody,
  TableColumn,
  TableHeader
} from '../common/Table';
import moment from "moment";

const UsersTable = ({items}) => {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableColumn mode={'th'}>User ID</TableColumn>
          <TableColumn mode={'th'}>Name</TableColumn>
          {
            items.length && items[0].hasOwnProperty('signupDate') ?
              <TableColumn mode={'th'}>Sign Up Date</TableColumn> :
              null
          }
          {
            items.length && items[0].hasOwnProperty('clients_this_mont') ?
              <TableColumn mode={'th'}>Clients This Month</TableColumn> :
              null
          }
          {
            items.length && items[0].hasOwnProperty('clients_last_mont') ?
              <TableColumn mode={'th'}>Clients Last Month</TableColumn> :
              null
          }
          <TableColumn mode={'th'}>Email</TableColumn>
          {
            items.length && items[0].hasOwnProperty('growth_percentage') ?
              <TableColumn mode={'th'}>Client growth</TableColumn> :
              null
          }
          {
            items.length && items[0].hasOwnProperty('sub_next_payment_attempt') ?
              <TableColumn mode={'th'}>Next payment attempt</TableColumn> :
              null
          }
          {
            items.length && items[0].hasOwnProperty('stripe_customer') ?
              <TableColumn mode={'th'}>Stripe customer ID</TableColumn> :
              null
          }
          {
            items.length && items[0].hasOwnProperty('sub_next_invoice_url') ?
              <TableColumn mode={'th'}>Invoice Url</TableColumn> :
              null
          }
        </TableRow>
      </TableHeader>
      <TableBody>
        {
          items.map((item, i) => (
            <TableRow key={i}>
              <TableColumn><span>{item.id}</span></TableColumn>
              <TableColumn><span>{item.name}</span></TableColumn>
              {
                !!item.hasOwnProperty('signupDate') ?
                  <TableColumn><span>{item.signupDate}</span></TableColumn> :
                  null
              }
              {
                !!item.hasOwnProperty('clients_this_mont') ?
                  <TableColumn><span>{item.clients_this_mont}</span></TableColumn> :
                  null
              }
              {
                !!item.hasOwnProperty('clients_last_mont') ?
                  <TableColumn><span>{item.clients_last_mont}</span></TableColumn> :
                  null
              }
              <TableColumn><span>{item.email}</span></TableColumn>
              {
                !!item.hasOwnProperty('growth_percentage') ?
                  <TableColumn>
                    <div className={item.growth_percentage >= 0 ? 'text-navy' : 'text-danger'}>
                      {Math.abs(item.growth_percentage)}%
                      &nbsp;
                      <i className={`fa ${item.growth_percentage >= 0 ? 'fa-level-up' : 'fa-level-down'}`} />
                    </div>
                  </TableColumn> :
                  null
              }
              {
                !!item.hasOwnProperty('sub_next_payment_attempt') ?
                  <TableColumn><span>{moment.unix(item.sub_next_payment_attempt).format("MM/DD/YYYY")}</span></TableColumn> :
                  null
              }
              {
                !!item.hasOwnProperty('stripe_customer') ?
                  <TableColumn><span>{item.stripe_customer}</span></TableColumn> :
                  null
              }
              {
                !!item.hasOwnProperty('sub_next_invoice_url') ?
                  <TableColumn><span>{item.sub_next_invoice_url}</span></TableColumn> :
                  null
              }
            </TableRow>
          ))
        }
      </TableBody>
    </Table>
  );
};

export default UsersTable;
