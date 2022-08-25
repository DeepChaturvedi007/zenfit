import React, { Component } from 'react';
import { connect } from 'react-redux'

import SearchField from './search-field';
import TagFilter from './tag-filter';
import {
  Modal,
  ModalTitle,
  ModalClose,
  ModalBody,
  ModalFooter
} from 'react16-modal-bootstrap';
import _ from 'lodash';

class ModalBootstrap extends Component {
  constructor(props) {
    super(props);
    this.handleSearchDebounced = _.debounce(this.handleSearchDebounced, 700);
  }

  render() {
    const {
      title,
      show,
      subtitle,
      onClose,
      children,
      selectedClients,
      onConfirm,
      isMobile,
      clientQuery,
      clientTags,
      searchClients,
      tagFilterConversation,
      tagSuggestions,
      assistant
    } = this.props;

    const btnClass = isMobile
      ? ''
      : 'btn-float-right';

    const checkboxStyle = {
      textAlign: 'right',
      marginBottom: '7px'
    };

    const smallStyle = {
      marginRight: '5px'
    };

    const modalSubtitle = subtitle
      ? <p>{subtitle}</p>
      : <div>
        <div style={checkboxStyle}>
          <small style={smallStyle}>Select all</small>
          <input
            type="checkbox"
            checked={this.props.selectedAllClients}
            onChange={this.props.toggleSelectAllClients} />
        </div>
        <SearchField
          tags={clientTags}
          action={this.handleSearchDebounced}
        />
        <div className="m-b-xs" />
        {!assistant &&
          <React.Fragment>
            <TagFilter
              q={clientQuery}
              tagFilterConversation={this.handleTagFilter}
            />
            <div className="m-b-xs" />
          </React.Fragment>
        }
      </div>
      ;
    const confirmBtn = selectedClients && Object.keys(selectedClients).length
      ? <button onClick={onConfirm} className='btn btn-success btn-upper btn-float-right'>
        Done
      </button>
      : null;

    let className = 'inmodal in sm2';

    if (this.props.className) {
      className = `${className} ${this.props.className}`;
    }

    return (
      <Modal className={className} isOpen={show} onRequestHide={onClose}>
        <div className="modal-header">
          <ModalClose className='close' onClick={onClose} />
          <div>
            <ModalTitle>{title}</ModalTitle>
            {modalSubtitle}
          </div>
        </div>
        <ModalBody>
          <div className='users-list-container'>
            {children}
          </div>
        </ModalBody>
        <ModalFooter>
          <button className='btn btn-default btn-upper' onClick={onClose}>
            Cancel
          </button>
          {confirmBtn}
        </ModalFooter>
      </Modal>
    );
  }

  handleSearchDebounced = (...args) => {
    this.props.searchClients(...args);
  };

  handleTagFilter = (...args) => {
    this.props.searchClients(...args);
  }
}

function mapStateToProps(state) {
  return {
    assistant: state.global.assistant,
  }
}

export default connect(mapStateToProps)(ModalBootstrap);