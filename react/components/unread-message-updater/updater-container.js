import React, {Component} from 'react';
import Pusher from 'pusher-js';

export default class UpdaterContainer extends Component {
    constructor(props) {
        super(props);

        this.state = {
            count: props.count
        };
        this.pusher = new Pusher('dcc6e72a783dd2f4b5e2', {
            appId: '1007100',
            cluster: 'eu',
            encrypted: true,
        });
    }

    componentDidMount() {
        this.subscribe();
    }

    subscribe = () => {
        if(!this.props.userId) {
            console.error('Unknown user for the sockets');
        }
        if (!this.chatChannel) {
            this.chatChannel = this.pusher.subscribe(`messages.unread.count.trainer.${this.props.userId}`);
            this.chatChannel.bind('pusher:subscription_succeeded', () => {
                this.chatChannel.bind('message', this.onMessageReceive);
            });
        }
    };

    onMessageReceive = (data) => {
        this.setState({count: data.count})
    };

    render() {
        const {count = 0} = this.state;
        return (
          +count ?
            <span className='label label-warning'>{count}</span> :
            null
        )
    }
}
