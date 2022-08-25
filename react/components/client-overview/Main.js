import React, { Fragment, useEffect, useState } from 'react';
import { connect } from 'react-redux';
import NavBar from "./components/NavBar";

import * as clients from './store/clients/actions';
import Filters from "./components/Filters";
import ClientsTableContainer from "./components/ClientsTableContainer";
import VersionAlert from "./components/VersionAlert";
import StripeConnectAlert from "./components/StripeConnectAlert";
import { FILTERS } from "./const";
import ReactTooltip from "react-tooltip";
import SideContent from "../side-content";

const Main = (props) => {
    const {
        clientsCount,
        isActiveFilter,
        filterCounts,
        filterProperty,
        searchQuery,
        tagFilter,
        listOffset,
        sortColumn,
        sortOrder,
        locale,
        sideContentMsgType,
        sideContentTmpType,
        sideContentClient,
        openSideContentFlg,
        selectedClientsDelete,
        openSideContent,
        closeSideContent,
        sendEmail,
        stripeConnect
    } = props;
    const { fetchFilterCount, fetchClients, changeFilterProperty, changeActiveFilter } = props;

    const wrapperEl = document.getElementById('wrapper');

    const [sentEmail, setSentEmail] = useState(false);
    const [reload, setReload] = useState(false);
    window.openSideContent = (open, client, messageType, templateType, reload) => {
        setReload(reload ? true : false)
        openSideContent(open, client, messageType, templateType)
    }
    const submitEmail = (data, msg) => {
        sendEmail(data).then(res => {
            setSentEmail(true);
            setTimeout(() => {
                toastr.success(msg);
                setSentEmail(false);
            }, 900);
            if (res.reload) {
                changeFilterProperty('pending');
            }
        })
            .catch(err => {
                toastr.error(err.response.data.reason);
            });
    }
    const handleSideContentClose = () => {
        closeSideContent()
        setSentEmail(true);
        setTimeout(() => {
            setSentEmail(false);
        }, 900);
    }

    useEffect(() => {
        fetchFilterCount();
    }, [isActiveFilter, searchQuery, tagFilter, selectedClientsDelete]);

    useEffect(() => {
        fetchClients(0).then(() => {
            const { fetchClients, isListReloading } = props
            const { scrollHeight, clientHeight } = wrapperEl;
            if ((scrollHeight) === (clientHeight) && !isListReloading)
                fetchClients();
        });
    }, [isActiveFilter, filterProperty, searchQuery, sortColumn, sortOrder, tagFilter, selectedClientsDelete]);

    useEffect(() => {
        ReactTooltip.rebuild();
    }, [isActiveFilter, filterProperty, searchQuery, sortColumn, sortOrder, listOffset, tagFilter]);

    return (
        <Fragment>
            <NavBar
                clientsCount={clientsCount}
                isActiveFilter={isActiveFilter}
                changeFn={changeActiveFilter}
            />
            {!stripeConnect && (
                <StripeConnectAlert />
            )}
            {isActiveFilter && (
                <Filters
                    filters={FILTERS}
                    filtersCount={filterCounts}
                    currentFilter={filterProperty}
                    onFilterChange={changeFilterProperty}
                />
            )}
            <ClientsTableContainer />
            {openSideContentFlg && (
                <SideContent
                    messageType={sideContentMsgType}
                    locale={locale}
                    templateType={sideContentTmpType}
                    client={sideContentClient}
                    sentEmail={sentEmail}
                    reload={reload}
                    onSubmit={submitEmail}
                    onClose={handleSideContentClose}
                />
            )}
        </Fragment>
    )
};

function mapStateToProps(state) {
    return {
        clients: state.clients.clients,
        listTotalCount: state.clients.listTotalCount,
        clientsCount: state.clients.clientsCount,
        isActiveFilter: state.clients.isActiveFilter,
        filterCounts: state.clients.filterCounts,
        filterProperty: state.clients.filterProperty,
        searchQuery: state.clients.searchQuery,
        listOffset: state.clients.listOffset,
        sortColumn: state.clients.sortColumn,
        sortOrder: state.clients.sortOrder,
        tagFilter: state.clients.tagFilter,
        selectedClientsDelete: state.clients.selectedClientsDelete,
        locale: state.clients.locale,
        sideContentMsgType: state.clients.sideContentMsgType,
        sideContentTmpType: state.clients.sideContentTmpType,
        sideContentClient: state.clients.sideContentClient,
        openSideContentFlg: state.clients.openSideContent,
        stripeConnect: state.clients.stripeConnect
    }
}

export default connect(mapStateToProps, { ...clients })(Main);
