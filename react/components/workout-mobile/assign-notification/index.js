import React from 'react';
import CSSTransitionGroup from 'react-transition-group/CSSTransitionGroup';

export default class AssignNotification extends React.Component {
    render() {
        const {show, text, error, handleClick} = this.props;
        let containerClass = `notification ${error ? 'notification-error' : ''}`;
        const assignNotification = show
            ? <div className={containerClass} onClick={handleClick}>
                <div className="inner">
                    <i className="material-icons">{error ? 'clear' : 'check'}</i>
                    <div className="notification-inner">
                        {text}
                    </div>
                </div>
            </div>
            : null;
        return (
            <CSSTransitionGroup transitionName="slide"
                                transitionAppear={true}
                                transitionAppearTimeout={500}
                                transitionEnterTimeout={300}
                                transitionLeaveTimeout={500}>
                {assignNotification}
            </CSSTransitionGroup>
        );
    }
}