import React from 'react';
import { DEFAULT_IMAGE_URL, S3_BEFORE_AFTER_IMAGES } from '../../constants';

const ConversationClient = React.memo(props => {
  const { active, client, onSelect } = props;
  const src = client.photo ? S3_BEFORE_AFTER_IMAGES + client.photo : DEFAULT_IMAGE_URL;

  return (
    <li onClick={() => onSelect(client)}>
      <div className="user-image">
        <img src={src} alt={client.name}/>
      </div>
      <div className="user-name">
        <p>{client.name}</p>
        {
          client.active ?
            <small className="text-green-success m-r-xs">Active</small> :
            <small className="text-danger m-r-xs">Inactive</small>
        }
        {
          client.tags.length ?
            client.tags.map((tag, i) => (<span className="label label-client-type" key={i}>{tag}</span>)) :
            null
        }
      </div>
      <div className={`${active ? 'user-status' : ''}`}>
        <span>{active ? 'Selected' : 'Select'}</span>
      </div>
    </li>
  );
});

export default ConversationClient;

