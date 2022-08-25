import React from 'react';
import Spinner from "../../spinner";
import wrapActionCreators from "react-redux/lib/utils/wrapActionCreators";
import DefaultMessageItem from "./DefaultMessageItem";
import CreateItem from "./CreateItem";

class DefaultMessageList extends React.Component {
    static defaultProps = {
        showCreate: false,
    };

    constructor(props) {
        super(props);
    }

    render() {
        const {isLoading, messages, onItemClick, itemActions, messageType} = this.props;

        const messageTemplates = messages.map((item) =>
            <DefaultMessageItem
                {...item}
                onClick={onItemClick}
                key={item.id}
                messageId={item.id}
                messageType={messageType}
                actions={itemActions}
            />
        );

        return (
            <div className="template-list-wrap">
                {(isLoading)
                    ? <Spinner show={isLoading}/>
                    : (
                        <div className="template-list">
                            {messageTemplates}
                        </div>
                    )
                }
            </div>
        );
    }
}

export default DefaultMessageList;
