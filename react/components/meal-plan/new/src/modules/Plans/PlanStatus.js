import React, { Fragment } from "react";
import PlanSettings from './PlanSettings';
import CheckCircleIcon from '@material-ui/icons/CheckCircle';
import VisibilityOffIcon from '@material-ui/icons/VisibilityOff';
import { IconBox } from '../../components/UI';
import { green, red } from "@material-ui/core/colors";

const VisibleIcon = () => <CheckCircleIcon style={{ color: green[500], marginRight: '3px' }}/>
const HiddenIcon = () => <VisibilityOffIcon style={{ color: red[500], marginRight: '3px' }}/>

const PlanStatus = React.memo(({ active, plan }) => {
  return (
    <div style={{marginRight: '5px', display: 'flex'}}>
      <div style={{marginRight: '5px', display: 'flex'}}>
        {active ? (
          <Fragment>
            <VisibleIcon />
            Plan is visible
          </Fragment>
        ) : (
          <Fragment>
            <HiddenIcon />
            Plan is hidden
          </Fragment>
        )}
      </div>
      <PlanSettings plan={plan} type={PlanSettings.TYPE_STATUS} position="right" />
    </div>
  );
});

export default PlanStatus;
