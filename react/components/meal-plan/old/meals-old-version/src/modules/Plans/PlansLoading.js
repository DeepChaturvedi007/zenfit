import React from "react";
import Loading from '../../Loading';
import { Card, CardBody, CardTitle } from '../../components/Card';
import { Center } from '../../components/UI';

const PlansLoading = React.memo(({ reason }) => (
  <Card>
    <CardBody>
      <Center>
        {!reason && <Loading/>}
        <CardTitle>{reason ? '...' : 'Loading meals plans...'}</CardTitle>
      </Center>
    </CardBody>
  </Card>
));

export default PlansLoading;
