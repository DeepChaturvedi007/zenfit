/*jshint esversion: 6 */
import React, { useState } from 'react';
import Dialog from '@material-ui/core/Dialog';
import moment from 'moment';
import { DURATION } from '../../const.js';
import './styles.scss';
export default function ModalExtend(props) {
    const {
        show,
        client,
        onSubmit,
        onClose
    } = props;
    const [duration, setDuration] = useState('');

    const changeDuration = (e) => {
        setDuration(e.target.value);
    }
    const handleSubmit = () => {
        onSubmit(duration)
    }
    const handleClose = () => {
        onClose(false, {}, '')
    }
    return (
        <Dialog aria-labelledby="simple-dialog-title" open={show} style={{zIndex: 2002}}>
            <div className="modal inmodal in sm2" style={{display: (show ? 'block' : 'none')}}>
                <div className="modal-dialog" style={{marginTop: 160}}>
                    <div className="modal-content modal-content-light-grey">
                        <div className="modal-header">
                            <button type="button" className="close" onClick={() => handleClose()}>
                                <span aria-hidden="true">×</span>
                                <span className="sr-only">Close</span>
                            </button>
                            <h4 className="modal-title">
                                Extend Client
                            </h4>
                            <p>Client is expiring {moment(client.endDate ? client.endDate.date : 'no end date').format('MMM DD, YYYY')}</p>
                        </div>
                        <div className="modal-body">
                            <div className="row">
                                <div className="col-sm-6">
                                    <label htmlFor="duration">Update Client's Duration</label>
                                    <select
                                        id="duration"
                                        name="duration"
                                        className="form-control select-track-progress"
                                        value={duration}
                                        onChange={changeDuration}
                                    >
                                        <option value=''>Pick # of months</option>
                                        {Object.keys(DURATION).map(month => {
                                          return <option key={month} value={month}>{DURATION[month]}</option>
                                        })}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div className="modal-footer footer-button">
                            <button className="btn btn-success btn-upper" onClick={handleSubmit}>Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    );
}
