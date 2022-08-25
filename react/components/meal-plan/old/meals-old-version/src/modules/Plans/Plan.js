import React, { Fragment, useCallback } from 'react';

import Collapse from '@material-ui/core/Collapse';
import { DateTime } from 'luxon';
import { ReactComponent as CheckboxCircleIcon } from 'remixicon/icons/System/checkbox-circle-fill.svg';
import { ReactComponent as EyeOffIcon } from 'remixicon/icons/System/eye-off-fill.svg';
import { ReactComponent as PencilIcon } from 'remixicon/icons/Design/pencil-fill.svg';
import { ReactComponent as MoreIcon } from 'remixicon/icons/System/more-fill.svg';
import PlanActivityContainer from '../../containers/PlanActivity';
import ModalContainer from '../../containers/Modal';
import ActivityTypes from '../../constants/ActivityTypes';
import MacroSplitTypes from '../../constants/MacroSplitTypes';
import ModalTypes from '../../constants/ModalTypes';
import SettingTypes from '../../constants/SettingTypes';
import MasterMealPlanTypes from '../../constants/MasterMealPlanTypes';
import Activity from './Activity';
import PlanBoard from './PlanBoard';
import Popup from '../../Popup';
import { Card as BaseCard, CardHeader, CardTitle, CardBody } from '../../components/Card';
import { Alert, Label, FlatButton, Button, IconBox, Link, Flex } from '../../components/UI';
import { PlanInfo, PlanInfoItem } from '../../components/Plan';
import { isCallable } from '../../utils/helpers';
import PlanSettings from './PlanSettings';
import styled from 'styled-components';
import Loading from "../../Loading";

const items = [
  {
    label(plan) {
      return (
        <React.Fragment>
          <Label>Target Kcals</Label>
          {plan.type === MasterMealPlanTypes.TYPE_FIXED_SPLIT && <PlanSettings plan={plan} type={PlanSettings.TYPE_KCALS} position="left" />}
        </React.Fragment>
      )
    },
    value(plan) {
      return plan.desired_kcals;
    },
  },
  {
    label(plan) {
      let position = window.innerWidth <= 540 ? 'right' : 'left';

      return (
        <React.Fragment>
          <Label>Macros</Label>
          {plan.type === MasterMealPlanTypes.TYPE_CUSTOM_MACROS && <PlanSettings plan={plan} type={PlanSettings.TYPE_MACROS} position={position} />}
        </React.Fragment>
      )
    },
    value(plan) {
      if (plan.type === MasterMealPlanTypes.TYPE_FIXED_SPLIT) {
        return MacroSplitTypes[plan.macro_split];
      }

      const { carbohydrate, protein, fat } = plan.avg_totals;
      const c = Math.round(carbohydrate * 4 / (carbohydrate * 4 + protein * 4 + fat * 9) * 100);
      const p = Math.round(protein * 4 / (carbohydrate * 4 + protein * 4 + fat * 9) * 100);
      const f = Math.round(fat * 9 / (carbohydrate * 4 + protein * 4 + fat * 9) * 100);

      return `C${carbohydrate} (${c}%), P${protein} (${p}%), F${fat} (${f}%)`;
    },
  },
  {
    label(plan) {
      return (
        <React.Fragment>
          <Label>Status</Label>
          <PlanSettings plan={plan} type={PlanSettings.TYPE_STATUS} position="left" />
        </React.Fragment>
      )
    },
    value(plan) {
      return plan.active ? (
        <React.Fragment>
          <IconBox type="success">
            <CheckboxCircleIcon />
          </IconBox>
          Plan is visible
        </React.Fragment>
      ) : (
        <React.Fragment>
          <IconBox type="danger">
            <EyeOffIcon />
          </IconBox>
          Plan is hidden
        </React.Fragment>
      );
    },
  },
  {
    label: () => (<Label>Created</Label>),
    value(plan) {
      return DateTime.fromSQL(plan.created).toFormat('DD');
    },
  },
  {
    label(plan) {
      return (
        <React.Fragment>
          <Label>Duration</Label>
          <PlanSettings plan={plan} type={PlanSettings.TYPE_DURATION} position="left" />
        </React.Fragment>
      )
    },
    value(plan) {
      return plan.meta && plan.meta.duration ? `${plan.meta.duration} weeks` : 'N/A';
    },
  }
];

