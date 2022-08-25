/*jshint esversion: 6 */
import React from 'react';
import moment from 'moment';
import Modal from '@material-ui/core/Modal';
import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";
import './styles.scss'
export default function ModalAddTask(props) {
    const {
        show,
        handleChange,
        handleSubmit,
        onClose
    } = props;
    const modalClose = React.useCallback((e) => {
        if(e.keyCode === 27){
            onClose()
        }
    }, [])

    React.useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])
    return (
        <Modal open={show} style={{zIndex: 2002}} onClose={onClose} className="inmodal in sm2">
            <div className="modal-dialog" style={{outline: 'none'}}>
                <div className="modal-content modal-content-light-grey">
                    <div className="modal-header">
                        <button type="button" className="close" onClick={onClose}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        <h4 className="modal-title">Add New Task</h4>
                    </div>
                    <form className="taskForm max-width" rel="normal" onSubmit={handleSubmit}>
                        <div className="modal-body">
                            <div className="notify"></div>
                            <div className="form-group">
                                <label htmlFor="newTaskTitle" className="control-label">Task's Title</label>
                                <input type="text" id="newTaskTitle" name="title" className="form-control" placeholder="New task's title" onChange={(e) => handleChange('title', e.target.value)} required/>
                            </div>
                            <div className="form-group">
                                <label htmlFor="newTaskDate" className="control-label">Due Date</label>
                                <div className="input-group-custom">
                                    <span className="input-group-addon-custom">
                                        <i className="fa fa-calendar"></i>
                                    </span>
                                    <DateTime
                                        name="dueDate"
                                        className="form-control-custom"
                                        autoComplete="off"
                                        onChange={(e) => handleChange('dueDate', e._d)}
                                        dateFormat="MMM DD YYYY"
                                        timeFormat={false}
                                        closeOnSelect={true}
                                        initialValue={moment.utc()}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="modal-footer">
                            <button className="btn btn-success btn-upper btn-block">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    );
}
