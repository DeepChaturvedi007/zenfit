import React, {Fragment, useEffect, useImperativeHandle} from 'react';
import { connect } from 'react-redux';

import Collapse from '@material-ui/core/Collapse';
import Card from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import SectionLoading from '../../../../spinner/SectionLoading';

import * as clients from "../../../store/clients/actions";
import SectionMoreComponent from "../Modules/SectionMoreComponent";
import IsThereDataComponent from "../Modules/IsThereDataComponent";
import TableHeadComponent from "../Modules/TableHeadComponent";

const ClientDocs = React.memo((props,ref) => {
    const{
        clientId,
        documentsCount,
        fetchClientDocs,
        deleteClientDocAction,
        handleMediaTemplateModal,
        handleActionModal,
        loading,
        docs
    }= props;

    const [collapse, setCollapse] = React.useState(false);
    const handleCollapse = () => {
        setCollapse((prev) => !prev);
    };

    useEffect(() => {
        fetchClientDocs(clientId);
    }, []);

    const deleteDoc = (docId) => {
        const deleteDoc = docs.find(foundDoc => foundDoc.id === docId)
        const msg = 'Are you sure you wish to delete: ' + deleteDoc.name + '?'
        handleActionModal(true, msg,()=> deleteClientDocAction(docId));
    }

    return (
        <Card className={"client-doc"}>
            <PowerHeader
                title={"Documents"}
                subtitle={`(${documentsCount} docs)`}
                handleCollapse={handleCollapse}
                collapse={collapse}
            >
                <div
                    className='section-header-right'
                    onClick={() => { handleMediaTemplateModal(true, clientId, "document", docs) }}
                >Add </div>
            </PowerHeader>
            <Collapse in={collapse}>
                <Fragment>
                    <div className='workouts-table media-table'>
                        <IsThereDataComponent length={docs.length} name={"documents"} loading={loading} >
                            {loading ? (
                                <div style={{height: '50px'}}>
                                    <SectionLoading show={loading}/>
                                </div>
                            ) : (
                                <table>
                                    <TableHeadComponent tableTitles={["title", "action"]}/>
                                    <tbody>
                                    <Fragment>
                                        {Object.keys(docs).map(key => {
                                            const document = docs[key];
                                            return (
                                                <tr className="workout-item-content" key={key}>
                                                    <td className='workout-item-title'>
                                                        <span className="media-item-title">{document.name} </span>
                                                    </td>
                                                    <td className="workout-item-more">
                                                        <SectionMoreComponent
                                                            visitLink={document.url}
                                                            deleteAction={deleteDoc}
                                                            clientId={clientId}
                                                            actionItemId={document.id}
                                                            itemName={"Document"}
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
                </Fragment>
            </Collapse>
        </Card>
    )
});

function mapStateToProps(state) {
    return {
        docs: state.clients.clientDocs,
        loading: state.clients.docsLoading
    }
}

export default connect(mapStateToProps, { ...clients })(ClientDocs);
