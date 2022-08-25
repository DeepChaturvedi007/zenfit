import React, {Fragment} from 'react';
import moment from 'moment'

import Card, {
    Header,
    Title
} from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import { connect } from 'react-redux';
const ClientCheckIn = React.memo((props) => {
    const { checkInInfo } = props;
    let content = checkInInfo && checkInInfo.content ? JSON.parse(checkInInfo.content) : null;
    let values = content && content.sliders ? content.sliders : null;
    let answers = content && content.answers ? content.answers : null;
    let msg = content && content.message ? content.message : null;

    return (
        <div style={{marginBottom:25}}>
            <Card>
                <PowerHeader
                    title={'Check-in message'}
                    subtitle={content ? moment(checkInInfo.date).format('ll') : 'No date'}
                />
                <div className='client-checkin-content'>
                    <div className="checkinInner">
                        <div className="checkinMsg">
                            {
                                !content ? ( <div className={"noData"}>No data</div> )
                                    :
                                    <Fragment>
                                        {
                                            <div className={"content"}>
                                                {
                                                    msg && (
                                                        <span>{msg}</span>
                                                    )
                                                }
                                                <div className="customQuestion">
                                                    {
                                                        answers && Object.entries(answers).map((item,index) => {
                                                            return (
                                                                <div key={index}>
                                                                    <strong className=''>{item[0]}: </strong>
                                                                    <span className={''}>
                                                                            {item[1]}
                                                                        </span>
                                                                </div>
                                                            )
                                                        })
                                                    }
                                                </div>
                                            </div>
                                        }

                                    </Fragment>

                            }
                        </div>
                        <div className="checkinAttributes">
                            {
                                values && Object.keys(values).map(key => {
                                    return (
                                        <div key={key}>
                                            <span className='client-checkin-content-key'>{key}: </span>
                                            <span className={'clientCheckinVal'}>{ typeof values[key] === 'number' ? `${values[key]}/5` : values[key] }</span>
                                        </div>
                                    )
                                })
                            }
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    )
})

function mapStateToProps(state) {
    return {
        checkInInfo: state.progress.checkInInfo,
    }
}

export default connect(mapStateToProps)(ClientCheckIn);
