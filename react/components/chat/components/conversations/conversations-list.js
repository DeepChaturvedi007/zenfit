import React from 'react';
import { connect } from 'react-redux';

import ConversationItem from './conversation-item';
import Spinner from '../../../spinner';
import SearchField from '../search-field';
import Button from '../button';
import TagFilter from '../tag-filter';

const ConversationsList = React.memo(props => {
	const {
		conversations,
		selectedConversation: selected,
		updateConversation,
		fetchChat,
		tagFilterConversation,
		handleClick,
		q,
		tags,
		isLoading,
		isMobile,
		assistant
	} = props;

	let listItems;

	if (isLoading) {
		listItems = null;
	} else {
		if (conversations.length) {
			listItems = (
				<div className="conversation-container-in">
					<div className="conversation-title">Conversations</div>
					<div className="conversation-list">
						{conversations.map(item => {
							const active = selected && item.id === selected.id && !isMobile;
							return <ConversationItem
								active={active}
								conversation={item}
								onClick={updateConversation}
								key={item.id} />;
						})}
					</div>
				</div>
			);
		} else {
			listItems = (
				<div className="conversation-notification">
					<p><b>{`No Messages ${q ? 'Such Criteria' : 'From Clients'}`}</b></p>
					<p>Click on <a onClick={handleClick}>New Message</a> on top to start a new conversation.</p>
				</div>
			);
		}
	}

	return (
		<div className="conversation-container">
			<div className="conversation-search">
				<SearchField q={q} tags={tags} action={fetchChat} />
				<Button handleClick={handleClick} btnTitle={'New Message'} />
			</div>
			{!assistant &&
				<div className="conversation-tag-filter">
					<TagFilter q={q} tagFilterConversation={tagFilterConversation} />
				</div>
			}
			<Spinner show={isLoading} />
			{listItems}
		</div>
	);
});

function mapStateToProps(state) {
	return {
		assistant: state.global.assistant,
	}
}

export default connect(mapStateToProps)(ConversationsList);

