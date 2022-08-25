import React, { useRef, useEffect } from 'react';
import { connect } from 'react-redux';
import * as clients from "../../../store/clients/actions";
import PowerHeader from '../Modules/PowerHeader';
import Card from '../../../../shared/components/Card';
import { App } from "../../../../meal-plan/new/src/App"

const ClientMeals = React.memo(({ clientId, masterMealPlansCount, userId, handleMealPlanModal, loadMealPlansFor, handleUnloadMealPlan, reloadForUpdate }) => {
    const [collapse, setCollapse] = React.useState(false);
    const [prevLoadedMealPlans, setPrevLoadedMealPlans] = React.useState(null);

    if (loadMealPlansFor !== null && loadMealPlansFor === clientId && prevLoadedMealPlans !== clientId) {
        setCollapse(true);
        setPrevLoadedMealPlans(clientId);
    }

    const handleCollapse = () => {
        setCollapse((prev) => !prev);
        setPrevLoadedMealPlans(null);
        handleUnloadMealPlan();
    };

    return (
        <Card>
            <PowerHeader
                title={'Meal Plans'}
                subtitle={`(${masterMealPlansCount} plans)`}
                handleCollapse={handleCollapse}
                collapse={collapse}
            >
                <div
                    className='section-header-right'
                    onClick={() => { handleMealPlanModal(true, clientId) }}
                >Create Meal Plan</div>
            </PowerHeader>
            {collapse && <App global={{ clientId, userId, reloadForUpdate }} />}
        </Card>
    );
})

function mapStateToProps(state) {
    return {
        userId: state.clients.userId,
        loadMealPlansFor: state.clients.loadMealPlansFor,
        reloadForUpdate: state.clients.reloadForUpdate
    }
}

export default connect(mapStateToProps, { ...clients })(ClientMeals);
