/*jshint esversion: 6 */
import React from 'react';
import OverlayConfirm from '../overlay-confirm';

export default class ModalUsersList extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            q: ''
        };
        this.handleChange = this.handleChange.bind(this);
        this.handleClose = this.handleClose.bind(this);
        this.handleConfirm = this.handleConfirm.bind(this);
    }

    s3 = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/client/photo/';
    userImageUrl = '/bundles/app/1456081788_user-01.png';

    render() {
        const {show, users, selectedUsers, onSelect, onClickAddClient} = this.props;
        const isDisabled = !Object.keys(selectedUsers).length;
        const addNewClient = (
            <div className="add-button-container">
                <button type="button" className="add-button" onClick={onClickAddClient}>
                    <span className="add-button-icon">
                        <i className="material-icons">add</i>
                    </span>
                    <span className="add-button-text">Add New Client</span>
                </button>
            </div>
        );
        const usersList = users
            ? <ul className="users-list">
                {users.map((user, i) => {
                    const isSelected = selectedUsers[user.id];
                    const src = user.photo ? this.s3 + user.photo : this.userImageUrl;
                    return (
                        <li ref={i} key={user.id} onClick={onSelect.bind(null, user)}>
                            <div className="user-image">
                                <img src={src}/>
                            </div>
                            <div className="user-name">
                                <p>{user.name}</p>
                            </div>
                            <div className={`user-status ${isSelected ? 'selected' : ''}`}>
                                <span>{isSelected ? 'Assigned' : 'Assign'}</span>
                            </div>
                        </li>
                    );
                })}
            </ul>
            : <div className="user-notification">
                <p>You have not added any clients.</p>
                <p>Please add your first client to assign this template.</p>
            </div>;
        const subtitle = `${Object.keys(selectedUsers).length} Client Selected`;
        const {q} = this.state;

        return (
            <OverlayConfirm show={show}
                            onClose={this.handleClose}
                            onConfirm={this.handleConfirm}
                            title="Assign Plan to Client(s)"
                            subtitle={subtitle}
                            isDisabled={isDisabled}>
                <div className="users-list-search">
                    <input type="text" value={q} placeholder="&#xf002; Search Clients" onChange={this.handleChange}/>
                </div>
                {addNewClient}
                {usersList}
            </OverlayConfirm>
        );
    }

    handleClose() {
        if (this.refs.length) {
            this.refs[0].scrollIntoView();
        }
        this.setState({
            q: ''
        });
        this.props.onClose();
    }

    handleConfirm() {
        this.setState({
            q: ''
        });
        this.props.onConfirm();
    }

    handleChange(event) {
        this.setState({
            q: event.target.value
        });
        this.props.onSearch && this.props.onSearch(event.target.value);
    }
}
