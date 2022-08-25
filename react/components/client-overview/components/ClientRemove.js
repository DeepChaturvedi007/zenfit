import React from 'react';
import {connect} from "react-redux";
import * as clients from '../store/clients/actions';

function ClientRemove(props) {
    const {selectedClients, isActiveFilter, handleActionModal, handleModalMultiSendOpen,deactivateSelectedClients,activateSelectedClients,deleteSelectedClients} = props;

    const actionChange = (e) => {
        const clients_id = selectedClients.reduce((accumulator, currentValue) => {
            accumulator.push(currentValue.id);
            return accumulator
        }, [])

        if(e.target.value === 'delete') {
            handleActionModal(true, 'Are you sure you wish to delete '+selectedClients.length+' clients?', () => deleteSelectedClients(clients_id))
        }
        else if(e.target.value === 'deactivate') {
            handleActionModal(true, 'Are you sure you wish to deactivate '+selectedClients.length+' clients?', () => deactivateSelectedClients(clients_id))
        }
        else if(e.target.value === 'active') {
            handleActionModal(true, 'Are you sure you wish to activate '+selectedClients.length+' clients?', () => activateSelectedClients(clients_id))
        }
        else if(e.target.value === 'message-send'){
            handleModalMultiSendOpen(true)
        }
    }
    return(
        selectedClients.length !== 0 ?(
            <div className="client-action-select">
                <div className="client-remove-link">
                    {selectedClients.length} clients selected
                </div>
                <select
                    id="periods"
                    name="periods"
                    className="form-control"
                    onChange={actionChange}
                >
                    <option value="">Action</option>
                    <option value="delete">Delete</option>
                    {isActiveFilter ? (
                        <option value="deactivate">Deactivate</option>
                    ) : (
                        <option value="active">Activate</option>
                    )}
                    <option value="message-send">Write message</option>
                </select>
            </div>

        ) : (
            <div className="client-remove-link"/>
        )
    )
}
function mapStateToProps(state) {
    return {
        selectedClients: state.clients.selectedClients,
        isActiveFilter: state.clients.isActiveFilter
    }
}

export default connect(mapStateToProps, {...clients})(ClientRemove);
