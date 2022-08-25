import React, { useEffect, Fragment } from 'react';
import moment from 'moment'

import Collapse from '@material-ui/core/Collapse';
import Card from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import { connect } from 'react-redux';
import * as workouts from "../../../store/workouts/actions";
import SectionMoreComponent from "../Modules/SectionMoreComponent";
import StatusInfoComponent from "../Modules/StatusInfoComponent";
import SectionLoading from '../../../../spinner/SectionLoading';
import { CLIENT_WORKOUT_METAS } from '../../../const.js';
import IsThereDataComponent from "../Modules/IsThereDataComponent";
import TableHeadComponent from "../Modules/TableHeadComponent";
import TitleComponent from "../Modules/TitleComponent";

const ClientWorkouts = React.memo((props) => {
    const {
        clientId,
        WorkoutPlansCount,
        handleWorkoutTemplateModal,
        updateWorkoutPlanAction,
        getWorkoutPlans,
        plans,
        plansLoading,
        handleActionModal,
        deleteWorkoutPlanAction
    } = props;
    const [collapse, setCollapse] = React.useState(false);

    const workoutStatusList = [
        "active",
        "inactive",
        "hidden",
    ];

    useEffect(() => {
        if (collapse) {
            getWorkoutPlans(clientId);
        }
    }, [collapse]);


    const handleCollapse = () => {
        setCollapse((prev) => !prev);
    };

    const deleteWorkoutPlan = (workoutPlanId) => {
        const deletePlan = plans.find(foundPlan => foundPlan.id === workoutPlanId);
        const msg = 'Are you sure you wish to delete: ' + deletePlan.name + '?'
        handleActionModal(true, msg, () => deleteWorkoutPlanAction(workoutPlanId,clientId));
    };

    const onSubmit = (name, workName, planId, clientId) => {
        updateWorkoutPlanAction(name, workName, planId, clientId)
    }

    return (
        <Card>
            <PowerHeader
                title={'Workouts'}
                subtitle={`(${WorkoutPlansCount} plans)`}
                handleCollapse={handleCollapse}
                collapse={collapse}
            >
                <div
                    className='section-header-right'
                    onClick={() => { handleWorkoutTemplateModal(true, clientId) }}
                >Add Workout</div>
            </PowerHeader>
            <Collapse in={collapse}>
                <React.Fragment>
                    <div className='workouts-table'>
                        <IsThereDataComponent length={plans.length} name={"workouts"} loading={plansLoading}>
                            {plansLoading
                                ? (
                                    <div style={{ height: '50px' }}>
                                        <SectionLoading show={plansLoading} />
                                    </div>
                                )
                                : (
                                    <table>
                                        <TableHeadComponent tableTitles={["title","date","tags","action"]}/>
                                        <tbody>
                                        <Fragment>
                                            {Object.keys(plans).map(key => {
                                                const plan = plans[key];
                                                const meta = plan.meta;
                                                return (
                                                    <tr className="workout-item-content" key={key}>
                                                        <td className='workout-item-title'>
                                                            <TitleComponent
                                                                name={plan.name}
                                                                id={plan.id}
                                                                clientId={clientId}
                                                                submit={onSubmit}
                                                                successMsg={"Workout title was updated!"}
                                                            />
                                                            <StatusInfoComponent
                                                                clientId={clientId}
                                                                optionsList={workoutStatusList}
                                                                actionItemId={plan.id}
                                                                currentStatus={plan.status}
                                                                changeAction={updateWorkoutPlanAction}
                                                                param={'status'}
                                                            />
                                                        </td>
                                                        <td className="workout-item-date">{plan.created ? moment(plan.created).format('MMM DD, YYYY') : ''}</td>
                                                        <td className="workout-item-tags" >
                                                            {meta && Object.keys(meta).map(key => {
                                                                if (key === 'type' || key === 'duration') return;
                                                                if (key == 'workoutsPerWeek' || key == 'duration') {
                                                                    if (meta[key]) return <span className="item-status-badge pending" key={key}>{meta[key]} {CLIENT_WORKOUT_METAS[key]}</span>;
                                                                } else {
                                                                    if (meta[key]) return <span className="item-status-badge pending" key={key}>{CLIENT_WORKOUT_METAS[key][meta[key] - 1]}</span>;
                                                                }
                                                            })}
                                                        </td>
                                                        <td className="workout-item-more">
                                                            <SectionMoreComponent
                                                                deleteAction={deleteWorkoutPlan}
                                                                visitLink={'/workout/clients/' + clientId + '/plan/' + plan.id}
                                                                clientId={clientId}
                                                                actionItemId={plan.id}
                                                                itemName={"Plan"}
                                                            />
                                                        </td>
                                                    </tr>
                                                )
                                            })}
                                        </Fragment>
                                        </tbody>
                                    </table>
                                )
                            }
                        </IsThereDataComponent>
                    </div>
                </React.Fragment>
            </Collapse>
        </Card>
    )
});

function mapStateToProps(state) {
    return {
        plans: state.workouts.plans,
        plansLoading: state.workouts.plansLoading
    }
}

export default connect(mapStateToProps, { ...workouts })(ClientWorkouts);