const actions = [
  {
    value: SettingTypes.PDF,
    label: 'Save as PDF',
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
      case SettingTypes.DELETE:
        modal.show(ModalTypes.SETTINGS, { plan, settingsType: item.value });
        break;
      case SettingTypes.PDF:
        activity.dispatch(ActivityTypes.PDF_DOWNLOAD, { plan });
        break;
      default:
    }
  };
};

const renderActionTrigger = () => (
  <FlatButton type="button" icon>
    <MoreIcon />
  </FlatButton>
);

const Card = styled(BaseCard)`
  position: relative;
`

const Wrapper = styled.div`
  filter: ${props => props.loading ? 'blur(1px)' : 'none'};
`;

const Backdrop = styled.div`
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  filter: blur(0px);
  display: flex;
  justify-content: center;
  align-items: center;
`;

const Plan = React.memo(({ plan, open, onView, onViewMeals }) => {
  const activity = PlanActivityContainer.useContainer();
  const modal = ModalContainer.useContainer();

  let alert = null;

  const handleView = useCallback(() => {
    window.location = onView(plan.id);
  }, [plan.id]);

  const handleDescription = useCallback((event) => {
    if (event) {
      event.preventDefault();
    }
    modal.show(ModalTypes.SETTINGS, { plan, settingsType: SettingTypes.SETTINGS_DESCRIPTION });
  }, [plan.id]);

  // if (!plan.meals_fit_kcals) {
  //   alert = {
  //     type: 'danger',
  //     children: `One or more of your meals don't hit the target kcal amount. Replace the meal or edit it manually.`,
  //   };
  // }
  // else if (!plan.explaination) {
  //   alert = {
  //     type: 'warning',
  //     children: 'Your plan does not have a description. Click above to create one.',
  //   };
  // }

  const hasDescription = !!plan.explaination;

  const itemZIndex = (max, index) => max - index;
  return (
    <Card>
      {
        plan.loading && (
          <Backdrop>
            <Loading size={10} />
          </Backdrop>
        )
      }
      <Wrapper loading={plan.loading}>
        <CardHeader>
          <div style={{position: 'absolute', width: '100%', height: '100%', zIndex: -1, cursor: 'pointer'}} onClick={onViewMeals}></div>
          <CardTitle>
            <span>{plan.name}</span>
            <PlanSettings plan={plan} type={PlanSettings.TYPE_NAME} position="left" />
          </CardTitle>
          <div style={{flex: 1}}></div>
          {activity.current && <Activity />}
          <Popup
            options={actions}
            onSelect={actionSelect(modal, activity, plan)}
            renderTrigger={renderActionTrigger}
          />
          <Button type="button" onClick={handleView}>
            <PencilIcon />
            <span>Edit</span>
          </Button>
        </CardHeader>
        {open && (
          <Collapse in={open}>
            <Fragment>
              <PlanInfo>
                {items.map((item, index) => (
                  <PlanInfoItem key={`col_${index}`} style={{ zIndex: itemZIndex(5, index) }}>
                    <Flex>
                      {isCallable(item.label) ? item.label(plan) : item.label}
                    </Flex>
                    <span>{item.value(plan)}</span>
                  </PlanInfoItem>
                ))}
                <PlanInfoItem style={{ zIndex: itemZIndex(5, 5) }}>
                  <Label>Description</Label>
                  <Link href="#" onClick={handleDescription}>
                    {hasDescription ? (
                      <React.Fragment>
                        <IconBox type="success">
                          <CheckboxCircleIcon />
                        </IconBox>
                      View Description
                    </React.Fragment>) : 'Create Description'}
                  </Link>
                </PlanInfoItem>
                {alert && (
                  <PlanInfoItem fill="true">
                    <Alert {...alert} />
                  </PlanInfoItem>
                )}
              </PlanInfo>
              <CardBody>
                <PlanBoard planType={plan.type} planId={plan.id} />
              </CardBody>
            </Fragment>
          </Collapse>
        )}
      </Wrapper>
    </Card>
  );
});

export default Plan;
