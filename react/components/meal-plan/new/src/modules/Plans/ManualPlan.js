import React, { Fragment, useCallback } from 'react';
import styled from 'styled-components';
import CreateIcon from '@material-ui/icons/Create';
import SettingsIcon from '@material-ui/icons/Settings';

import PlanActivityContainer from '../../containers/PlanActivity';
import ModalContainer from '../../containers/Modal';
import PlanSettings from './PlanSettings';
import Popup from '../../Popup';
import ModalTypes from '../../constants/ModalTypes';
import { Card as BaseCard, CardHeader, CardTitle, CardBody } from '../../components/Card';
import { Alert, Label, FlatButton, Button, IconBox, Link, Flex } from '../../components/UI';
import SettingTypes from '../../constants/SettingTypes';
import PlanStatus from './PlanStatus';
import PlanLastUpdated from './PlanLastUpdated';

const Card = styled(BaseCard)`
  position: relative;
`

const actions = [
  {
    value: SettingTypes.CLONE,
    label: 'Clone',
  },
  {
    divider: true,
  },
  {
    value: SettingTypes.DELETE,
    label: 'Delete',
    modifier: 'danger',
  },
];

const actionSelect = (modal, activity, plan) => {
  return (event, item) => {
    event.preventDefault();

    switch (item.value) {
      case SettingTypes.CLONE:
        modal.show(ModalTypes.SETTINGS, { plan, settingsType: item.value })
        break;
      case SettingTypes.DELETE:
        modal.show(ModalTypes.SETTINGS, { plan, settingsType: item.value });
        break;
      default:
    }
  };
};

const renderActionTrigger = () => (
  <FlatButton type="button" icon>
    <SettingsIcon style={{ width: 20, height: 20 }} color={"inherit"} />
  </FlatButton>
);

const ManualPlan = React.memo(({ plan, onView }) => {
  const activity = PlanActivityContainer.useContainer();
  const modal = ModalContainer.useContainer();

  return (
    <Card>
      <CardHeader>
        <CardTitle>
          <span>{plan.name}</span>
          <PlanSettings plan={plan} type={PlanSettings.TYPE_NAME} position="left" />
        </CardTitle>
        <div style={{ flex: 1 }}></div>
        <PlanLastUpdated
          date={plan.last_updated}
        />
        <PlanStatus
          active={plan.active}
          plan={plan}
        />
        <Popup
          options={actions}
          onSelect={actionSelect(modal, activity, plan)}
          renderTrigger={renderActionTrigger}
        />
        <a style={{ zIndex: '10' }} href={onView(plan.id)}>
          <Button type="button">
            <CreateIcon />
            <span>Edit</span>
          </Button>
        </a>
      </CardHeader>
    </Card>
  )
});

export default ManualPlan;
