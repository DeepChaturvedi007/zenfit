/*jshint esversion: 6 */
import React from 'react';
import ModalOptionsList from '../../modal-options-list';
import AssignClients from '../../assign-clients';
import AssignNotification from '../../assign-notification';
import {EDIT_WORKOUT_DAY, APPLY_WORKOUT_TEMPLATE_TO_CLIENTS} from '../../../../api/workout-api';

export default class OptionsList extends React.Component {

    addNewDay = '#addPlanModal';
    deleteWorkoutPlan = '#deleteModal';
    addNewClientModal = '#addNewClientModal';

    constructor(props) {
        super(props);

        this.state = {
            show: false,
            isShowCreatePlan: false,
            isShowClients: false,
            isShowAssignNotification: false,
            assignNotificationText: '',
            assignNotificationError: false
        };
    }

    render() {
        const {
            show,
            isShowCreatePlan,
            isShowClients,
            isShowAssignNotification,
            assignNotificationText,
            assignNotificationError
        } = this.state;
        const {planId, planName, planComment, isTemplate} = this.props;
        const modalTitle = isTemplate ? 'Rename Workout Plan Template' : 'Rename Workout Plan';
        const btnText = isTemplate ? 'Rename Workout Template' : 'Rename Workout';
        const assignClientsElement = isTemplate ? (
            <AssignClients show={isShowClients}
                           onClose={this.closeClients}
                           onClickAddClient={this.handleClickAddClient}
                           onAssignClients={this.handleAssignClients}/>
        ) : null;
        const assignClientsOptionElement = isTemplate
            ? <li onClick={this.showClients}>Assign Plan to Client(s)</li> : null;
        return (
            <div>
                <button className="btn btn-default btn-sm" onClick={this.showModal.bind(this)} type="button">
                    <span className="icon-dots"/>
                </button>
                <ModalOptionsList show={show} closeModal={this.closeModal.bind(this)}>
                    <li onClick={this.handleUseTemplate}>Use Template(s)</li>
                    {assignClientsOptionElement}
                    <li onClick={this.handleShowCreatePlanModal}>
                        {isTemplate ? 'Rename Template' : 'Rename Workout Plan'}
                    </li>
                    <li onClick={this.itemClickHandler.bind(this, this.addNewDay)}>Add New Day</li>
                    <li className="text-danger" onClick={this.itemClickHandler.bind(this, this.deleteWorkoutPlan)}>
                        Delete Workout
                    </li>
                </ModalOptionsList>
                <ModalCreatePlanFromScratch show={isShowCreatePlan}
                                            planId={planId}
                                            title={planName}
                                            comment={planComment}
                                            titlePlaceholder={'Title of Workout Plan (e.g. Booty Plan)'}
                                            commentPlaceholder={'Enter Your Comment'}
                                            modalTitle={modalTitle}
                                            btnText={btnText}
                                            formAction={EDIT_WORKOUT_DAY(planId)}
                                            onClose={this.handleCloseCreatePlanModal}/>
                {assignClientsElement}
                <AssignNotification show={isShowAssignNotification}
                                    text={assignNotificationText}
                                    error={assignNotificationError}
                                    handleClick={this.notificationClick}/>
            </div>
        );
    }

    showModal() {
        this.setState({show: true});
    }

    closeModal() {
        this.setState({show: false});
    }

    showClients = () => {
        this.setState({isShowClients: true, show: false});
    };

    closeClients = () => {
        this.setState({isShowClients: false});
    };

    itemClickHandler(id) {
        this.closeModal();
        $(id).modal('show');
    }

    handleShowCreatePlanModal = () => {
        this.setState({isShowCreatePlan: true, show: false});
    };

    handleUseTemplate = () => {
        this.setState({show: false});
        $('#addWorkoutTemplate').modal('show');
    };

    handleCloseCreatePlanModal = () => {
        this.setState({isShowCreatePlan: false, item: {}});
    };

    handleClickAddClient = () => {
        this.closeClients();
        $(this.addNewClientModal).modal('show');
    };

    handleAssignClients = (clients) => {
        fetch(APPLY_WORKOUT_TEMPLATE_TO_CLIENTS(this.props.planId), {
            method: 'post',
            credentials: 'include',
            body: JSON.stringify({clientsIds: clients.map(client => client.id)})
        }).then(response => response.json()).then(response => {
            this.closeClients();
            this.showNotification(response[0]);
        });
    };

    showNotification = (response) => {
        this.setState({
            isShowAssignNotification: true,
            assignNotificationText: response.assigned
                ? 'Workout Template Successfully Assigned'
                : 'Something went wrong. Please try reload this page',
            assignNotificationError: !response.assigned
        });
        setTimeout(() => this.setState({isShowAssignNotification: false}), 2000);
    };

    notificationClick = () => {
        this.setState({isShowAssignNotification: false});
    }
}
