import React, { Fragment, useCallback } from 'react';

import Collapse from '@material-ui/core/Collapse';
import SettingsIcon from '@material-ui/icons/Settings';
import CheckCircleIcon from '@material-ui/icons/CheckCircle';
import { green } from "@material-ui/core/colors";
import CreateIcon from '@material-ui/icons/Create';
import { DateTime } from 'luxon';
import PlanActivityContainer from '../../containers/PlanActivity';
import ModalContainer from '../../containers/Modal';
import ActivityTypes from '../../constants/ActivityTypes';
import MacroSplitTypes from '../../constants/MacroSplitTypes';
import ModalTypes from '../../constants/ModalTypes';
import SettingTypes from '../../constants/SettingTypes';
import FoodPrefs from '../../constants/FoodPrefs';
import MasterMealPlanTypes from '../../constants/MasterMealPlanTypes';
import Activity from './Activity';
import PlanBoard from './PlanBoard';
import Popup from '../../Popup';
import { Card as BaseCard, CardHeader, CardTitle, CardBody } from '../../components/Card';
import { Alert, Label, FlatButton, Button, IconBox, Link, Flex } from '../../components/UI';
import { PlanInfo, PlanInfoItem } from '../../components/Plan';
import { isCallable } from '../../utils/helpers';
import PlanSettings from './PlanSettings';
import PlanStatus from './PlanStatus';
import styled from 'styled-components';
import Loading from "../../Loading";
import PlanLastUpdated from './PlanLastUpdated';

const SuccesIcon = () => <CheckCircleIcon style={{ color: green[500], marginBottom: "-2px", marginLeft: "2px" }} />

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
      return <Label>Created</Label>;
    },
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
  },
  {
    label(plan) {
      return (
        <React.Fragment>
          <Label>Pref.</Label>
        </React.Fragment>
      )
    },
    value(plan) {
      const parsed = JSON.parse(plan.parameters);
      if (parsed.hasOwnProperty('foodPreferences')) {
        const preferences = Object.keys(parsed.foodPreferences).filter(pref => {
          return parsed.foodPreferences[pref] === true;
        });

        if (preferences.length === 0) {
          return 'N/A';
        } else {
          return preferences.map(pref => {
            return <FoodPrefLabel>{FoodPrefs[pref]}</FoodPrefLabel>
          });
        }
      }
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
    <SettingsIcon style={{ width: 20, height: 20 }} color={"inherit"} />
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

const FoodPrefLabel = styled.span`
  font-family: Poppins;
  font-size: 11px;
  font-weight: 500;
  font-stretch: normal;
  font-style: normal;
  line-height: normal;
  letter-spacing: normal;
  color: #3a3a3c;
  background-color: #ececee;
  border-radius: 3px;
  padding: 3px 7px;
  margin: 0px 6px 0px 0px
`;

const Plan = React.memo(({ plan, open, onView, onViewMeals }) => {
  const activity = PlanActivityContainer.useContainer();
  const modal = ModalContainer.useContainer();

  let alert = null;

  const handleDescription = useCallback((event) => {
    if (event) {
      event.preventDefault();
    }
    modal.show(ModalTypes.SETTINGS, { plan, settingsType: SettingTypes.SETTINGS_DESCRIPTION });
  }, [plan.id]);

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
          <div style={{ position: 'absolute', width: '100%', height: '100%', cursor: 'pointer' }} onClick={onViewMeals} />
          <CardTitle>
            <span>{plan.name}</span>
            <PlanSettings plan={plan} type={PlanSettings.TYPE_NAME} position="left" />
          </CardTitle>
          <div style={{ flex: 1 }}></div>
          {activity.current && <Activity />}
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
        {open && (
          <Collapse in={open}>
            <Fragment>
              <PlanInfo>
                {items.map((item, index) => (
                  <PlanInfoItem key={`col_${index}`} style={{ zIndex: itemZIndex(5, index) }}>
                    {<Flex>
                      {isCallable(item.label) ? item.label(plan) : item.label}
                    </Flex>}
                    <span>{item.value(plan)}</span>
                  </PlanInfoItem>
                ))}
                <PlanInfoItem style={{ zIndex: itemZIndex(5, 5) }}>
                  <Label>Description</Label>
                  <Link href="#" onClick={handleDescription}>
                    {hasDescription ? (
                      <React.Fragment>
                        <SuccesIcon />
                        View Description
                      </React.Fragment>
                    ) : 'Create Description'}
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
