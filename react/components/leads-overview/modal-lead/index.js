/*jshint esversion: 6 */
import React, { useState, useEffect, useCallback } from 'react';
import Modal from '@material-ui/core/Modal';
import moment from 'moment';

import ClientInfo from './ClientInfo';
import LeadStatus from './LeadStatus';
import FollowUp from './FollowUp';

import ButtonLoading from '../../spinner/ButtonLoading';

import withStyle from './styles';
import { Tooltip } from '@material-ui/core';
function ModalLeadUpdate(props) {
    const {
        classes,
        show,
        leadInfo,
        error,
        isAssistant,
        submitLoading,
        handleModalOpen,
        handleSubmit,
        leadDelete,
        leadTags
    } = props;

    const [leadDetails, setLeadDetails] = useState({
        name: '',
        email: '',
        phone: '',
        leadStatus: 1,
        followUpDate: '',
        dialogMessage: '',
        salesNotes: '',
        tags: []
    })
    const [followUp, setFollowUp] = useState(false);
    const [deleteLead, setDeleteLead] = useState(false);
    const [convertToClient, setConvertToClient] = useState(false);
    const [disableSaveBtn, setDisableSaveBtn] = useState(false);

    const changeLeadInfo = (value, name) => {
        setLeadDetails({ ...leadDetails, [name]: value })
    }

    const onSubmit = () => {
        let data = {
            name: leadDetails.name,
            email: leadDetails.email,
            phone: leadDetails.phone,
            status: leadDetails.leadStatus,
            followUpAt: moment(leadDetails.followUpDate).format('YYYY-MM-DD HH:mm:ss'),
            dialogMessage: leadDetails.dialogMessage,
            salesNotes: leadDetails.salesNotes,
            tags: leadDetails.tags,
        }

        data.followUp = followUp ? true : false;
        data.convertToClient = convertToClient;
        data.inDialog = true;

        if (leadInfo) {
            data.lead = leadInfo.id
        }

        if(typeof status === 'number') {
            data.status = status;
        }
        if(Array.isArray(status)){
            data.tags = status;
        }

        handleSubmit(data);
    }

    const modalClose = useCallback((e) => {
        if (e.keyCode === 27) {
            handleModalOpen(false)
        }
    }, []);

    useEffect(() => {
        setConvertToClient(false);

        if (leadInfo) {
            setLeadDetails({
                name: leadInfo.name,
                email: leadInfo.email,
                phone: leadInfo.phone,
                leadStatus: leadInfo.status,
                followUpDate: leadInfo.followUpAt ? new Date(leadInfo.followUpAt.date.replace(' ', 'T')) : new Date(),
                dialogMessage: leadInfo.dialogMessage ? leadInfo.dialogMessage : '',
                salesNotes: leadInfo.salesNotes ? leadInfo.salesNotes : '',
                tags: leadInfo.tags
            });
            setFollowUp(leadInfo.followUpAt ? true : false);
            setDisableSaveBtn(leadInfo.status === 5 || leadInfo.status === 3)
        } else {
            setLeadDetails({
                name: '',
                email: '',
                phone: '',
                leadStatus: 1,
                followUpDate: new Date(),
                dialogMessage: '',
                salesNotes: '',
                tags: []
            })
            setFollowUp(false);
            setDisableSaveBtn(false)
        }
        setDeleteLead(false);
    }, [leadInfo, show])

    useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])

    useEffect(() => {
        if (convertToClient) {
            onSubmit();
        }
    }, [convertToClient]);

    return (
        <Modal open={show} style={{ zIndex: 2002, overflow: 'auto' }} className="inmodal in sm2" onClose={() => handleModalOpen(false)}>
            <div className="modal-dialog" style={{ outline: 'none', maxWidth: '830px' }}>
                <div className="modal-content modal-content-light-grey">
                    <div className="modal-header">
                        {(error !== '' && !submitLoading) && (
                            <div className="alert alert-danger">{error}</div>
                        )}
                        <button type="button" className="close" onClick={() => handleModalOpen(false)}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        {!deleteLead ? (
                            <div className="text-left" style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <h4 className="modal-title">
                                    Lead
                                </h4>
                            </div>
                        ) : (
                                <div className="text-left">
                                    <h4 className="modal-title">Delete Lead</h4>
                                    <p>Sure you want to delete lead? You will not be able to recover this lead!</p>
                                </div>
                            )}

                    </div>
                    {!deleteLead && (
                        <div className={"modal-body " + classes.modalBody}>
                            <div className="leadInfo">
                                <ClientInfo
                                    classes={classes}
                                    leadInfo={leadDetails}
                                    changeLeadInfo={changeLeadInfo}
                                />
                            </div>
                            <div className="separator"></div>
                            <div className="salesInfo">
                                <LeadStatus
                                    classes={classes}
                                    leadInfo={leadDetails}
                                    tagsList={leadTags}
                                    isAssistant={isAssistant}
                                    changeLeadInfo={changeLeadInfo}
                                />
                                <FollowUp
                                    classes={classes}
                                    leadInfo={leadDetails}
                                    followUp={followUp}
                                    changeLeadInfo={changeLeadInfo}
                                    handleFollow={setFollowUp}
                                />
                            </div>
                        </div>
                    )}
                    {!deleteLead ? (
                        <div className={"modal-footer " + classes.footer}>
                            {(!isAssistant && leadInfo) && (
                                <button
                                    className="btn btn-default btn-upper delete"
                                    onClick={() => setDeleteLead(true)}
                                >
                                    Delete lead?
                                </button>
                            )}
                            <div style={{ flex: 1 }} />
                            {!submitLoading ? (
                                <div>
                                    <button
                                        className="btn btn-success btn-upper align-middle"
                                        disabled={
                                            (leadDetails.name !== "" &&
                                                leadDetails.email !== "" &&
                                                leadDetails.phone !== ""
                                            ) ? false : true
                                        }
                                        onClick={() => onSubmit()}>{leadInfo ? 'Save' : 'Add lead'}
                                    </button>
                                    {leadInfo && (
                                      <button
                                          className="btn btn-dark btn-upper"
                                          disabled={
                                              (leadDetails.name !== "" &&
                                                  leadDetails.email !== "" &&
                                                  leadDetails.phone !== ""
                                              ) ? false : true
                                          }
                                          onClick={() => setConvertToClient(true)}>Create client
                                      </button>
                                    )}
                                </div>
                            ) : (
                                <button className="btn btn-success btn-upper"><ButtonLoading size={12} />Saving</button>
                            )}
                        </div>
                    ) : (
                            <div className={"modal-footer " + classes.footer}>
                                <div style={{ flex: 1 }} />
                                <button
                                    className="btn btn-default btn-upper"
                                    onClick={() => setDeleteLead(false)}
                                >Cancel
                                </button>
                                <button
                                    className="btn btn-danger btn-upper"
                                    disabled={isAssistant}
                                    onClick={leadDelete}
                                >Delete lead
                                </button>
                            </div>
                    )}
                </div>
            </div>
        </Modal>
    );
}

export default withStyle(ModalLeadUpdate);
