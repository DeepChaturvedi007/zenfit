import React from 'react';

class ItemActions extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isOpen: false,
        };

        this.toggleDropdown = this.toggleDropdown.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
        this.wrapperRef = React.createRef();
    }

    componentWillUnmount() {
        document.removeEventListener('mousedown', this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (this.wrapperRef && !this.wrapperRef.current.contains(event.target) && this.state.isOpen) {
            this.toggleDropdown();
        }
    }

    toggleDropdown = () => {
        const {isOpen} = this.state;
        this.setState({isOpen: !isOpen});
        if (!isOpen) {
            document.addEventListener('mousedown', this.handleClickOutside);
        } else {
            document.removeEventListener('mousedown', this.handleClickOutside);
        }
    };

    render() {
        const {actions, messageBody, messageId, messageTitle, messageType, messageSubject} = this.props;
        const {isOpen} = this.state;

        let actionItems = [];
        Object.keys(actions).forEach((key) => {
            actionItems.push((
                <div className="item-actions-list-item" key={actions[key].title} onClick={(event) => {
                    event.stopPropagation();
                    actions[key].fn(messageBody, messageId, messageTitle, messageType, messageSubject);
                    this.toggleDropdown();
                }}>{actions[key].title}</div>
            ));
        });

        return (
            <div className="item-actions" ref={this.wrapperRef}>
                <button onClick={(event) => {
                    event.stopPropagation();
                    this.toggleDropdown();
                }}><i className="material-icons">more_horiz</i></button>
                <div className={`item-actions-list ${(isOpen) ? 'open' : ''}`}>
                    {actionItems}
                </div>
            </div>
        );
    }
}

export default ItemActions;
