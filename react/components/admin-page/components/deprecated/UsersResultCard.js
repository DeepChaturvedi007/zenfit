import React from 'react';
import Card, {CardContent, CardTitle} from "../common/Card";
import {Row, Col} from "../common/Grid";
import UsersTable from "./UsersTable";

const FailedPaymentsCard = ({ title, users, loading, loadMore, extraFields = [] }) => {
  const items = users.map(user => {
    const base = {
      id: user.id,
      name: user.name,
      email: user.email
    };
    const extras = {};
    extraFields.forEach(field => {
      extras[field] = user[field];
    });
    return {
      ...base,
      ...extras
    }
  });
  const onScrolled = (e) => {
    const el = e.target;
    const shouldLoadMore = el.scrollTop > el.scrollHeight - el.clientHeight - 100;
    if(shouldLoadMore && !loading) {
      loadMore();
    }
  };
  return (
    <Card>
      <CardTitle>{title}</CardTitle>
      <CardContent onScroll={onScrolled} style={{maxHeight: '400px', overflow: 'auto'}}>
        <UsersTable items={items}/>
        {!!loading && (
          <Row>
            <Col size={12} variant={'xs'} className={'text-center'} style={{padding: '15px'}}>
              <i className={'fa fa-circle-o-notch fa-spin fa-fw'} style={{fontSize: '24px', opacity: 0.5}}/>
            </Col>
          </Row>
        )}
      </CardContent>
    </Card>
  )
};

export default FailedPaymentsCard;
