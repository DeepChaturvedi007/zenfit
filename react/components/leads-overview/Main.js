import React, { Fragment, useEffect, useState } from 'react';
import { connect } from 'react-redux';

import _ from 'lodash';

import Button from '@material-ui/core/Button';
import * as leads from './store/leads/actions';

import Spinner from "../spinner";
//container
import Filters from './containers/Filters';
import SearchField from './containers/SearchField';
import TagField from './containers/TagField';
import LeadTable from './containers/LeadTable';
import EmptyLead from './containers/EmptyLead';
import ModalLeadUpdate from './modal-lead';
import ModalAddClient from '../client-overview/components/modal-add-client';
import SideContent from "../side-content";
//styles
import withStyle from './styles';

import { FILTER_LIST, TABLE_HEADER, LEAD_STATUS } from './const';

const Main = (props) => {
    const {
        classes,
        authUserId,
        isAdmin,
        isAssistant,
        locale,
        listUpdate,
        pageLoading,
        getList,
        allLeads,
        newLeads,
        inDialogLeads,
        wonLeads,
        lostLeads,
        noAnswerLeads,
        stripeConnect,
        submitLoading,
        paymentWaitingLeads,
        fetchLeadsCount,
        fetchLeads,
        fetchLeadTags,
        searchLeads,
        searchByTag,
        createUpdateLead,
        clientUpdate,
        leadDelete,
        sendEmail,
        leadTags,
        count,
        searchQuery,
        convertToClient,
        searchTag,
        paymentRequired,
        showLeadUtm,
        handleUpdateFilter,
        activeFilter,
        settings
    } = props;
    const [selectedLeadList, setSelectedLeadList] = useState(allLeads);
    const [leadInfo, setLeadInfo] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [error, setError] = useState('');
    const [modalAddClientOpen, setModalAddClientOpen] = useState(false);
    const [clientId, setClientId] = useState('');
    const [leadId, setLeadId] = useState('');
    const [clientInfo, setClientInfo] = useState(null);
    const [messageType, setMessageType] = useState('');
    const [templateType, setTemplateType] = useState('');
    const [openSideContent, setOpenSideContent] = useState(false);
    const [sortField, setSortField] = useState({})
    const [sentEmail, setSentEmail] = useState(false);
    const [selectLead, setSelectLead] = useState([]);
    window.openSideContent = (open, client, messageType, templateType) => {
        setClientInfo(client);
        setMessageType(messageType);
        setTemplateType(templateType);
        setTimeout(() => {
            setOpenSideContent(open);
        }, 300)
    }
    const changeFilter = (value, leadsList) => {
        handleUpdateFilter(value);
        fetchLeads();
    }
    const handleUpdateModal = (lead) => {
        setModalOpen(true)
        setError('');
        setLeadInfo(lead)
    }

    const handleSubmit = data => {
        createUpdateLead(data).then(res => {
            if (res.status === 'success') {
                if (data.convertToClient) {
                    setClientId(res.resData.client.id)
                    setLeadId(res.resData.lead)
                    setModalAddClientOpen(true);
                }

                setModalOpen(false);

            } else {
                setError(res.resData)
            }

            fetchLeads();
            fetchLeadsCount();
        })
            .catch(err => {
                console.log(err)
            })
    }

    const handleAddClientModal = (value) => {
        setModalAddClientOpen(value)
    }

    const handleAddClientModalClose = () => {
        setModalAddClientOpen(false)
    }

    const handleDelete = () => {
        leadDelete(leadInfo.id).then(res => {
            setModalOpen(false);
        })
    }

    const searchLead = (value) => {
        searchLeads(value)
    }

    const handleSearchTag = (value) => {
        searchByTag(value)
    }

    const closeSideContent = () => {
        setSentEmail(true);
        setTimeout(() => {
            setOpenSideContent(false);
            setSentEmail(false);
        }, [900])
    }
    const sortColumn = (column) => {

        const newSortData = { ...sortField, ...{ [column]: sortField[column] === 'asc' ? 'desc' : 'asc' } };
        const sort_list = Object.keys(newSortData).map((item) => {
            return item
        })
        const order_list = Object.values(newSortData).map((item) => {
            return item
        })

        const newData = _.orderBy(
            selectedLeadList,
            sort_list,
            order_list
        );
        setSortField(newSortData)
        setSelectedLeadList(newData)
    }
    const submitEmail = (data, msg) => {
        sendEmail(data).then(res => {
            setSentEmail(true);
            setTimeout(() => {
                toastr.success(msg);
                setOpenSideContent(false);
                setSentEmail(false);
            }, 900);
        })
            .catch(err => {
                toastr.error(err.response.data.reason);
            });
    }

    useEffect(() => {
        fetchLeadTags();
    }, [listUpdate])
    useEffect(() => {
        fetchLeads();
        fetchLeadsCount();
    }, [listUpdate, searchQuery, searchTag])
    useEffect(() => {
        const newData1 = props[`${activeFilter}Leads`].map((item) => {
            item.diffDays = item.followUpAt ? moment().diff(moment(item.followUpAt.date)) : null;
            item.createDate = item.createdAt ? moment(item.createdAt.date).format('YYYY-MM-DD') : null;
            return item;
        })
        if (sortField) {
            const sort_list = Object.keys(sortField).map((item) => {
                return item
            })
            const order_list = Object.values(sortField).map((item) => {
                return item
            })
            const newData = _.orderBy(
                newData1,
                sort_list,
                order_list
            );
            setSelectedLeadList(newData)
        }
        else {
            setSelectedLeadList(newData1)
        }
    }, [getList])

    return (
        <div className={classes.container}>
            {pageLoading ? (
                <Spinner show={true} />
            ) : (
                <Fragment>
                    <Filters
                        filterList={FILTER_LIST}
                        allLeads={allLeads}
                        newLeads={newLeads}
                        inDialogLeads={inDialogLeads}
                        wonLeads={wonLeads}
                        lostLeads={lostLeads}
                        paymentWaitingLeads={paymentWaitingLeads}
                        count={count}
                        selectedKey={activeFilter}
                        changeFilter={changeFilter}
                    />
                    <div className={classes.searchField}>
                        <SearchField
                            searchLead={searchLead}
                        />
                        {!isAssistant && (
                            <TagField
                                handleChange={handleSearchTag}
                                tags={leadTags}
                                placeholder="Select"
                            />
                        )}

                        {selectLead.length !== 0 && (
                            <TagField
                                handleChange={multiChange}
                                tags={leadTags}
                                placeholder="Assign tag"
                            />
                        )}
                        <div style={{ flex: 1 }} />
                        {!isAssistant && (
                            <Button
                                classes={{
                                    contained: classes.addNewButtonContent,
                                    label: classes.addNewButtonText,
                                    focusVisible: classes.addNewButtonVisible
                                }}
                                variant="contained"
                                onClick={() => handleUpdateModal(null)}
                            >
                                Add New Lead
                            </Button>
                        )}
                    </div>
                    {selectedLeadList.length === 0 ? (
                        <EmptyLead
                            handleAddLeadModal={handleUpdateModal}
                        />
                    ) : (
                        <LeadTable
                            tableHead={TABLE_HEADER}
                            leadsList={selectedLeadList}
                            leadStatus={LEAD_STATUS}
                            sortField={sortField}
                            openModal={handleUpdateModal}
                            handleSort={sortColumn}
                            isAdmin={isAdmin}
                            showLeadUtm={showLeadUtm}
                        />
                    )}
                </Fragment>
            )}
            <ModalLeadUpdate
                show={modalOpen}
                error={error}
                leadInfo={leadInfo}
                isAssistant={isAssistant}
                submitLoading={submitLoading}
                handleModalOpen={setModalOpen}
                handleSubmit={handleSubmit}
                leadDelete={handleDelete}
                leadTags={leadTags}
            />
            <ModalAddClient
                show={modalAddClientOpen}
                clientIdFromLead={clientId}
                stripeConnect={stripeConnect}
                clientUpdate={clientUpdate}
                locale={locale}
                fromLead={true}
                leadId={leadId}
                handleModal={handleAddClientModal}
                onClose={handleAddClientModalClose}
                paymentRequired={paymentRequired}
                settings={settings}
            />
            {openSideContent && (
                <SideContent
                    messageType={messageType}
                    locale={locale}
                    templateType={templateType}
                    client={clientInfo}
                    sentEmail={sentEmail}
                    onSubmit={submitEmail}
                    onClose={closeSideContent}
                />
            )}
        </div>
    )
};

function mapStateToProps(state) {
    return {
        authUserId: state.leads.authUserId,
        isAssistant: state.leads.isAssistant,
        isAdmin: state.leads.isAdmin,
        locale: state.leads.locale,
        listUpdate: state.leads.listUpdate,
        pageLoading: state.leads.pageLoading,
        getList: state.leads.getList,
        allLeads: state.leads.allLeads,
        noAnswerLeads: state.leads.noAnswerLeads,
        newLeads: state.leads.newLeads,
        inDialogLeads: state.leads.inDialogLeads,
        wonLeads: state.leads.wonLeads,
        lostLeads: state.leads.lostLeads,
        paymentWaitingLeads: state.leads.paymentWaitingLeads,
        stripeConnect: state.leads.stripeConnect,
        submitLoading: state.leads.submitLoading,
        leadTags: state.leads.leadTags,
        count: state.leads.count,
        searchQuery: state.leads.searchQuery,
        searchTag: state.leads.searchTag,
        paymentRequired: state.leads.paymentRequired,
        showLeadUtm: state.leads.showLeadUtm,
        activeFilter: state.leads.activeFilter,
        settings: state.leads.settings
    }
}

export default connect(mapStateToProps, { ...leads })(withStyle(Main));
