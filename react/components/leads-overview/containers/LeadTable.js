import React, { Fragment, useEffect, useCallback } from 'react';

import {connect} from 'react-redux';
import * as leads from '../store/leads/actions';

import TableHeader from './component/TableHeader';
import LeadItem from './component/LeadItem';
import Spinner from "../../spinner";
import withStyle from './styles';

const LeadTable = (props) => {
    const {
        classes,
        tableHead,
        leadsList,
        leadStatus,
        loadMoreLoading,
        sortField,
        openModal,
        handleSort,
        fetchLoadMoreLeads,
        loadMore,
        isAdmin,
        showLeadUtm
    } = props;

    const wrapperEl = document.getElementById('wrapper');

    const onScroll = useCallback((e) => {
        const {loadMoreLoading, offset, leadsList} = props;
        if ((wrapperEl.scrollHeight - wrapperEl.scrollTop) <= (wrapperEl.clientHeight + 50) && !loadMoreLoading) {
            if(offset === leadsList.length){
                loadMore();
            }
        }
    })
    useEffect(() => {
        wrapperEl.addEventListener('scroll', onScroll);
        return function cleanup() {
            wrapperEl.removeEventListener('scroll', onScroll);
        }
    }, [onScroll])
    useEffect(() => {
        if(loadMoreLoading){
            fetchLoadMoreLeads();
        }
    }, [loadMoreLoading])
    return (
        <Fragment>
            <table className={classes.leadTableContent}>
                <TableHeader
                    classes={classes}
                    tableHead={tableHead}
                    sortField={sortField}
                    handleSort={handleSort}
                />
                <tbody>
                    <LeadItem
                        leadsList={leadsList}
                        leadStatus={leadStatus}
                        openModal={openModal}
                        isAdmin={isAdmin}
                        showLeadUtm={showLeadUtm}
                    />
                </tbody>
            </table>
            {loadMoreLoading && (
                <Spinner show={true} />
            )}
        </Fragment>
    )
}

function mapStateToProps(state) {
    return {
        loadMoreLoading: state.leads.loadMoreLoading,
        offset: state.leads.offset,
        limit: state.leads.limit
    }
}

export default connect(mapStateToProps, {...leads})(withStyle(LeadTable));
