import React from 'react';
import moment from 'moment'

import Card, {
    Header,
    Title, Footer
} from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import { connect } from 'react-redux';
import * as clients from "../../../store/clients/actions";
import { statusAction, updateText } from '../statusList';

const ClientTasks = (props) => {
    const {
        clientDetail,
        ignoreStatus,
        handleTaskModal,
        resolveReminder,
        handleMessageType,
        openConfirmModal,
        handleExtendModal,
        handleSubscriptionModal,
        selectedClientId,
        userId,
        statusUpdateFlg,
        statusId,
        addTaskFlg,
        filterProperty,
        locale,
    } = props;
    const allTasks = [...clientDetail.status, ...clientDetail.reminders]
    const unresolved_tasks = allTasks.filter(function (status) {
        return !status.resolved
    });
    const solved_tasks = allTasks.filter(function (status) {
        return status.resolved
    });
    const [showCompletedTask, setShowCompletedTask] = React.useState(false);
    const [tasksList, setTasksList] = React.useState(filterProperty !== 'pending' ? unresolved_tasks : allTasks);
    const [unresolvedTasks, setUnresolvedTasks] = React.useState([]);
    const [solvedTasks, setSolvedTasks] = React.useState([]);
    const [reload, setReload] = React.useState(false);
    const [load, setLoad] = React.useState(false);
    const ignoreChange = (statusId, eventName, index) => {
        if (eventName) {
            if (eventName !== 'client.need_welcome') {
                ignoreStatus(statusId, clientDetail.id, eventName);
            }
            else {
                openConfirmModal(true)
            }
        }
    }
    const updateClick = (eventName, id, action) => {
        const update_value = statusAction(clientDetail, userId, eventName, id, locale, action);
        if (update_value) {
            if (update_value.flg === 'template') {
                handleMessageType(update_value.value, clientDetail.id);
            }
            else if (update_value.flg === 'extend') {
                handleExtendModal(true, clientDetail, id);
            }
            else if (update_value.flg === 'reminder') {
                resolveReminder(id, clientDetail.id)
            }
            else if (update_value.flg === 'resubscribe') {
                handleSubscriptionModal(true, clientDetail)
            }
        }
    }

    const handleTaskList = (value) => {
        setTasksList(value)
        setShowCompletedTask(!showCompletedTask);
    }
    React.useEffect(() => {
        if (statusId !== '' && selectedClientId === clientDetail.id) {
            setTasksList(tasksList.map(item => (item.id === statusId ? { ...item, resolved: true } : item)));
            setUnresolvedTasks(unresolvedTasks.map(item => (item.id === statusId ? { ...item, resolved: true } : item)))
        }
        setReload(!reload)
    }, [statusUpdateFlg]);
    React.useEffect(() => {
        if (filterProperty !== 'pending') {
            setUnresolvedTasks(unresolved_tasks)
            setSolvedTasks(solved_tasks)
            if (!showCompletedTask) {
                setTasksList(unresolved_tasks)
            }
            if (showCompletedTask) {
                setTasksList(solved_tasks)
            }
        }
    }, [selectedClientId]);
    React.useEffect(() => {
        setUnresolvedTasks(unresolved_tasks)
        if (!showCompletedTask && load && selectedClientId === clientDetail.id) {
            const list = [...tasksList, ...[unresolved_tasks[unresolved_tasks.length - 1]]];
            setTasksList(list)
            setReload(!reload)
        }
        setLoad(true)
    }, [addTaskFlg])

    return (
        <div>
            <Card className={"client-task"}>
                <PowerHeader
                    title={'Tasks'}
                    subtitle={`${unresolved_tasks.length}`}
                >
                    <div
                        className='section-header-right'
                        onClick={() => { handleTaskModal(true, clientDetail.id) }}
                    >Add</div>
                </PowerHeader>
                <div className='client-task-content'>
                    <div style={{ padding: '5px 0px' }} id="client-task-list">
                        {tasksList.map(function (item, i) {
                            return (
                                <div className="client-task-list" key={i}>
                                    <label className="client-task-checkbox">
                                        <input type="checkbox" checked={item.resolved ? true : false} onChange={() => { }} disabled />
                                        <span className="checkmark"></span>
                                        <span className="check-label">{item.title + (item.dueDate ? (" [" + moment(item.dueDate.date).format("DD MMM") + "]") : "")}</span>
                                    </label>
                                    {item.resolved ? (
                                        <p className="client-task-status resolve">Done{item.resolved_by ? ', ' + moment(item.resolved_by.date).format("MMM DD") : ''}</p>
                                    ) : (
                                        <div style={{ display: 'flex', alignItems: 'center' }}>
                                            {updateText(item.event_name).map((textItem, j) => {
                                                return (
                                                    <React.Fragment key={j}>
                                                        <p
                                                            className="client-task-status solve"
                                                            onClick={() => { updateClick(item.event_name, item.id, textItem) }}
                                                            style={j !== 0 ? { marginLeft: 0 } : {}}
                                                        >
                                                            {textItem}
                                                        </p>
                                                        {j < updateText(item.event_name).length - 1 && (
                                                            <p>/</p>
                                                        )}
                                                    </React.Fragment>
                                                )
                                            })}
                                            {item.event_name && (
                                                <React.Fragment>
                                                    <p>/</p>
                                                    <p className="client-task-status solve" style={{ marginLeft: 0 }} onClick={() => ignoreChange(item.id, item.event_name, i)}>Ignore</p>
                                                </React.Fragment>
                                            )}
                                        </div>
                                    )}
                                </div>
                            )
                        })}
                        {(tasksList.length === 0) && (
                            <div className="client-task-list">No data</div>
                        )}
                    </div>
                </div>
                <Footer>
                    {filterProperty !== 'pending' && (
                        <React.Fragment>
                            {(showCompletedTask && (solvedTasks.length !== 0 && unresolvedTasks.length !== 0)) && (
                                <div className="client-task-show-complete" onClick={() => handleTaskList(unresolvedTasks)}>Show unresolved tasks ({unresolvedTasks.length})</div>
                            )}
                            {(!showCompletedTask && solvedTasks.length !== 0) && (
                                <div className="client-task-show-complete" onClick={() => handleTaskList(solvedTasks)}>
                                    {unresolvedTasks.length === 0 ? (
                                        'Show completed tasks (' + (solvedTasks.length) + ')'
                                    ) : (
                                        'Show completed tasks (' + (solvedTasks.length) + ')'
                                    )
                                    }
                                </div>
                            )}
                        </React.Fragment>
                    )}
                </Footer>
            </Card>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        userId: state.clients.userId,
        statusUpdateFlg: state.clients.statusUpdateFlg,
        statusId: state.clients.statusId,
        addTaskFlg: state.clients.addTaskFlg,
        filterProperty: state.clients.filterProperty,
        locale: state.clients.locale
    }
}

export default connect(mapStateToProps, { ...clients })(ClientTasks);
