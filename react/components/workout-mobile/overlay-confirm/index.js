/*jshint esversion: 6 */
import React from 'react';

export default class OverlayConfirm extends React.Component {
    render() {
        const {show, isDisabled, title, subtitle, subtitleBold, onClose, onConfirm, children} = this.props;
        const hiddenClass = show ? '' : 'hidden';
        const tickClass = isDisabled ? 'tick-grey' : '';

        return (
            <div className={`overlay-container ${hiddenClass}`}>
                <div className="overlay-header">
                    <div className="overlay-close">
                        <button onClick={onClose}><i className="material-icons">clear</i></button>
                    </div>
                    <div className="overlay-text">
                        {title && <h2>{title}</h2>}
                        {subtitle && <p>{subtitle}</p>}
                        {subtitleBold && <p className="bold">{subtitleBold}</p>}
                    </div>
                    <div className={`overlay-confirm ${tickClass}`}>
                        <button onClick={!isDisabled && onConfirm}>
                            <i className="material-icons">check</i>
                        </button>
                    </div>
                </div>
                {children}
            </div>
        );
    }
}
