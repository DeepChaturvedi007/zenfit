import React, {Fragment} from 'react'
import Card from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import SectionLoading from '../../../../spinner/SectionLoading';
import moment from "moment";
import { connect } from 'react-redux';
import { S3_BEFORE_AFTER_IMAGES1 } from "../../../const";
import IsThereDataComponent from "../Modules/IsThereDataComponent";
import * as clients from "../../../store/clients/actions";

const ClientPicture = ({ images, clientImagesMax, loading, clientId, fetchMoreClientImagesAction }) => {

    const loadMore = () =>{
        fetchMoreClientImagesAction(clientId)
    }

    return (
        <Card>
            {
                <PowerHeader
                    title={'Pictures'}
                    subtitle={'View Gallery'}
                    subtitleLink={"/progress/client/" + clientId}
                />
            }
            <Fragment>
                {loading ? (
                    <div style={{ height: '125px' }}>
                        <SectionLoading show={true} />
                    </div>
                ) : (
                    <div className='client-picture-content'>
                        <IsThereDataComponent length={images.length} name={"pictures"}>
                            <div className="imageWrapper" style={{overflow: (images.length <= 2 ? "hidden" :"")}}>
                                {
                                    images.map((image,index) => {
                                        return(
                                            <div className="picture-item" key={index}>
                                                <img src={S3_BEFORE_AFTER_IMAGES1 + images[index].name} alt="pic" style={{ width: (images.length > 0 ? "50" : '100%') }} />
                                                <div className="picture-date" style={{ width: "100%"}} >{moment(images[index].date).format('MMM DD, YYYY')}</div>
                                            </div>
                                        )
                                    })
                                }
                            </div>
                            <div className={`loadMore ${clientImagesMax && "disabled" }`} onClick={ () => !clientImagesMax && loadMore()}>
                                { !clientImagesMax ? "Load more" : "All images loaded"}
                            </div>
                        </IsThereDataComponent>

                    </div>
                )}
            </Fragment>
        </Card>
    )
}

function mapStateToProps({ clients }) {
    return {
        images: clients.clientImages,
        loading: clients.imagesLoading,
        clientImagesMax: clients.clientImagesMax
    }
}

export default connect(mapStateToProps,{...clients})(ClientPicture);
