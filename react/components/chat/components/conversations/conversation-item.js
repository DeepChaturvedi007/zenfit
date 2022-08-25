import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
import { S3_BEFORE_AFTER_IMAGES, DEFAULT_IMAGE_URL } from '../../constants';

export default class ConversationItem extends PureComponent {
  static propTypes = {
    active: PropTypes.bool,
    conversation: PropTypes.object.isRequired,
    onClick: PropTypes.func,
  };

  render() {
    const { active, conversation } = this.props;
    const date = conversation.sentAt ? moment(conversation.sentAt).format('MMM Do') : null;
    const text = conversation.message ? conversation.message.replace(/(<([^>]+)>)/ig,'') : '';
    const src = conversation.clientImg ? S3_BEFORE_AFTER_IMAGES + conversation.clientImg : DEFAULT_IMAGE_URL;
    const clientStatus = conversation.active ? 'ACTIVE' : 'INACTIVE';
    const clientStatusClass = conversation.active ? 'conversation-active' : 'conversation-inactive';

    let className = 'conversation-item';

    if (active) {
      className += ' selected';
    } else if (conversation.isNew) {
      className += ' new';
    }

    const clientTags = Array.isArray(conversation.clientTags) ? conversation.clientTags : [];
    let i = 0;

    return (
      <div className={className} onClick={this.handleClick}>
        <img className="conversation-img" src={src} alt="" />
        <div className="conversation-info">
          <span className="conversation-date">{date}</span>
          <div className="conversation-client m-b-xs">
            <b className="conversation-name">{conversation.client}</b>
          </div>
          <div className="conversation-meta">
            <span className={`${clientStatusClass} m-r-xs`}>{clientStatus}</span>
            {
              clientTags.length ?
                clientTags.map(tag => (<span className="label label-client-type" key={`tag:${tag}:${conversation.clientId}:${i++}`}>{tag}</span>)) :
                null
            }
          </div>
          <p className="conversation-text" dangerouslySetInnerHTML={{__html: text}}/>
        </div>
      </div>
    );
  }

  handleClick = () => {
    const { active, onClick } = this.props;

    if (!active && onClick) {
      onClick(this.props.conversation)
    }
  };
}
