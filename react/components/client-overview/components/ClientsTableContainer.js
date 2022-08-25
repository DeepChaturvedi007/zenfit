import React from 'react';
import Pusher from "pusher-js";
import SearchField from "./SearchField";
import TagFilter from './TagFilter';
import ClientRemove from './ClientRemove';
import ClientsTable from "./ClientsTable";
import ModalAddTask from './modal-add-task';
import ModalSubscription from '../../modals/modal-subscription';
import ModalConfirmNeed from './modal-confirm-need';
import ModalExtend from './modal-duration-extend';
import ModalAddClient from './modal-add-client';
import ModalMultiSend from './modal-multi-send';
import ModalWorkoutTemplates from './modal-workout-templates';
import ModalMealPlan from './modal-meal-plan';

import { connect } from 'react-redux';
import * as clients from "../store/clients/actions";
import * as workouts from "../store/workouts/actions";
import ModalMediaTemplates from "./modal-media-templates";

const pusher = new Pusher('dcc6e72a783dd2f4b5e2', {
    appId: '1007100',
    cluster: 'eu',
    encrypted: true,
});
const ClientsTableContainer = (props) => {
    let count1 = 0;
    const {
        clientAdding,
        clientAddError,
        clients,
        selectedClients,
        isTaskModalOpen,
        isWorkoutTemplateModalOpen,
        isMealPlanModalOpen,
        clientIdTask,
        isSubModal,
        clientSub,
        locale,
        userId,
        isExtendModalOpen,
        handleTaskModal,
        handleWorkoutTemplateModal,
        handleMealPlanModal,
        addNewTask,
        handleSubscriptionModal,
        clientPaymentUpdate,
        stripeConnect,
        handleExtendModal,
        extendDuration,
        clientStatusUpdate,
        paymentError,
        addClient,
        clientUpdate,
        addClientModalClose,
        multiSendMessage,
        updateMessageCount,
        paymentRequired,
        settings,
        addWorkoutTemplate,
        generateMealPlan,
        isMediaTemplateModalOpen,
        handleMediaTemplateModal,
        mediaTemplateModalType,
        selectedClient,
        addClientVideo,
        addClientDoc,
        mealPlanModalError,
        currentMedias,
        mealPlanModalLoading
    } = props;
    const [newTask, setNewTask] = React.useState({
        title: '',
        dueDate: ''
    });

    const [confirmModalOpen, setConfirmModalOpen] = React.useState(false);
    const [modalMessage, setModalMessage] = React.useState('');
    const [modalAddClientOpen, setModalAddClientOpen] = React.useState(false);
    const [confirmAction, setConfirmAction] = React.useState(() => () => { });
    const [trialEnd, setTrialEnd] = React.useState(false);
    const [modalMultiSendOpen, setModalMultiSendOpen] = React.useState(false);
    const [load, setLoad] = React.useState(false);
    const inputHandleChange = (name, value) => {
        setNewTask({
            ...newTask,
            [name]: value
        })
    }
    const handleSubmit = (e) => {
        e.preventDefault();
        const data = {
            client: clientIdTask,
            title: newTask.title,
            dueDate: newTask.dueDate
        }
        addNewTask(data);
        setNewTask({
            title: '',
            dueDate: ''
        })
    }
    const handleTrialEnd = (trialEnd) => {
        setTrialEnd(trialEnd);
    }
    const handleClose = () => {
        handleTaskModal(false, handleTaskModal)
    }
    const handleCloseWorkoutTemplateModal = () => {
        handleWorkoutTemplateModal(false, handleWorkoutTemplateModal)
    }
    const handleCloseMealPlanModal = () => {
        handleMealPlanModal(false, handleMealPlanModal)
    }
    const handleCloseMediaTemplateModal = () => {
        handleMediaTemplateModal(false, handleMediaTemplateModal);
    }
    const handleSubModal = () => {
        handleSubscriptionModal(false, '')
    }
    const handlePaymentSubmit = (data) => {
        clientPaymentUpdate(data).then(data => {
            if (data) {
                window.openSideContent(true, data, 1, 'payment-email');
            }
        })
    }
    const onHandleConfirmModal = (open, msg, action) => {
        setConfirmModalOpen(open);
        setModalMessage(msg);
        setConfirmAction(() => action);
    }

    const confirmSubmit = () => {
        confirmAction();
        setConfirmModalOpen(false);
    }

    const extendClientDuration = (duration) => {
        let bodyData = new FormData();
        bodyData.append('duration', duration);
        extendDuration(bodyData, clientSub.id)
    }
    const onClientStatusReceive = (data) => {
        clientStatusUpdate(data);
    }
    const handleAddClientModal = (value) => {
        setModalAddClientOpen(value)
    }
    const handleAddClientModalClose = () => {
        setModalAddClientOpen(false)
        addClientModalClose()
    }
    const handleMultiSendMessage = (msg) => {
        const clients = selectedClients.map((item) => {
            return item.id
        });
        multiSendMessage(userId, clients, msg).then(res => {
            setModalMultiSendOpen(false);
            toastr.success(res.msg)
        })
    }
    const applyWorkoutTemplate = id => {
        addWorkoutTemplate(id, [selectedClient.id]);
    }
    const handleGenerateMealPlan = (data) => {
        generateMealPlan(data, selectedClient.id);
    }
    const onMessageReceive = (data) => {
        // Message count updating involves querying get client counts endpoint, and under certain circumstances the amount of
        // requests can be overwhelmingly large. Disable the dynamic update for now, also consider experimenting with the update
        // threshold (3) if the updating is still required.
        // count1 ++;
        // if(count1 === 3) {
        //     updateMessageCount(data)
        //     count1 = 0;
        // }
    }
    const applyMedia = id => {
        if (mediaTemplateModalType === 'video') {
            addClientVideo(id, selectedClient.id);
        } else {
            addClientDoc(id, selectedClient.id);
        }
    }
    React.useEffect(() => {
        if (clients[0] && !load) {
            const chatChannel = pusher.subscribe(`clientStatus.trainer.${clients[0].trainer.id}`);
            chatChannel.bind('clientStatus', onClientStatusReceive);
            setLoad(true)
        }
    }, [clients, load])
    React.useEffect(() => {
        const unReadChatChannel = pusher.subscribe(`messages.unread.count.trainer.${props.userId}`);
        unReadChatChannel.bind('pusher:subscription_succeeded', () => {
            unReadChatChannel.bind('message', onMessageReceive);
        });
    }, [])

    return (
        <div className="clients-table">
            <div className="clients-table-top">
                <SearchField />
                <div className="tag-other-action">
                    <TagFilter />
                    <ClientRemove
                        handleActionModal={onHandleConfirmModal}
                        handleModalMultiSendOpen={setModalMultiSendOpen}
                    />
                    <div className="btn client-add" onClick={() => handleAddClientModal(true)}>Add New Client</div>
                </div>
            </div>
            <ClientsTable
                handleActionModal={onHandleConfirmModal}
            />
            <ModalAddTask
                show={isTaskModalOpen}
                handleChange={inputHandleChange}
                handleSubmit={handleSubmit}
                onClose={handleClose}
            />
            <ModalWorkoutTemplates
                show={isWorkoutTemplateModalOpen}
                onClose={handleCloseWorkoutTemplateModal}
                applyWorkoutTemplate={applyWorkoutTemplate}
                selectedClient={selectedClient}
            />
            <ModalMealPlan
                show={isMealPlanModalOpen}
                onClose={handleCloseMealPlanModal}
                generateMealPlan={handleGenerateMealPlan}
                selectedClient={selectedClient}
                mealPlanModalError={mealPlanModalError}
                mealPlanModalLoading={mealPlanModalLoading}
            />
            <ModalMediaTemplates
                show={isMediaTemplateModalOpen}
                onClose={handleCloseMediaTemplateModal}
                applyMedia={applyMedia}
                currentMedia={currentMedias}
                type={mediaTemplateModalType}
            />
            <ModalSubscription
                show={isSubModal}
                client={clientSub}
                locale={locale}
                errorMessage={paymentError}
                onClose={handleSubModal}
                handleSubmit={handlePaymentSubmit}
            />
            <ModalConfirmNeed
                show={confirmModalOpen}
                message={modalMessage}
                trialEnd={trialEnd}
                handleTrialEnd={handleTrialEnd}
                onClose={onHandleConfirmModal}
                onSubmit={confirmSubmit}
            />
            <ModalExtend
                show={isExtendModalOpen}
                client={clientSub}
                onClose={handleExtendModal}
                onSubmit={extendClientDuration}
            />
            <ModalAddClient
                show={modalAddClientOpen}
                loading={clientAdding}
                error={clientAddError}
                locale={locale}
                stripeConnect={stripeConnect}
                handleSubmit={addClient}
                clientUpdate={clientUpdate}
                handleModal={handleAddClientModal}
                onClose={handleAddClientModalClose}
                paymentRequired={paymentRequired}
                settings={settings}
            />
            <ModalMultiSend
                show={modalMultiSendOpen}
                clients={selectedClients}
                handleModalOpen={setModalMultiSendOpen}
                onSubmit={handleMultiSendMessage}
            />
        </div>
    );
};
function mapStateToProps(state) {
    return {
        clientAdding: state.clients.clientAdding,
        clientAddError: state.clients.clientAddError,
        isTaskModalOpen: state.clients.isTaskModalOpen,
        isWorkoutTemplateModalOpen: state.workouts.isWorkoutTemplateModalOpen,
        isMealPlanModalOpen: state.clients.isMealPlanModalOpen,
        isMediaTemplateModalOpen: state.clients.isMediaTemplateModalOpen,
        mediaTemplateModalType: state.clients.mediaTemplateModalType,
        currentMedias: state.clients.currentMedias,
        userId: state.clients.userId,
        clientIdTask: state.clients.clientIdTask,
        isSubModal: state.clients.isSubModal,
        clientSub: state.clients.clientSub,
        selectedClients: state.clients.selectedClients,
        locale: state.clients.locale,
        isExtendModalOpen: state.clients.isExtendModalOpen,
        clients: state.clients.clients,
        paymentError: state.clients.paymentError,
        stripeConnect: state.clients.stripeConnect,
        paymentRequired: state.clients.paymentRequired,
        settings: state.clients.settings,
        selectedClient: state.clients.selectedClient,
        mealPlanModalError: state.clients.mealPlanModalError,
        mealPlanModalLoading: state.clients.mealPlanModalLoading
    }
}

export default connect(mapStateToProps, { ...clients, ...workouts })(ClientsTableContainer);
