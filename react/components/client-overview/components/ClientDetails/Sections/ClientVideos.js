import React, { Fragment, useEffect} from 'react';
import Collapse from '@material-ui/core/Collapse';
import Card from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import { connect } from 'react-redux';
import * as clients from "../../../store/clients/actions";
import SectionMoreComponent from "../Modules/SectionMoreComponent";
import SectionLoading from '../../../../spinner/SectionLoading';
import IsThereDataComponent from "../Modules/IsThereDataComponent";
import TableHeadComponent from "../Modules/TableHeadComponent";

const ClientVideos = React.memo((props) => {
    const{
        clientId,
        videosCount,
        fetchClientVideos,
        deleteClientVideoAction,
        handleMediaTemplateModal,
        videos,
        loading,
        handleActionModal
    } = props;
    const [collapse, setCollapse] = React.useState(false);
    const handleCollapse = () => {
        setCollapse((prev) => !prev);
    };


    useEffect(() => {
        fetchClientVideos(clientId);
    }, []);

    const deleteVideo = (videoId) => {
        const deleteVideo = videos.find(foundVideo => foundVideo.id === videoId)
        const msg = 'Are you sure you wish to delete: ' + deleteVideo.title + '?'
        handleActionModal(true, msg, () => deleteClientVideoAction(videoId));
    }

    return (
        <Card>
            <PowerHeader
                title={"Videos"}
                subtitle={`(${videosCount} videos)`}
                handleCollapse={handleCollapse}
                collapse={collapse}
            >
                <div
                    className='section-header-right'
                    onClick={() => { handleMediaTemplateModal(true, clientId, "video", videos) }}
                >Add </div>
            </PowerHeader>
            <Collapse in={collapse}>
                <Fragment>
                    <div className='workouts-table media-table'>
                        <IsThereDataComponent length={videos.length} name={"videos"} loading={loading} >
                            {loading ? (
                                <div style={{ height: '50px' }}>
                                    <SectionLoading show={loading} />
                                </div>
                                ): (
                                <table>
                                    <TableHeadComponent tableTitles={["title","action"]}/>
                                    <tbody>
                                        <Fragment>
                                            {Object.keys(videos).map(key => {
                                                const video = videos[key];
                                                return (
                                                    <tr className="workout-item-content" key={key}>
                                                        <td className='workout-item-title'>
                                                            <span className="media-item-title">{video.title}</span>
                                                        </td>
                                                        <td className="workout-item-more">
                                                            <SectionMoreComponent
                                                                visitLink={video.url}
                                                                deleteAction={deleteVideo}
                                                                clientId={clientId}
                                                                actionItemId={video.id}
                                                                itemName={"Video"}
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
})

function mapStateToProps(state) {
    return {
        videos: state.clients.clientVideos,
        loading: state.clients.videosLoading
    }
}

export default connect(mapStateToProps, { ...clients })(ClientVideos);
